<?php

namespace Modules\Yjsoft\Attendance\Http\Resources;

use App\Http\Resources\BaseApiResource;

/**
 * 출석 목록 행 리소스
 */
class AttendanceListResource extends BaseApiResource
{
    /**
     * 리소스를 배열로 변환
     */
    public function toArray($request): array
    {
        return [
            'rank'          => $this->daily_rank,
            'attend_time'   => $this->attend_time,
            'greeting'      => $this->greeting,
            'nickname'      => $this->whenLoaded('user', fn () => $this->user->nickname ?? $this->user->name ?? ''),
            'profile_image' => $this->whenLoaded('user', fn () => $this->user->profile_image ?? null),
            'base_point'    => $this->base_point,
            'random_point'  => $this->random_point,
            'total_point'   => ($this->base_point ?? 0) + ($this->bonus_point ?? 0) + ($this->random_point ?? 0),
        ];
    }
}
