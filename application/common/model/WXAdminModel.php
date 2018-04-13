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


use app\common\model\CommonModel;

class WXAdminModel extends CommonModel
{
    protected $table = 'wechat_admins';
    protected $insert = [];
    protected $update = [];
    public function admin()
    {
        return $this->belongsTo('AdminModel', 'id', 'admin_id')->bind([
            'name', 'phone'
        ]);
    }

}