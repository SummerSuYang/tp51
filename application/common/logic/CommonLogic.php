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

use think\Exception;
use think\facade\Request;

abstract class CommonLogic
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
    //隐藏的字段
    protected $hidden = [];
    //向数据库写入的字段，也就是新建和更新时的字段
    protected $writeFields = [];

    //逻辑层的基础模型
    abstract public function model();

    //查找一条记录
    abstract public function getById();

    /**
     * @return mixed
     * 列表
     */
    public function getLists()
    {
        return ($this->model())::lists($this->scope, $this->where, $this->with, $this->order, $this->append, $this->hidden);
    }

    /**
     * @return array
     * @throws Exception
     * 新建
     */
    public function create()
    {
        try{
            $para = $this->fieldFilter();
            $new = ($this->model())::create($para);
            return ['id' => $new->id];
        }catch (Exception $e){
            throw $e;
        }
    }

    /**
     * @param $id
     * @return array
     * @throws Exception
     * 更新
     */
    public function updateById($id)
    {
        $record = $this->getById($id, false);
        try{
            $para = $this->fieldFilter();
            foreach ($para as $k => $v) $record->{$k} = $v;
            $record->save();

            return ['id' => $record->id];
        }catch (Exception $e){
            throw $e;
        }
    }

    /**
     * @param $id
     * @return array
     * @throws Exception
     * 删除
     */
    public function delete($id)
    {
        $record = $this->getById($id, false);
        try{
            $record->delete();

            return ['id' => $record->id];
        }catch (Exception $e){
            throw $e;
        }
    }

    /**
     * @return array
     * 获取不同请求方法下的字段
     */
    protected function fieldFilter()
    {
        $fields = [];
        if(method_exists($this, $method = Request::method().'Fields'))
            $fields = $this->{$method};

        return Request::only($fields, $method);
    }

    /**
     * @return array
     * 新建时的字段
     */
    protected function postFields()
    {
        return $this->writeFields;
    }

    /**
     * @return array
     * 更新时的字段
     */
    protected function putFields()
    {
        return $this->writeFields;
    }

    /**
     * @param $property
     * @param array $field
     * @return $this
     * 向某个成员数组追加一些字段
     */
    protected function append($property, $field = [])
    {
        if(property_exists($this, $property) && is_array( $this->{$propert})){
            if(is_string($field)) $field = explode(',', $field);
            $this->{$propert} = array_merge($this->{$propert}, $field);
            //去重
            array_unique($this->{$property});
        }

        return $this;
    }

    /**
     * @param $property
     * @param array $field
     * @return $this
     * 向某个数组成员移除一些字段，慎用
     */
    protected function remove($property, $field = [])
    {
        if(property_exists($this, $property) && is_array( $this->{$propert})){
            if(is_string($field)) $field = explode(',', $field);
            $this->{$property} = array_diff($this->{$property}, $field);
        }

        return $this;
    }

    /**
     * @param $property
     * @param array $field
     * @return $this
     * 只返回某个数组成员的一部分字段
     */
    protected function only($property, $field = [])
    {
        if(property_exists($this, $property) && is_array( $this->{$propert})){
            if(is_string($field)) $field = explode(',', $field);
            $this->{$property} = $field;
        }

        return $this;
    }
}