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

class FormulaException extends CommonException
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
        17002 => [
            'code' => 400,
            'msg' => '返回精度大于计算精度'
        ],
        17003 => [
            'code' => 400,
            'msg' => '公式格式错误'
        ],
        17004 => [
	        'code' => 400,
	        'msg' => '公式计算过程中出错，结果栈中不止一个元素'
        ],
    ];
}