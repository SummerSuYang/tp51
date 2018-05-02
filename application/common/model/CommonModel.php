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

use app\common\service\CurrentUser;
use think\Exception;
use think\model\concern\SoftDelete;
use think\Model;
use app\common\service\JWTAuth;

class CommonModel extends Model
{
    use SoftDelete;

    protected $deleteTime = 'delete_time';
    protected $type = [
        'create_time' => 'integer',
        'update_time' => 'integer',
        'delete_time' => 'integer',
		'status' => 'integer'
    ];
    protected $insert = ['admin'];
    protected $update = ['admin'];
    //每一页的数据数
    protected static $perPage = 10;

    /**
     * 分页列表
     */
    protected static function lists(
        $scope=[],$where=[],$with=[],$order=[],$append=[],$hidden=[]
    )
    {
        //范围和关联
        $query = static::scope($scope)->with($with);

        //条件筛选
        if(!empty($where)) {
            static::queryWhere($query, $where);
        }

        //排序
        if( !empty($order)){
            foreach ($order as $item)
                $query->order($item[0], $item[1]);
        }

        //分页
        $list = $query->paginate(static::$perPage);

        //数据集合
        $collection = $list->getCollection();

        //追加字段
        if(!empty($append)) {
            $collection = $collection->append($append);
        }

        //隐藏字段
        if(!empty($hidden)) {
            $collection = $collection->hidden($hidden);
        }

        return static::listReturn($collection->toArray(), $list);
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
    protected static function getById($id, $scope = [], $with = [], $append = [], $hidden = [])
    {
        $record = static::scope($scope)->with($with)->find(['id' => $id]);
        if(is_null($record)) {
            return $record;
        } else{
            if( !empty($append)) {
                $record->append($append);
            }
            if( !empty($hidden)) {
                $record->hidden($hidden);
            }

            return $record;
        }
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
    public function setAdminAttr()
    {
        return CurrentUser::getAccountAttribute('name') ? : '';
    }

    /**
     * @param $paginate
     * 设置每页的数据个数
     */
    public static function setPerPage($perPage)
    {
        if(isPositiveInteger($perPage))
            static::$perPage = $perPage;
    }
}