<?php
/*
|--------------------------------------------------------------------------
| Creator: SuYang
| Date: 2018/5/11 0011 16:04
|--------------------------------------------------------------------------
|                                                    计算器类
|--------------------------------------------------------------------------
*/

namespace app\common\service;


use app\lib\exception\CalculatorException;

class Calculator
{
    //小数点后的位数
    protected $scale =4;

    /**
     * @param $scale
     * @return $this
     * @throws CalculatorException
     * 设置小数点后的位数
     */
    public function setCalculateScale($scale)
    {
        if( !isPositiveInteger($scale)){
            throw new CalculatorException(16001);
        }

        $this->scale = $scale;

        return $this;
    }

    /**
     * @return int
     */
    public function returnCalculateScale()
    {
        return $this->scale;
    }

    /**
     * @param $strOne
     * @param $strTwo
     * @return string
     * 加法
     */
    public function add($strOne, $strTwo)
    {
        $this->prepare($strOne, $strTwo);
        return bcadd($strOne, $strTwo, $this->scale);
    }

    /**
     * @param $strOne
     * @param $strTwo
     * @return string
     * 减法
     */
    public function sub($strOne, $strTwo)
    {
        $this->prepare($strOne, $strTwo);
        return bcsub($strOne, $strTwo, $this->scale);
    }

    /**
     * @param $strOne
     * @param $strTwo
     * @return string
     * 乘法
     */
    public function mul($strOne, $strTwo)
    {
        $this->prepare($strOne, $strTwo);
        return bcmul($strOne, $strTwo, $this->scale);
    }

    /**
     * @param $strOne
     * @param $strTwo
     * @return string
     * 除法
     */
    public function div($strOne, $strTwo)
    {
        $this->prepare($strOne, $strTwo);
        //empty('0')为true
        if(empty($strTwo)){
            throw new CalculatorException(16003);
        }
        return bcdiv($strOne, $strTwo, $this->scale);
    }

    /**
     * @param $strOne
     * @param $strTwo
     * @throws CalculatorException
     * 转换并检查参数
     */
    public function prepare(&$strOne, &$strTwo)
    {
        $strOne = (string)$strOne;
        $strTwo = (string)$strTwo;
    }

    /**
     * @param $strOne
     * @param $strTwo
     * @param $symbol
     * @return string
     * @throws CalculatorException
     * 根据符号计算
     */
    public function calculateBySymbol($strOne, $strTwo, $symbol)
    {
        switch ($symbol){
            case '+': return $this->add($strOne, $strTwo);break;
            case '-': return $this->sub($strOne, $strTwo);break;
            case '*': return $this->mul($strOne, $strTwo);break;
            case '/': return $this->div($strOne, $strTwo);break;
            default : throw new CalculatorException(16004);
        }
    }
}