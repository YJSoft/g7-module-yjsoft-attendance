<?php

namespace Modules\Yjsoft\Attendance\Exceptions;

use Exception;

/**
 * 오늘 이미 출석한 경우 발생하는 예외
 */
class AlreadyAttendedException extends Exception
{
    public function __construct()
    {
        $message = __('yjsoft-attendance::messages.already_attended');
        parent::__construct($message, 409);
    }
}
