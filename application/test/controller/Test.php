<?php
/*
|--------------------------------------------------------------------------
| Creator: SuYang
| Date: 2018/5/13 0013 14:25
|--------------------------------------------------------------------------
|                                                      说明
|--------------------------------------------------------------------------
*/

namespace app\test\controller;

use app\common\service\Formula;
use think\Controller;

class Test extends Controller
{
    public function test()
    {
        $str = '0.001121*ID11+3/ID1+ID114/11211+100/ID113+ID115+ID111-ID112-45.2/ID112';
        $value = [
            111 => 0.11,
            112 => 0.1,
            113 => 50.64,
            114 => 10000.45,
            115 => -40,
            1 => 10,
            11 => 1,
        ];

        $obj = new Formula();
        //$obj->check($str);
        $bool = $obj->calculate($str, $value);
        dump($bool);
    }
}