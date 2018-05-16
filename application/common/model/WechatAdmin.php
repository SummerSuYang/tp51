<?php
/*
|--------------------------------------------------------------------------
| Creator: SuYang
| Date: 2018/4/10 0010 18:24
|--------------------------------------------------------------------------
|                                                      说明
|--------------------------------------------------------------------------
*/

namespace app\common\model;

class WechatAdmin extends CommonModel
{
    protected $insert = [];
    protected $update = [];
    public function admin()
    {
        return $this->belongsTo('Admin', 'id', 'admin_id')->bind([
            'name', 'phone'
        ]);
    }
}