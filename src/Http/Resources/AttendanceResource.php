<?php

namespace Modules\Yjsoft\Attendance\Http\Resources;

use App\Http\Resources\BaseApiResource;

/**
 * 출석 기록 단건 리소스
 */
class AttendanceResource extends BaseApiResource
{
    /**
     * 리소스를 배열로 변환
     */
    public function toArray($request): array
    {
        return [
            'id'           => $this->id,
            'user_id'      => $this->user_id,
            'attend_date'  => $this->attend_date?->toDateString(),
            'attend_time'  => $this->attend_time,
            'greeting'     => $this->greeting,
            'base_point'   => $this->base_point,
            'bonus_point'  => $this->bonus_point,
            'random_point' => $this->random_point,
            'total_point'  => ($this->base_point ?? 0) + ($this->bonus_point ?? 0) + ($this->random_point ?? 0),
            'daily_rank'   => $this->daily_rank,
        ];
    }
}
