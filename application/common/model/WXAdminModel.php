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

class WXAdminModel extends CommonModel
{
    protected $insert = [];
    protected $update = [];
    public function admin()
    {
        return $this->belongsTo('AdminModel', 'id', 'admin_id')->bind([
            'name', 'phone'
        ]);
    }
}