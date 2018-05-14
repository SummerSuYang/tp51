<?php
/*
|--------------------------------------------------------------------------
| Creator: SuYang
| Date: 2018/5/11 0011 17:00
|--------------------------------------------------------------------------
|                                               公式计算器类
|--------------------------------------------------------------------------
*/

namespace app\common\service;

use app\lib\exception\FormulaException;

class Formula
{
    //计算器对象
    protected $calculator;
    //公式
    protected $formula;
    //存储原始的公式
    protected $originalFormula;
    //占位符迭代器
    protected $placeholder;
    //占位符与真实值之间的关系数组
    protected $replaceValue = [];
    //运算符优先级
    protected $symbolPriority = [
        '+' => 1,
        '-' => 1,
        '*' => 2,
        '/' => 2
    ];
    //占位符常量数组,用于生成迭代器
    const placeholderValue =  [
        'a', 'b', 'c', 'd', 'e', 'f', 'g', 'h', 'i', 'j', 'k', 'l', 'm', 'n', 'o', 'p', 'q', 'r', 's',
        't', 'u', 'v', 'w', 'x', 'y' ,'z'
    ];
    //返回的结果的小数点后的位数
    protected $resultScale = 2;

    public function __construct()
    {
        $this->calculator = new Calculator();

        //占位符数组迭代器
        $this->placeholder = new \ArrayIterator(self::placeholderValue);
        //归位
        $this->placeholder->rewind();
    }

    /**
     * 计算
     */
    public function calculate($formula, $value)
    {
        $this->formula = $this->originalFormula = $formula;

        //占位符替换
        $this->replace($value);

        //转化为后缀表达式
        $this->suffixFormula();

        //占位符迭代器归位
        $this->placeholder->rewind();

        //逆波兰计算法计算
        $result = $this->RPN();

        //四舍五入返回
        return $this->returnProperResult($result);
    }

    /**
     * 计算时替换所有的变量和常量
     */
    protected function replace($value)
    {
        //用占位符替代参数变量
        $mapVar = $this->variableReplace($value);

        //用占位符替代浮点型常量
        $mapFloat = $this->pregReplace('/[0-9]+\.[0-9]+/');

        //用占位符替代整型常量
        $mapInt = $this->pregReplace('/[1-9][0-9]*/');

        //正则检查
        if( !$this->pregCheckFormula()){
            throw new FormulaException(17003);
        }

        //占位符与真实值之间的关系映射
        $this->replaceValue = array_merge($mapVar, $mapFloat, $mapInt);
    }

    /**
     * @param $value
     * 替换参数变量
     * value是从数据库中查出来的参数值数组
     */
    protected function variableReplace(array $value)
    {
        preg_match_all('/ID\d+/', $this->formula, $match);

        $match = array_unique(current($match));

        $map = [];

        foreach ($match as $item){
            $key = (int)ltrim($item, 'ID');
            if( !$this->placeholder->valid()){
                throw new FormulaException([
                    'msg' => '公式:'.$this->originalFormula.'过长'
                ]);
            }
            if( !key_exists($key, $value)){
                throw new FormulaException([
                    'msg' => '缺少模板参数'.$key.'的值',
                ]);
            }
            $map[$this->placeholder->current()] = (string)$value[$key];
            $this->placeholder->next();
        }

        $this->formula = str_replace(array_values($match), array_keys($map), $this->formula);

        return $map;
    }

    /**
     * 用占位符替换特定的正则表达式
     */
    protected function pregReplace($preg)
    {
        preg_match_all($preg, $this->formula, $match);
        $match = array_unique(current($match));
        $map = [];
        foreach ($match as $item){
            if( !$this->placeholder->valid()){
                throw new FormulaException([
                    'msg' => '公式:'.$this->originalFormula.'过长'
                ]);
            }
            $map[$this->placeholder->current()] = $item;
            $this->placeholder->next();
        }

        $this->formula = str_replace(array_values($match), array_keys($map), $this->formula);

        return $map;
    }
    
    /**
     * 将中缀表达式转换成后缀表达式
     */
    protected function suffixFormula()
    {
        $stack = new \SplStack();

        //所有的符号
        $symbol = array_keys($this->symbolPriority);

        //结果
        $result= '';

        for($i = 0; $i < strlen($this->formula); $i++){
            $char = $this->formula[$i];
            //如果是符号
            if(in_array($char, $symbol)){
                //如果栈顶的符号优先级大于当前符号的优先级,则栈中的元素依
                //次出栈并作为结果字符串的一部分
                if( !$stack->isEmpty() && $this->priority($stack->top(), $char)){
                    while ( !$stack->isEmpty()){
                        //依次弹出栈顶元素
                        $result.= $stack->pop();
                    }
                }

                //将当前符号压入栈
                $stack->push($char);
            }
            //不是符号就是数字，数字不用进栈直接作为结果字符串的一部分
            else{
                $result.= $char;
            }
        }
        //将栈中剩余的元素依次全部出栈
        while ( !$stack->isEmpty()){
            $result.= $stack->pop();
        }

        $this->formula = $result;

        $this->destroyStack($stack);
    }

    /**
     * 逆波兰算法计算
     */
    protected function RPN()
    {
        $stack = new \SplStack();

        //所有的符号
        $symbol = array_keys($this->symbolPriority);

        for($i = 0; $i < strlen($this->formula); $i++){
            $char = $this->formula[$i];
            if( !in_array($char, $symbol)){
                if( !key_exists($char, $this->replaceValue)){
                    throw new FormulaException([
                        'msg' => '找不到占位符的值。公式为：'.$this->originalFormula.'。占位符为：'.$char,
                    ]);
                }
                $stack->push($this->replaceValue[$char]);
            }
            else{
                //栈连续弹出两次
                if($stack->isEmpty()){
                    throw new FormulaException(17001);
                }
                $two = $stack->pop();
                if($stack->isEmpty()){
                    throw new FormulaException(17001);
                }
                $one = $stack->pop();
                //计算, 计算的精度比返回的精度多一位
                $result = $this->calculator->setScale($this->resultScale + 1)
                                                              ->calculateBySymbol($one, $two, $char);
                //压入栈
                $stack->push($result);
            }
        }

        //结果
        $result = $stack->pop();

        $this->destroyStack($stack);

        return $result;
    }

    /**
     * @param $symbolOne
     * @param $symbolTwo
     * @return bool
     * 比较符号优先级
     */
    protected function priority($symbolOne, $symbolTwo)
    {
        return $this->symbolPriority[$symbolOne] > $this->symbolPriority[$symbolTwo];
    }

    /**
     * @param $result
     * @return string
     * @throws FormulaException
     * 返回精确地结果
     */
    protected function returnProperResult($result)
    {
        //计算精度
        $calculateScale = $this->calculator->returnScale();
        //计算精度与返回结果的精度之间的差值
        $gap = $calculateScale - $this->resultScale;
        //计算精度必须大于返回结果的精度，不然结果可能不准
        if($gap <= 0){
            throw new FormulaException(17002);
        }

        //进行四舍五入操作
        $subLength = strlen($result) - $gap - 1;
        $str = substr($result, 0, $subLength);
        $edgeChar = $result[$subLength];
        $overflow = $result[$subLength + 1];
        if((int)$overflow >= 5){
            $edgeChar =(string)((int)$edgeChar + 1);
        }

        return $str.$edgeChar;
    }

    /**
     * @param $stack
     * 销毁栈
     */
    protected function destroyStack($stack)
    {
        $stack = null;
        unset($stack);
    }

    /**
     * @return bool
     * @throws FormulaException
     */
    protected function pregCheckFormula()
    {
        return preg_match('/^([a-z][\+\-\*\/])+[a-z]$/', $this->formula);
    }

    /**
     * @param $formula
     * @throws FormulaException
     * 检查公式是否合法
     */
    public function check($formula)
    {
        $this->formula = $this->originalFormula = $formula;

        //用占位符替代变量
        $this->pregReplace('/ID\d+/');

        //用占位符替代浮点型常量
        $this->pregReplace('/[0-9]+\.[0-9]+/');

        //用占位符替代整型常量
        $this->pregReplace('/[1-9][0-9]*/');

        //占位符迭代器归位
        $this->placeholder->rewind();

        return $this->pregCheckFormula();
    }
}