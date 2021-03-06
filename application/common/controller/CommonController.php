<?php

namespace app\common\controller;

use app\common\contract\ControllerContract;
use app\common\contract\LogicContract;
use app\common\contract\ValidateContract;
use app\common\service\CurrentUser;
use app\lib\enum\Version;
use think\Controller;
use think\Exception;
use think\facade\Request;

/*
|--------------------------------------------------------------------------
|                                      项目的基础控制器
|--------------------------------------------------------------------------
*/
class CommonController extends Controller implements ControllerContract
{
    //逻辑层与验证器，需要子类通过构造函数依赖注入
    protected $logic;

    protected $validate;

    protected $type = 'json';

    public function __construct(LogicContract $logic, ValidateContract $validate)
    {
        parent::__construct();
    }

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
            'token' => CurrentUser::getResponseToken(),
        ];

        if(!method_exists($this, $method = 'response'.ucfirst($this->type))){
        	$msg = '无法返回类型为'.$this->type.'类型的数据';
            throw new Exception($msg);
        }

        return call_user_func_array([$this, $method], [$returnArray, $httpCode, $header]);
    }

    /**
     * @param $returnArray
     * @param $httpCode
     * @param $header
     * @param string $type
     * @return \think\response\Json
     */
    protected function responseJson($returnArray, $httpCode, $header)
    {
        return json($returnArray, $httpCode, $header);
    }

    /**
     * @return \think\response\Json
     * 列表
     */
    public function index()
    {
	    $this->validate->paramsCheck('index', 'get');

        $data = $this->logic->getPaginate();

        return $this->response(1201, $data);
    }

    /**
     * @param $id
     * @return \think\response\Json
     * 一个对象
     */
    public function read($id)
    {
	    $this->validate->paramsCheck('read', 'get');

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
	    $this->validate->paramsCheck('delete', 'delete');

        $data = $this->logic->delete($id);

        return $this->response(1401, ['data' => $data]);
    }
}