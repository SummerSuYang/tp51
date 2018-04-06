<?php

namespace app\lib\exception;

use app\lib\enum\ExceptionEnum;

class ParamsException extends BaseException
{
    // HTTP 状态码
    protected $code = 400;
    // 错误消息
    protected $msg = '参数错误';
    // 错误码 13000
    protected $errorCode = ExceptionEnum::PARAMS;
    // 自定义的错误集
    protected $errorMsg = [
        13001 => [
            'code' => 400,
            'msg' => '缺少必要的参数',
        ]
    ];
}