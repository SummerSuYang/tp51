<?php
/*
|--------------------------------------------------------------------------
| Creator: SuYang
| Date: 2018/3/28 0028 17:25
|--------------------------------------------------------------------------
|                                                      说明
|--------------------------------------------------------------------------
*/

namespace app\common\model;

class Admin extends CommonModel
{
    protected $hidden = ['create_time', 'avatar'];

    public function scopeStatus($query)
    {
        return $query->where('status', 'in', [1,2]);
    }

    public function scopeShow($query)
    {
        return $query->where('status', 1);
    }

    public function getStatusTextAttr($value, $data)
    {
        $status = [1 => '生效中', 2 => '屏蔽中', 3 => '已删除'];
        return $status[$data['status']];
    }

    public function getGroupInfoAttr($value, $data)
    {
        $access = AuthAccess::where('uid',$data['id'])->select()->toArray();
        $access = current($access);
        $group = AuthGroup::getById($access['group_id']);
        return ['id' =>$group['id'], 'name' => $group['name']];
    }
}