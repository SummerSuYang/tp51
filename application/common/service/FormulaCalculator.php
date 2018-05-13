<?php
/*
|--------------------------------------------------------------------------
| Creator: SuYang
| Date: 2018/5/11 0011 17:00
|--------------------------------------------------------------------------
|                                                      说明
|--------------------------------------------------------------------------
*/

namespace app\common\service;


use app\lib\exception\CalculatorFormulaException;
use think\Exception;

class FormulaCalculator
{
    //计算器对象
    protected $calculator;
    //公式
    protected $formula;
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
    //占位符常量数组
    const placeholderValue =  [
        'a', 'b', 'c', 'd', 'e', 'f', 'g', 'h', 'i', 'j', 'k', 'l', 'm',
        'n', 'o', 'p', 'q', 'r', 's', 't', 'u', 'v', 'w', 'x', 'y' ,'z'
    ];

    public function __construct(Calculator $calculator)
    {
        $this->calculator = $calculator;

        //占位符数组迭代器
        $this->placeholder = new \ArrayIterator(self::placeholderValue);
        //归位
        $this->placeholder->rewind();
    }

    public function handle($formula, $value)
    {
        $this->formula = $this->originalFormula = $formula;

        $this->middleFormula($value);


        $this->suffixFormula();

        $this->placeholder->rewind();

        return $this->RPN();
    }

    /**
     * @param $value
     * 转换成中缀表达式，所有的变量和常量均有占位符替换
     */
    protected function middleFormula($value)
    {
        $this->replaceVariable($value);

        $this->replaceConst();
    }

    /**
     * @param $value
     * 替换参数变量
     * value是从数据库中查出来的参数值数组
     */
    protected function replaceVariable(array $value)
    {
        preg_match_all('/ID\d+/', $this->formula, $match);

        $match = array_unique(current($match));

        $new = [];

        foreach ($match as $item){
            $key = (int)ltrim($item, 'ID');
            if( !$this->placeholder->valid()){
                throw new CalculatorFormulaException(['msg' => '公式:'.$this->originalFormula.'过长']);
            }
            $new[$this->placeholder->current()] = (string)$value[$key];
            $this->placeholder->next();
        }

        //将公式里面的参数变量用占位符替换
        $this->formula = str_replace(array_values($match), array_keys($new), $this->formula);
        $this->replaceValue = array_merge($this->replaceValue, $new);
    }

    /**
     *替换常量
     */
    protected function replaceConst()
    {
        $this->replaceConstFloat();

        $this->replaceConstInt();
    }

    /**
     * 替换浮点型常量
     */
    protected function replaceConstFloat()
    {
        preg_match_all('/[0-9]+\.[0-9]+/', $this->formula, $match);

        $match = array_unique(current($match));

        $new = [];

        foreach ($match as $item){
            if( !$this->placeholder->valid()){
                throw new CalculatorFormulaException(['msg' => '公式:'.$this->originalFormula.'过长']);
            }
            $new[$this->placeholder->current()] = (string)$item;
            $this->placeholder->next();
        }

        $this->formula = str_replace(array_values($match), array_keys($new), $this->formula);
        $this->replaceValue = array_merge($this->replaceValue, $new);
    }

    /**
     * 替换整型常量
     */
    protected function replaceConstInt()
    {
        preg_match_all('/[1-9][0-9]*/', $this->formula, $match);

        $match = array_unique(current($match));

        $new = [];

        foreach ($match as $item){
            if( !$this->placeholder->valid()){
                throw new CalculatorFormulaException(['msg' => '公式:'.$this->originalFormula.'过长']);
            }
            $new[$this->placeholder->current()] = (string)$item;
            $this->placeholder->next();
        }

        $this->formula = str_replace(array_values($match), array_keys($new), $this->formula);
        $this->replaceValue = array_merge($this->replaceValue, $new);
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
                //如果栈顶的符号优先级大于当前符号的优先级
                if( !$stack->isEmpty() && $this->priority($stack->top(), $char)){
                    while ( !$stack->isEmpty()){
                        //依次弹出栈顶元素
                        $result.= $stack->pop();
                    }
                }

                //将当前符号压入栈
                $stack->push($char);
            }
            else{
                $result.= $char;
            }
        }
        while ( !$stack->isEmpty()){
            $result.= $stack->pop();
        }

        $this->formula = $result;
    }

    /**
     * @return mixed
     * @throws CalculatorFormulaException
     * @throws \app\lib\exception\CalculatorException
     * 逆波兰算法
     */
    protected function RPN()
    {
        $stack = new \SplStack();

        //所有的符号
        $symbol = array_keys($this->symbolPriority);

        for($i = 0; $i < strlen($this->formula); $i++){
            $char = $this->formula[$i];
            if( !in_array($char, $symbol)){
                $stack->push($this->replaceValue[$char]);
            }
            else{
                //栈连续弹出两次
                if($stack->isEmpty()){
                    throw new CalculatorFormulaException(17001);
                }
                $two = $stack->pop();
                if($stack->isEmpty()){
                    throw new CalculatorFormulaException(17001);
                }
                $one = $stack->pop();
                //计算
                $result = $this->calculator->calculateBySymbol($one, $two, $char);
                //压入栈
                $stack->push($result);
            }
        }

        return $stack->pop();
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
}