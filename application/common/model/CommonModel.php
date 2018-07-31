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

class CommonModel extends Model
{
    use SoftDelete;

    protected $deleteTime = 'delete_time';

    //每一页的数据数
    protected static $perPage = 10;

    public $dateFormat = 'Y-m-d H:i';

	/**
	 * @param $scope
	 * @param $where
	 * @param $with
	 * @param $order
	 * @return $this
	 * 获取query
	 */
    public static function prepareQuery($scope,$where,$with,$order)
    {
        $query = static::scope($scope)->with($with)->where($where);

        if(!empty($order)){
            foreach ($order as $item){
	            $query->order($item[0], $item[1]);
            }
        }

        return $query;
    }

	/**
	 * @param $scope
	 * @param $where
	 * @param $with
	 * @param $order
	 * @param $append
	 * @return array
	 */
    public static function fetchPaginate($scope,$where,$with,$order,$append)
    {
    	$query = static::prepareQuery($scope,$where,$with,$order);

	    //分页
	    $list = $query->paginate(static::$perPage);

	    //数据集合
	    $collection = $list->getCollection();

	    //追加字段
	    if(!empty($append)) {
		    $collection = $collection->append($append);
	    }

	    $data = $collection->toArray();

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
	 * @param $paginate
	 * 设置每页的数据个数
	 */
	public static function setPerPage($perPage)
	{
		if(isPositiveInteger($perPage)){
			static::$perPage = $perPage;
		}
	}

	/**
	 * @param $scope
	 * @param $where
	 * @param $with
	 * @param $order
	 * @param $append
	 * @return array|\PDOStatement|string|\think\Collection
	 * 获取数据集合
	 */
	public static function fetchCollection($scope,$where,$with,$order,$append)
	{
		$query = static::prepareQuery($scope,$where,$with,$order);

		//数据集合
		$collection = $query->select();

		//追加字段
		if(!empty($append)) {
			$collection = $collection->append($append);
		}

		return $collection;
	}

	/**
	 * @param $id
	 * @param $scope
	 * @param $with
	 * @param $append
	 * @return array|null|\PDOStatement|string|Model
	 * 获取一个对象
	 */
    public static function fetchById($id,$scope,$with,$append)
    {
        $record = static::scope($scope)->with($with)->find(['id' => $id]);

        if(is_null($record)) {
            return $record;
        } else{
            if( !empty($append)) {
                $record->append($append);
            }
            return $record;
        }
    }

    /**
     * @param $value
     * @return false|string
     * 创造时间获取器
     */
    public function getCreateTimeAttr($value)
    {
        return date($this->dateFormat, $value);
    }

    /**
     * @param $value
     * @return false|string
     * 修改时间获取器
     */
    public function getUpdateTimeAttr($value)
    {
        return date($this->dateFormat, $value);
    }
}