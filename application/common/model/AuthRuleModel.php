<?php
/*
|--------------------------------------------------------------------------
| Creator: SuYang
| Date: 2018/4/12 0012 17:37
|--------------------------------------------------------------------------
|                                             权限规则表的模型
|--------------------------------------------------------------------------
*/

namespace app\common\model;

class AuthRuleModel extends CommonModel
{
    protected $hidden = [
        'path', 'level', 'name', 'icon', 'condition', 'remark', 'is_menu', 'wight',
        'status', 'create_time', 'update_time', 'delete_time'
    ];
}