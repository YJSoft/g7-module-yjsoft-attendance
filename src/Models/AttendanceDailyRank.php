<?php

namespace Modules\Yjsoft\Attendance\Models;

use Illuminate\Database\Eloquent\Model;

class AttendanceDailyRank extends Model
{
    protected $table = 'attendance_daily_ranks';

    protected $fillable = [
        'rank_date',
        'user_id',
        'rank',
        'bonus_point',
        'bonus_paid',
    ];

    protected $casts = [
        'rank_date'   => 'date',
        'user_id'     => 'integer',
        'rank'        => 'integer',
        'bonus_point' => 'integer',
        'bonus_paid'  => 'boolean',
    ];

    /**
     * 사용자 관계
     */
    public function user()
    {
        return $this->belongsTo(\App\Models\User::class, 'user_id');
    }
}
