<?php
/*
|--------------------------------------------------------------------------
| Creator: SuYang
| Date: 2018/3/27 0027 11:55
|--------------------------------------------------------------------------
|                                                   上传附件
|--------------------------------------------------------------------------
*/

namespace app\home\controller;
use app\lib\exception\UploadFileException;
use app\lib\traits\UploadToOSSPublic;
use app\common\controller\CommonController;
use app\lib\validate\UploadFile as validate;
use think\facade\Request;

class UploadFile extends CommonController
{
    use UploadToOSSPublic;

    /**
     * @return \think\response\Json
     * @throws UploadFileException
     * @throws \OSS\Core\OssException
     */
    public function uploadToPublic()
    {
        (new validate())->paramsCheck('', 'post');

        $type = Request::post('type') ? : 1;

        $data = $this->setOSSBucket('magicgogo')->uploadToOSS('', $type);

        return $this->response(1100, ['data' => $data]);
    }
}