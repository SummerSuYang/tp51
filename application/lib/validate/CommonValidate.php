<?php

namespace app\lib\validate;

use app\common\contract\ValidateContract;
use app\lib\exception\ParamsException;
use think\facade\Request;
use think\Validate;

class CommonValidate extends Validate implements ValidateContract
{
    /**
     * 用于对参数进行批量校验
     *
     * @param string $scene 支持场景教研
     * @param string $method 可指定http方法
     * @return bool
     * @throws ParamsException
     */
    public function paramsCheck($scene = '', $method = 'param', $batch = false)
    {
        $params = Request::{$method}(); // 获取所有参数

        $result = $this->scene($scene)->batch($batch)->check($params); // 批量校验
        if (!$result) {
            throw new ParamsException([
                'msg' => $this->error
            ]);
        }

        return true;
    }

    // 不允许为空
    protected function isNotEmpty($value, $rule = '', $data = '', $field = '')
    {
        if (empty($value)) {
            return $field . '不允许为空';
        } else {
            return true;
        }
    }

    // 必须是正整数
    protected function isPositiveInteger($value, $rule = '', $data = '', $field = '')
    {
        if (isPositiveInteger($value, $rule)) {
            return true;
        }
        return $field . '必须是正整数';
    }

    protected function checkValueByRegex($value, $rule = '', $data = '', $field = '')
    {
        if (empty($value)) {
            return false;
        }

        return regex($value, $rule);
    }

	/**
	 * @return $this
	 * 新建时的应用场景
	 */
    protected function sceneCreate()
    {
        return $this;
    }

	/**
	 * @return $this
	 * 更新时的应用场景
	 */
    protected function sceneUpdate()
    {
        return $this;
    }

	/**
	 * @return $this
	 * 列表的应用场景
	 */
	protected function sceneIndex()
	{
		return $this;
	}

	/**
	 * @return $this
	 * 对象的应用场景
	 */
	protected function sceneRead()
	{
		return $this;
	}

	/**
	 * @return $this
	 * 删除的应用场景
	 */
	protected function sceneDelete()
	{
		return $this;
	}
}