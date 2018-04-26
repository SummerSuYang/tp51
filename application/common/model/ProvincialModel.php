<?php
/**
 * User: suyang
 * Date: 2018/3/22 0022
 * Time: 14:52
 */

namespace app\common\model;

/*
|--------------------------------------------------------------------------
|                                     后台模块的省会模型
|--------------------------------------------------------------------------
*/

class ProvincialModel extends CommonModel
{
    public static function nameLists($scope=[],$where=[],$with=[],$order=[],$append = [])
    {
        //范围和关联
        $query = self::scope($scope)->with($with);

        //条件筛选
        if(!empty($where)) self::queryWhere($query, $where);

        //排序
        if( !empty($order)){
            foreach ($order as $item)
                $query->order($item[0], $item[1]);
        }

        $data = $query->select()->toArray();

        return ['data' => $data];
    }
    public function children()
    {
        return $this->hasMany('CityModel','pid','value');
    }

}