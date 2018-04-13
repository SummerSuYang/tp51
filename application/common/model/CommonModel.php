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
use think\model\concern\SoftDelete;
use think\Model;
use app\common\service\JWTAuth;

class CommonModel extends Model
{
    use SoftDelete;

    protected $autoWriteTimestamp = true;
    protected $resultSetType = 'collection';
    protected $deleteTime = 'delete_time';
    protected $type = [
        'create_time' => 'integer',
        'update_time' => 'integer',
		'status' => 'interger'
    ];
    protected $insert = ['last_operator'];
    protected $update = ['last_operator'];

    /**
     * 分页列表
     */
    public static function lists(
        $scope=[],$where=[],$with=[],$order=[],$append = [],$hidden = []
    )
    {
        $query = static::baseQuery($scope, $where, $with, $order);

        //分页
        $list = $query->paginate(10);

        //数据集合
        $collection = $list->getCollection();

        //追加字段
        if(!empty($append)) $collection = $collection->append();

        //隐藏字段
        if(!empty($hidden)) $collection = $collection->hidden($hidden);

        return self::listReturn($collection->toArray(), $list);
    }

    /**
     *获取集合
     */
    public function getCollections(
        $scope=[],$where=[],$with=[],$order=[],$append = [],$hidden = [],$fields = []
    )
    {
        $query = static::baseQuery($scope, $where, $with, $order);

        if(!empty($fields)) $collection = $query->field($fields)->select();
        else $collection = $query->select();

        //追加字段
        if(!empty($append)) $collection = $collection->append();

        //隐藏字段
        if(!empty($hidden)) $collection = $collection->hidden($hidden);

        return $collection;
    }

    /**
     * 基础的query
     */
    public static function baseQuery($scope=[],$where=[],$with=[],$order=[])
    {
        //范围和关联
        $query = static::scope($scope)->with($with);

        //条件筛选
        if(!empty($where)) static::queryWhere($query, $where);

        //排序
        if( !empty($order)){
            foreach ($order as $item)
                $query->order($item[0], $item[1]);
        }

        return $query;
    }

    /**
     * @param $data
     * @param $list
     * @return array
     * 通用的分页数据
     */
    public static function listReturn($data, $list)
    {
        return [
            'paginate' =>
                [
                    'total' => $list->total(),
                    'per_page' => $list->listRows(),
                    'current_page' => $list->currentPage(),
                    'last_page' => $list->lastPage(),
                ],
            'data' => $data,
        ];
    }

    /**
     * 获取一个对象
     */
    public static function getById($id, $scope = [], $with = [], $append = [], $hidden = [])
    {
        $record = static::scope($scope)->with($with)->find(['id' => $id]);
        if(is_null($record)) return null;
        else return $record->hidden($hidden)->append($append);
    }

    /**
     * @param $query
     * @param $where
     * @return mixed
     * 列表的筛选项
     */
    public static function queryWhere($query, $where)
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
        else return 'unknown';
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
}