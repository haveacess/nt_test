<?php

namespace App\Services;

use App\Models\AppsModel;
use App\Models\AppStatsModel;
use App\Models\CountriesModel;
use Carbon\CarbonPeriod;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use InvalidArgumentException;

class HistoryService {
    private int $appId;
    private int $countryId;

    private Client $client;

    const ENDPOINT_STATISTIC = 'https://api.apptica.com';

    /**
     * Get history of some app
     *
     * @param int $appId Application ID for receive history
     * @param int $countryId Country ID for receive history
     * @throws InvalidArgumentException
     */
    public function __construct(int $appId, int $countryId)
    {
        if (!AppsModel::where('id', $appId)->exists()) {
            Log::warning('invalid for receive history', ['app_id' => $appId]);
            throw new InvalidArgumentException('app_id is invalid');
        }

        if (!CountriesModel::where('id', $countryId)->exists()) {
            Log::warning('invalid for receive history', ['country_id' => $countryId]);
            throw new InvalidArgumentException('country_id is invalid');
        }

        Log::info('Service for receive app statistics init successful', [
            'appId' => $appId,
            'countryId' => $countryId
        ]);

        $this->appId = $appId;
        $this->countryId = $countryId;

        $this->client = new Client([
            'base_uri' => self::ENDPOINT_STATISTIC,
            'timeout'  => 2
        ]);
    }

    /**
     * Get Top positions of the app for specific period
     * (When statistic not found in database -
     * We're trying to get it from 3-party service)
     *
     * @param CarbonPeriod $period Period for receive statistic
     * @return array
     * @throws InvalidArgumentException|ClientException
     */
    public function getTopPositions(CarbonPeriod $period): array
    {
        Log::info('Try to receive top positions for period: ', [
            'startDate' => $period->getStartDate()->format('d-m-Y'),
            'endDate' => $period->getEndDate()->format('d-m-Y')
        ]);

        $groupByDate = function (Collection $statistics) {
            $result = [];

            foreach ($statistics as $item) {
                $result[$item->date][$item->id_category] = $item->top_place;
            }

            return $result;
        };

        $statsModel = new AppStatsModel();
        $statsModel->id_app = $this->appId;
        $statsModel->id_country = $this->countryId;

        if ($statsModel->isFull($period)) {
            Log::info('Fully statistic has in database. Receiving from database');

            $stats = $statsModel->getByPeriod($period, ['date', 'id_category', 'top_place'])->get();
            return $groupByDate($stats);
        }

        $newStats = $this->fetchTopPositions($period);

        DB::table($statsModel->getTable())->insertOrIgnore($newStats);

        $stats = $statsModel->getByPeriod($period, ['date', 'id_category', 'top_place'])->get();

        return $groupByDate($stats);
    }

    /**
     * Fetch top positions for statistic from 3-party service
     *
     * @param CarbonPeriod $period Period for receive statistic
     * @return array List of positions for push in database
     * @throws GuzzleException
     */
    private function fetchTopPositions(CarbonPeriod $period): array
    {
        Log::info('Receive top positions from 3-party service', [
            'startDate' => $period->getStartDate()->format('d-m-Y'),
            'endDate' => $period->getEndDate()->format('d-m-Y')
        ]);

         $response = $this->client->get("/package/top_history/{$this->appId}/{$this->countryId}", [
            "query" => [
                "date_from" => $period->getStartDate()->format('Y-m-d'),
                "date_to" => $period->getEndDate()->format('Y-m-d')
            ]
        ]);

        $decodedResponse = json_decode($response->getBody());
        Log::info('Top positions from 3-party service received successful');

        $topPositionByDate = [];

        foreach ($decodedResponse->data as $categoryId => $subCategoryInfo) {
            foreach ($subCategoryInfo as $subCategoryId => $topPlaceByDate) {
                foreach ($topPlaceByDate as $date => $topPlace) {

                    if (!isset($topPositionByDate[$date][$categoryId])) {
                        $topPositionByDate[$date][$categoryId] = $topPlace;
                    }

                    if (!is_null($topPlace) && $topPlace < $topPositionByDate[$date][$categoryId]) {
                        $topPositionByDate[$date][$categoryId] = $topPlace;
                    }
                }
            }
        }

        $newStats = [];
        foreach ($topPositionByDate as $date => $positionByCategory) {
            foreach ($positionByCategory as $categoryId => $topPlace) {
                $newStats[] = [
                    'id_category' => $categoryId,
                    'id_country' => $this->countryId,
                    'id_app' => $this->appId,
                    'date' => $date,
                    'top_place' => $topPlace
                ];
            }


        }

        return $newStats;
    }
}
