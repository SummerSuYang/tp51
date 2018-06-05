<?php
/*
|--------------------------------------------------------------------------
| Creator: SuYang
| Date: 2018/5/11 0011 17:00
|--------------------------------------------------------------------------
|                                               公式计算器类
|--------------------------------------------------------------------------
|这个类有两个接口：
|1.calculate方法。通过公式和公式中“参数变量”的值计算公式的结果
|2.check方法。检查一个公式是否合法。在calculate方法中会自动判断公式
|是否合法因此在计算时不需要调用此方法。
|--------------------------------------------------------------------------
*/

namespace app\common\service;

use app\lib\exception\FormulaException;

class Formula extends Calculator
{
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
        'a', 'b', 'c', 'd', 'e', 'f', 'g', 'h', 'i', 'j', 'k', 'm', 'n', 'o', 'p', 'q', 'r', 's',
        't', 'u', 'v', 'w', 'x', 'y' ,'z'
    ];
    //返回的结果的小数点后的位数
    protected $resultScale = 2;
    //整型常量的正则
    protected $pregInt = '/[0-9]+/';
    //浮点型常量的正则
    protected $pregFloat = '/[0-9]+\.[0-9]+/';
    //变量的正则
    protected $pregVar = '/ID\d+/';

    public function __construct()
    {
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
        $this->formula = $this->originalFormula = trim($formula);

        //占位符替换
        $this->replace($value);

        //转化为后缀表达式
        $this->suffixFormula();

        //占位符迭代器归位
        $this->placeholder->rewind();

        //逆波兰计算法计算
        $result = $this->RPN();

        return $result;
        //四舍五入返回
        //return $this->returnProperResult($result);
    }

    /**
     * 计算时替换所有的变量和常量
     */
    protected function replace($value)
    {
        //用占位符替代参数变量
        $mapVar = $this->variableReplace($value);

        //用占位符替代浮点型常量
        $mapFloat = $this->pregReplace($this->pregFloat);

        //用占位符替代整型常量
        $mapInt = $this->pregReplace($this->pregInt);

        //正则检查
        if( !$this->checkFormula()){
            throw new FormulaException([
            	'code' => 400,
                'msg' => '公式 '.$this->originalFormula.' 格式错误']);
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
        preg_match_all($this->pregVar, $this->formula, $match);

        $match = array_unique(current($match));

        //比如公式为ID111+ID1111,如果先替换ID111那么公式就会变成类似
	    //于a+a1的形式。因此要对变量进行降序排序才能保证替换的准确性
        arsort($match);

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

        arsort($match);

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

        //结果
        $result= '';

        for($i = 0; $i < strlen($this->formula); $i++){
            $char = $this->formula[$i];

            //如果是符号
            if($this->isSymbol($char)){
            	//如果是左括号就进栈
            	if($char == '('){
		            $stack->push($char);
	            }

	            //如果是右括号就弹出栈元素直到遇到左括号为止
	            elseif ($char == ')'){
            		while ($stack->top() != '('){
			            $result.= $stack->pop();
		            }
		            $stack->pop();
	            }

	            //若果是加减乘除符号
	            else{
		            //如果栈顶的符号优先级大于等于当前符号的优先级,则栈中的符号
		            //出栈并作为结果字符串的一部分
		            while( !$stack->isEmpty() && $this->priority($stack->top(), $char)){
			            //依次弹出栈顶元素
			            $result.= $stack->pop();
		            }

		            //将当前符号压入栈
		            $stack->push($char);
	            }
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

        for($i = 0; $i < strlen($this->formula); $i++){
            $char = $this->formula[$i];
            if( !$this->isSymbol($char)){
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
                $result = $this->setCalculateScale($this->resultScale)
                                         ->calculateBySymbol($one, $two, $char);
                //压入栈
                $stack->push($result);
            }
        }

	    if($stack->isEmpty()){
		    throw new FormulaException(17001);
	    }
        //结果
        $result = $stack->pop();

        if( !$stack->isEmpty()){
        	throw new FormulaException(17004);
        }

        $this->destroyStack($stack);

        return $result;
    }

    /**
     * 比较符号优先级
     */
    protected function priority($symbolOne, $symbolTwo)
    {
    	if($symbolOne == '('){
    		return false;
	    }
        return  $this->symbolPriority[$symbolOne] >=
                      $this->symbolPriority[$symbolTwo];
    }

    /**
     * 返回合适地结果
     */
    protected function returnProperResult($result)
    {
    	//小数点的位置
    	$potPosition = strpos($result, '.');
    	if( !$potPosition){
    		return $result.'.00';
	    }

    	//整数部分，包括小数点
    	$integer = substr($result, 0, $potPosition + 1);

    	//小数部分
    	$decimal = substr($result, $potPosition + 1);

    	$gap = strlen($decimal) - $this->resultScale;

    	if($gap == 0){
    		return $result;
	    }
	    elseif ($gap < 0){
    		$gap = 0 - $gap;
    		for($i = 0; $i < $gap; $i++){
    			$result.= '0';
		    }

		    return $result;
	    }
	    else{
		    $decimal = substr($decimal, 0, $this->resultScale);
		    return $integer.$decimal;
	    }
    }

    /**
     * @param $stack
     * 销毁计算中所使用的栈
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
        //return preg_match('/^(\(*[a-z]\)*[\+\-\*\/])+[a-z]\)*$/', $this->formula);
	    return preg_match('/^(\(*[a-z]\)*[\+\-\*\/])*[a-z]*\)*$/', $this->formula);
    }

	/**
	 * @return bool
	 * 检查左右括号的个数是否相等
	 */
    protected function parenthesisCheckFormula()
    {
    	return substr_count($this->formula, '(') ===
	                substr_count($this->formula, ')');
    }

	/**
	 * @return bool
	 * @throws FormulaException
	 * 检查公式格式
	 */
    protected function checkFormula()
    {
    	return $this->parenthesisCheckFormula() &&
	                 $this->pregCheckFormula();
    }
    /**
     * @param $formula
     * @throws FormulaException
     * 检查公式是否合法
     */
    public function check($formula)
    {
        $this->formula = $this->originalFormula = trim($formula);

        //用占位符替代变量
        $this->pregReplace($this->pregVar);

        //用占位符替代浮点型常量
        $this->pregReplace($this->pregFloat);

        //用占位符替代整型常量
        $this->pregReplace($this->pregInt);

        //占位符迭代器归位
        $this->placeholder->rewind();

        return $this->pregCheckFormula();
    }

    /**
     * @param $char
     * @return bool
     * 判断一个字符是不是“加减乘除”符号
     */
    protected function isSymbol($char)
    {
        $symbols = array_keys($this->symbolPriority);
        $symbols = array_merge($symbols, ['(',')']);
        return in_array($char, $symbols);
    }
}