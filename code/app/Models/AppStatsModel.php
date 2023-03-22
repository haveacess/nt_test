<?php

namespace App\Models;

use Carbon\CarbonPeriod;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class AppStatsModel extends Model
{
    use HasFactory;

    protected $table = 'app_stats';
    protected $fillable = ['id_country', 'id_category', 'id_app', 'date', 'top_place'];
    public $timestamps = false;

    /**
     * Do you have is full statistic for app by specific period
     *
     * @param CarbonPeriod $period Period for get stats
     * @return bool True - when statistic exist for each of day in period
     */
    public function isFull(CarbonPeriod $period): bool
    {
        $countExistDates = $this->getByPeriod($period, ['date'])
            ->groupBy('date')
            ->get()
            ->count();

        return count($period) === $countExistDates;
    }

    /**
     * Get builder for get stats for specific period
     *
     * @param CarbonPeriod $period Period for get stats
     * @param array $columns list of selected columns
     * @throws InvalidArgumentException
     * @return Builder
     */
    public function getByPeriod(CarbonPeriod $period, array $columns): Builder
    {
        if (is_null($this->id_app) || is_null($this->id_country)) {
            throw new InvalidArgumentException('id_app or id_country is not filled');
        }

        return DB::table($this->table)
            ->select($columns)
            ->where([
                ['id_app', '=', $this->id_app],
                ['id_country', '=', $this->id_country],
            ])
            ->whereBetween('date', [
                $period->getStartDate()->format('Y-m-d'),
                $period->getEndDate()->format('Y-m-d')
            ]);
    }
}
