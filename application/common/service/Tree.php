<?php
/*
|--------------------------------------------------------------------------
| Creator: Su Yang
| Date: 2018/6/13 0013 13:59
|--------------------------------------------------------------------------
|                                                      构造树
|--------------------------------------------------------------------------
*/

namespace app\common\service;

class Tree
{
	public $idField = 'id';
	public $pidField = 'pid';
	public $levelField = 'level';
	public $leafLevel = 0;
	public static $instance;

	/**
	 * @param array $elements
	 * @param int $root
	 * @return array
	 */
	public function buildTree(array &$elements, $root = 0)
	{
		if(empty($elements)) return $elements;

		$branch = [];

		foreach ($elements as &$element) {
			if ($element[$this->pidField] == $root) {
				if($element[$this->levelField] < $this->leafLevel){
					$children = $this->buildTree($elements, $element[$this->idField]);
					if(!empty($children)){
						$element['children'] = $children;
					}
				}
				/*else{
					$element['children'] = [];
				}*/
				$branch[] = $element;
				unset($element);
			}
		}
		return $branch;
	}

	/**
	 * @param $field
	 * @return $this
	 */
	public function setIdField($field)
	{
		$this->idField = $field;
		return $this;
	}

	/**
	 * @param $field
	 * @return $this
	 */
	public function setPidField($field)
	{
		$this->pidField = $field;
		return $this;
	}

	/**
	 * @param $field
	 * @return $this
	 */
	public function setLevelField($field)
	{
		$this->levelField = $field;
		return $this;
	}

	/**
	 * @param $level
	 * 叶子节点的层数
	 */
	public function setLeafLevel($level)
	{
		$this->leafLevel = $level;
		return $this;
	}
}