<?php
/*
|--------------------------------------------------------------------------
| Creator: SuYang
| Date: 2018/5/13 0013 14:06
|--------------------------------------------------------------------------
|                                                      说明
|--------------------------------------------------------------------------
*/

namespace app\lib\exception;


use app\lib\enum\ExceptionEnum;

class CalculatorFormulaException extends CommonException
{
    // HTTP 状态码
    protected $code = 400;
    // 错误消息
    protected $msg = 'FORMULA EXCEPTION';
    // 错误码 16000
    protected $errorCode = ExceptionEnum::FORMULA;
    protected $errorMsg = [
        17001 => [
            'code' => 400,
            'msg' => '后缀表达式错误导致栈异常为空'
        ],
    ];
}