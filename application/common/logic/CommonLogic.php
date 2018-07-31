<?php
/*
|--------------------------------------------------------------------------
| Creator: SuYang
| Date: 2018/4/5 0005 14:24
|--------------------------------------------------------------------------
|                                                 基础逻辑层
|--------------------------------------------------------------------------
*/

namespace app\common\logic;

use app\common\contract\LogicContract;
use think\Exception;
use think\facade\Request;

abstract class CommonLogic implements LogicContract
{
    //查询数据库时的“范畴”
    protected $scope = [];
    //列表查询的时候的where条件
    protected $where = [];
    //查询关联
    protected $with = [];
    //查询排序
    protected $order = [];
    //查询追加
    protected $append = [];
    //允许向数据库写入的字段，也就是新建时允许字段
    protected $createFields = [];
	//向数据库写入的字段，也就是更新时的字段
	protected $updateFields = [];
	//向数据库查询的字段
	protected $queryField = [];
	//经过过滤器过滤后的字段
	protected $fields = [];
    //基础model，由抽象方法configModel指定
    private $model;

    //查找一条记录,返回model
    abstract public function getById($id);

    abstract protected function model();

    public function __construct()
    {
    	$this->model();

        $this->fieldFilter();
    }

	/**
	 * @return array
	 * 分页数据
	 */
    public function getPaginate()
    {
    	$this->orderFieldFilter();

        return ($this->model)::fetchPaginate(
            $this->scope, $this->where, $this->with, $this->order,
            $this->append
        );
    }

	/**
	 * @return mixed
	 * 返回所有的查询数据
	 */
    public function getCollection()
    {
    	$this->orderFieldFilter();

    	return ($this->model)::fetchCollection(
    		$this->scope, $this->where, $this->with, $this->order,
		    $this->append
	    );
    }

    /**
     * @return array
     * @throws Exception
     * 新建
     */
    public function create()
    {
    	$new = ($this->model)::create($this->fields);

    	return ['id' => $new->id];
    }

    /**
     * @param $id
     * @return array
     * @throws Exception
     * 更新
     */
    public function updateById($id)
    {
        $record = $this->getById($id);

        foreach ($this->fields as $k => $item){
        	$record->{$k} = $item;
        }

        $record->save();

        return ['id' => $record->id];
    }

    /**
     * @param $id
     * @return array
     * 删除
     */
    public function delete($id)
    {
        $record = $this->getById($id);

	    $record->delete();

	    return ['id' => $record->id];
    }

	/**
	 * 过滤从前端传过来的字段
	 */
    protected function fieldFilter()
    {
    	//允许向数据库写入的字段
        if (Request::isPost()){
        	$this->fields = Request::only($this->createFields, 'post');
        }

        //允许向数据库更新的字段
        if (Request::isPut()){
	        $this->fields = Request::only($this->updateFields, 'put');
        }

        //允许向数据库查询的字段
        if(Request::isGet()){
        	$this->fields = Request::only($this->queryField, 'get');
        }
    }

	/**
	 * 处理排序
	 */
    protected function orderFieldFilter()
    {
    	$this->order = [];
    }

	/**
	 * 查询搜索条件
	 */
    protected function buildWhere()
    {
    	$this->where = [];
    }
}