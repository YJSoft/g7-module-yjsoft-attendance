<?php

namespace Modules\Yjsoft\Attendance\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class AttendanceRecord extends Model
{
    protected $table = 'attendance_records';

    protected $fillable = [
        'user_id',
        'attend_date',
        'attend_time',
        'greeting',
        'base_point',
        'bonus_point',
        'random_point',
        'daily_rank',
    ];

    protected $casts = [
        'attend_date' => 'date',
        'user_id'     => 'integer',
        'base_point'  => 'integer',
        'bonus_point' => 'integer',
        'random_point' => 'integer',
        'daily_rank'  => 'integer',
    ];

    /**
     * 총 포인트 합산 (기본 + 보너스 + 랜덤)
     */
    public function getTotalPointAttribute(): int
    {
        return ($this->base_point ?? 0) + ($this->bonus_point ?? 0) + ($this->random_point ?? 0);
    }

    /**
     * 특정 사용자 필터 스코프
     */
    public function scopeByUser(Builder $query, int $userId): Builder
    {
        return $query->where('user_id', $userId);
    }

    /**
     * 특정 연/월 필터 스코프
     */
    public function scopeByMonth(Builder $query, int $year, int $month): Builder
    {
        return $query->whereYear('attend_date', $year)
                     ->whereMonth('attend_date', $month);
    }

    /**
     * 사용자 관계
     */
    public function user()
    {
        return $this->belongsTo(\App\Models\User::class, 'user_id');
    }
}
