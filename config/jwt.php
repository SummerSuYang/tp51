<?php

use app\common\model\admin\AdminModel;

return [
    'admin' => [
        // token有效时间：s；如果不设置默认一个小时
        'expires_in' => 3600,
        'secret_key' => 'GlZpriHMdRo5ylMAu9zeFxbhIBNwPeOyw6104UT3',
        // 需要加入随机字符串
        'need_nonce_str' =>false,
        'model' => '',
    ],
];