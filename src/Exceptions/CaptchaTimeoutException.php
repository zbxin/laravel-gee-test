<?php

namespace Zbxin\GeeTest\Exceptions;

use Throwable;

class CaptchaTimeoutException extends \RuntimeException
{
    public function __construct($message = "", $code = 0, Throwable $previous = null)
    {
        parent::__construct(empty($message) ? '验证码超时,请刷新页面重新获取.' : $message, $code, $previous);
    }
}
