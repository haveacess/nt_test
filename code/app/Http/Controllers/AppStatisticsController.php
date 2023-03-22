<?php

namespace App\Http\Controllers;

use App\Http\Requests\TopPositionsRequest;
use App\Http\Responses\ApiResponse;
use App\Services\HistoryService;
use Carbon\CarbonPeriod;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Facades\Log;

class AppStatisticsController extends Controller
{
    public function getTopPositions(TopPositionsRequest $request): ApiResponse
    {
        try {
            $service = new HistoryService(1421444, 1);

            $date = $request->get('date');
            $period = CarbonPeriod::create($date, $date);
            $topPositionsByDate = $service->getTopPositions($period);

            if (empty($topPositionsByDate)) {
                return new ApiResponse('stats not found', 404);
            }

            $apiResponse = new ApiResponse('ok');
            $apiResponse->setData($topPositionsByDate[$date]);
            return $apiResponse;
        } catch (ClientException $e) {
            Log::error('Failed receive top position for app: ', [
                'request' => $e->getRequest()->getUri()->getPath(),
                'response' => $e->getResponse()->getBody()->getContents()
            ]);
            return new ApiResponse('Third-party service error. Data cannot be retrieved.', 404);
        }
    }
}
