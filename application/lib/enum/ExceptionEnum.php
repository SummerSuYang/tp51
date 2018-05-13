<?php

namespace app\lib\enum;

class ExceptionEnum
{
    const BASE = 10000;
    //鉴权或认证错误
    const AUTH = 11000;
    //有关token的错误
    const JWT = 12000;
    //提交过来的数据错误
    const PARAMS = 13000;
	 //上传附加错误
    const UPLOAD_FILE = 14000;
    //微信错误
    const WX = 15000;
    //计算器异常
    const CALCULATOR = 16000;
    //公式计算异常
    const FORMULA = 17000;
}