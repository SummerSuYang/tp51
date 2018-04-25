<?php
/*
|--------------------------------------------------------------------------
| Creator: SuYang
| Date: 2018/4/5 0005 14:11
|--------------------------------------------------------------------------
|                                                 基础模型
|--------------------------------------------------------------------------
*/


namespace app\common\model;

use think\Exception;
use think\model\concern\SoftDelete;
use think\Model;
use app\common\service\JWTAuth;

class BaseModel extends Model
{
    use SoftDelete;

    protected $autoWriteTimestamp = true;
    protected $resultSetType = 'collection';
    protected $deleteTime = 'delete_time';
    protected $type = [
        'create_time' => 'integer',
        'update_time' => 'integer',
		'status' => 'integer'
    ];
    protected $insert = ['last_operator'];
    protected $update = ['last_operator'];
    protected $paginate = 10;
    private static $instance = null;

    /**
     * 分页列表
     */
    protected function lists(
        $scope=[],$where=[],$with=[],$order=[],$append=[],$hidden=[]
    )
    {
        //范围和关联
        $query = static::scope($scope)->with($with);

        //条件筛选
        if(!empty($where)) $this->queryWhere($query, $where);

        //排序
        if( !empty($order)){
            foreach ($order as $item)
                $query->order($item[0], $item[1]);
        }

        //分页
        $list = $query->paginate($this->paginate);

        //数据集合
        $collection = $list->getCollection();

        //追加字段
        if(!empty($append)) $collection = $collection->append($append);

        //隐藏字段
        if(!empty($hidden)) $collection = $collection->hidden($hidden);

        return $this->listReturn($collection->toArray(), $list);
    }

    /**
     * @param $data
     * @param $list
     * @return array
     * 通用的分页数据
     */
    public function listReturn($data, $list)
    {
        return [
            'paginate' =>
                [
                    'total' => (int)$list->total(),
                    'per_page' => (int)$list->listRows(),
                    'current_page' => (int)$list->currentPage(),
                    'last_page' => (int)$list->lastPage(),
                ],
            'data' => $data,
        ];
    }

    /**
     * 获取一个对象
     */
    protected function getById($id, $scope = [], $with = [], $append = [], $hidden = [])
    {
        $record = static::scope($scope)->with($with)->find(['id' => $id]);
        if(is_null($record)) {
            return null;
        } else{
            return $record->hidden($hidden)->append($append);
        }
    }

    /**
     * @param $query
     * @param $where
     * @return mixed
     * 列表的筛选项
     */
    public function queryWhere($query, $where)
    {
        return $query;
    }

    /**
     * @return string
     * 自动写入操作人
     */
    public function setLastOperatorAttr()
    {
        if( !empty($account = JWTAuth::getAccount()))
            return $account->getAttr('name');
        else return '';
    }

    /**
     * @param $value
     * @return false|string
     * 创造时间获取器
     */
    public function getCreateTimeAttr($value)
    {
        return date('Y-m-d H:i', $value);
    }

    /**
     * @param $value
     * @return false|string
     * 修改时间获取器
     */
    public function getUpdateTimeAttr($value)
    {
        return date('Y-m-d H:i', $value);
    }

    /**
     * @param $paginate
     * 设置每页的数据个数
     */
    protected function setPaginate($paginate)
    {
        if(isPositiveInteger($paginate))
            $this->paginate = $paginate;

        return $this;
    }

    /**
     * @param $method
     * @param $args
     * @return mixed
     * 静态调用类的方法
     */
    public static function __callStatic($method, $args)
    {
        if(is_null(static::$instance))
            static::$instance = new static();

        if( !method_exists(static::$instance, $method)){
            throw new Exception("无法调用 $method 方法");
        }
        return call_user_func_array([static::$instance, $method], $args);
    }
}