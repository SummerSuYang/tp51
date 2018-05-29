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
        $str = 'ID111/0+ID1*ID102-50+ID102*(2+ID141)+ID103';

        $value = [
            111 => 0.115,
            102 => -0.125,
            103 => 50.642,
            141 => 10000.45,
            511 => -40,
            1 => 10,
            11 => 1,
        ];

        $obj = new Formula();
        //$obj->check($str);
        $bool = $obj->calculate($str, $value);
        dump($bool);
    }
}