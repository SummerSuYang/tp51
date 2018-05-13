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

use app\common\service\Calculator;
use app\common\service\FormulaCalculator;
use think\Controller;

class Test extends Controller
{
    public function test()
    {
        $str = 'ID114+ID113*2-ID112*111-500*40';
        $value = [
            111 => 0.11,
            112 => 0.35,
            113 => 50.64,
            114 => 10000.45,
            115 => 40
        ];
    }
}