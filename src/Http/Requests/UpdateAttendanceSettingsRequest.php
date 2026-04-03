<?php

namespace Modules\Yjsoft\Attendance\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * 출석부 설정 저장 요청 검증
 *
 * authorize()에서 권한 체크 금지 — 라우트 미들웨어에서 처리.
 */
class UpdateAttendanceSettingsRequest extends FormRequest
{
    /**
     * 권한 체크는 라우트 미들웨어에서 수행
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * 검증 규칙
     */
    public function rules(): array
    {
        return [
            'general.auto_attend'       => ['sometimes', 'boolean'],

            'bonus.base_point'          => ['sometimes', 'integer', 'min:0'],
            'bonus.weekly_streak_point' => ['sometimes', 'integer', 'min:0'],
            'bonus.monthly_streak_point' => ['sometimes', 'integer', 'min:0'],
            'bonus.yearly_streak_point' => ['sometimes', 'integer', 'min:0'],
            'bonus.rank1_point'         => ['sometimes', 'integer', 'min:0'],
            'bonus.rank2_point'         => ['sometimes', 'integer', 'min:0'],
            'bonus.rank3_point'         => ['sometimes', 'integer', 'min:0'],

            'time_limit.enabled'        => ['sometimes', 'boolean'],
            'time_limit.start_hour'     => ['sometimes', 'integer', 'between:0,23'],
            'time_limit.start_minute'   => ['sometimes', 'integer', 'between:0,59'],
            'time_limit.end_hour'       => ['sometimes', 'integer', 'between:0,23'],
            'time_limit.end_minute'     => ['sometimes', 'integer', 'between:0,59'],

            'random_point.enabled'      => ['sometimes', 'boolean'],
            'random_point.min_point'    => ['sometimes', 'integer', 'min:1'],
            'random_point.max_point'    => ['sometimes', 'integer', 'min:1'],
            'random_point.probability'  => ['sometimes', 'integer', 'between:1,100'],

            'greetings.list'            => ['sometimes', 'array', 'min:1'],
            'greetings.list.*'          => ['string', 'max:255'],
        ];
    }
}
