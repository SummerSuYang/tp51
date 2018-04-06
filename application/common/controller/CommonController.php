<?php

namespace app\common\controller;

use app\lib\enum\Version;
use think\Controller;
use think\facade\Request;
use app\common\service\JWTAuth;

/*
|--------------------------------------------------------------------------
|                                      项目的基础控制器
|--------------------------------------------------------------------------
*/
class CommonController extends Controller
{
    //逻辑层与验证器，需要子类通过构造函数依赖注入
    protected $logic;
    protected $validate;

    /**
     * @param $return
     * @param array $data
     * @return \think\response\Json
     * 通用返回
     */
    public function response($return, $data = [])
    {
        list($httpCode, $msg) = CodeToResponse::show($return);

        $returnArray = [
            'msg' => $msg,
            'err_code' => 0,
            'request_url' => Request::url()
        ];

        if (key_exists('paginate', $data))
            $returnArray = array_merge($returnArray, $data['paginate']);

        if (key_exists('data', $data))
            $returnArray['data'] = $data['data'];

        $header = [
            'version' => Version::CURRENT_VERSION,
            'token' => JWTAuth::getReturnToken(),
        ];

        return $this->responseType($returnArray, $httpCode, $header);
    }

    /**
     * @param $returnArray
     * @param $httpCode
     * @param $header
     * @param string $type
     * @return \think\response\Json
     */
    protected function responseType($returnArray, $httpCode, $header, $type = 'json')
    {
        if ($type == 'json')
            return json($returnArray, $httpCode, $header);
    }

    /**
     * @return \think\response\Json
     * 列表
     */
    public function index()
    {
        $data = $this->logic->getLists();
        return $this->response(1201, $data);
    }

    /**
     * @param $id
     * @return \think\response\Json
     * 一个对象
     */
    public function read($id)
    {
        $data = $this->logic->getById($id);

        return $this->response(1201, ['data' => $data]);
    }

    /**
     * @return \think\response\Json
     * 新建
     */
    public function save()
    {
        $this->validate->paramsCheck('create', 'post');

        $data = $this->logic->create();

        return $this->response(1100, ['data' => $data]);
    }

    /**
     * @param $id
     * @return \think\response\Json
     * 更新
     */
    public function update($id)
    {
        $this->validate->paramsCheck('update', 'put');

        $data = $this->logic->updateById($id);

        return $this->response(1301, ['data' => $data]);
    }

    /**
     * @param $id
     * @return \think\response\Json
     * 删除
     */
    public function delete($id)
    {
        $data = $this->logic->delete($id);

        return $this->response(1401, ['data' => $data]);
    }
}