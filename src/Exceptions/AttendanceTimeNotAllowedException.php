<?php

namespace Modules\Yjsoft\Attendance\Exceptions;

use Exception;

/**
 * 출석 가능 시간이 아닌 경우 발생하는 예외
 */
class AttendanceTimeNotAllowedException extends Exception
{
    public function __construct()
    {
        $message = __('yjsoft-attendance::messages.time_not_allowed');
        parent::__construct($message, 403);
    }
}
