<?php

namespace Modules\Yjsoft\Attendance\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * 출석 요청 검증
 *
 * authorize()에서 권한 체크 금지 — 라우트 미들웨어에서 처리.
 */
class StoreAttendanceRequest extends FormRequest
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
            'greeting' => ['nullable', 'string', 'max:255'],
        ];
    }
}
