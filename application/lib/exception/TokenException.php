<?php

namespace app\lib\exception;

use app\lib\enum\ExceptionEnum;

class TokenException extends CommonException
{
    // HTTP 状态码
    protected $code = 403;
    // 错误消息
    protected $msg = 'TOKEN EXCEPTION';
    // 错误码 12000
    protected $errorCode = ExceptionEnum::TOKEN;
    // 自定义的错误集
    protected $errorMsg = [
        //找不到前传过来的token
        12001 => [
            'code' => 401,
            'msg' => '缺少token',
        ],
        //token被设置了nbf，现在还不能使用
        12002=>[
            'code' => 401,
            'msg' => 'token现在还不能使用'
        ],
        12003=>[
            'code' => 401,
            'msg' => 'Token已经过期'
        ],
        //header 中缺少必要的声明或者加密算法不被接收或者payload为空
        12004=>[
            'code' => 401,
            'msg' => 'token中的信息错误'
        ],
        //token可能被修改过导致签名验证失败
        12005=>[
            'code' => 401,
            'msg' => '签名验证失败'
        ],
        12006=>[
            'code' => 401,
            'msg' => 'Token 生成失败'
        ],
        12007=>[
            'code' => 401,
            'msg' => '无法解析用户信息'
        ],
        12008=>[
            'code' => 401,
            'msg' => '无法生成载荷，缺少必要的用户信息'
        ],
        12009=>[
            'code' => 401,
            'msg' => '无法生成token, 缺少配置信息'
        ],
        12010=>[
            'code' => 401,
            'msg' => '请选择token应用场景'
        ],
        12011=>[
            'code' => 401,
            'msg' => '无法确定的用户'
        ],

        12012=>[
            'code' => 401,
            'msg' => '无法生成载荷,用户信息的类型错误'
        ],
    ];
}