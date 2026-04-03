<?php

namespace Modules\Yjsoft\Attendance\Models;

use Illuminate\Database\Eloquent\Model;
use Modules\Yjsoft\Attendance\Enums\StreakType;

class AttendanceStreak extends Model
{
    protected $table = 'attendance_streaks';

    protected $fillable = [
        'user_id',
        'streak_type',
        'period_start',
        'period_end',
        'current_streak',
        'is_completed',
        'bonus_paid',
    ];

    protected $casts = [
        'streak_type'    => StreakType::class,
        'period_start'   => 'date',
        'period_end'     => 'date',
        'is_completed'   => 'boolean',
        'bonus_paid'     => 'boolean',
        'user_id'        => 'integer',
        'current_streak' => 'integer',
    ];

    /**
     * 사용자 관계
     */
    public function user()
    {
        return $this->belongsTo(\App\Models\User::class, 'user_id');
    }
}
