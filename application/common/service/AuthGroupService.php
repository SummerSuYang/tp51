<?php
/*
|--------------------------------------------------------------------------
| Creator: SuYang
| Date: 2018/3/28 0028 17:24
|--------------------------------------------------------------------------
|                                                    鉴权服务
|--------------------------------------------------------------------------
|三个基础方法
|getGroup需要传递一个user id来获取用户所在的组一个用户可以在多个组
|
|getRulesId 根据group id来获取rule id，因为group id可能多个所以该函
|数主要是合并去重
|
|getRulesList 通过rule id来获取权限列表，可是是平行结构也可以是树形结
|构
|
|五个对外接口
|userAccessListId需要传递一个user id来获取用户的rule id。实际是对
|getGroup和getRulesId的封装
|
|userAccessList 返回用户的权限列表是对userAccessListId和getRulesList
|的封装
|
|checkAccess 检查用户的权限。先根据路由获得该rule id。然后使用
|userAccessListId 获取该用户所有的rule id并判断当前路由id是否在里面
|
|allAccessList() 所有权限
|
|buildTree 将平行结构转化为树形结构
|--------------------------------------------------------------------------
*/

namespace app\common\service;

use app\lib\exception\AuthException;
use think\Db;
use think\facade\Cache;
use think\facade\Request;

class AuthGroupService
{
    public static $parentIds = [];
    public static $fields = ['id', 'pid', 'name', 'title'];
    /**
     * 获取用户所在的权限组，如果uid为空字符串就返回所有的权限组
     */
    protected static function getGroup($uid = '')
    {
        //user-group表的表名
        $auth_group_access = static::userGroup();
        //group表的表名
        $auth_group = static::groupRules();

        $where[] = ["{$auth_group}.status", '=', 1];
        if(is_numeric($uid))
            $where[] = ["{$auth_group_access}.uid", '=', $uid];

        return Db::view($auth_group_access, 'uid,group_id')
                       ->view($auth_group, 'name,rules', "{$auth_group_access}.group_id={$auth_group}.id", 'LEFT')
                       ->where($where)
                       ->select()->toArray();
    }

    /**
     * @param $groups
     * @return array
     * 返回一个或几个权限组的权限id
     * groups 是一个二维数组
     */
    protected static function getRulesId($groups)
    {
        if(empty($groups)) return [];
        $rulesId = [];
        //一个用户可能在多个组里面
        foreach ($groups as $item)
            $rulesId = array_merge($rulesId, explode(',',$item['rules']));
        if(empty($rulesId)) return [];
        else return array_unique($rulesId);
    }

    /**
     * 根据权限id返回权限列表
     */
    protected static function getRulesList($rulesId, $tree = true, $pid = 0)
    {
        if(empty($rulesId)) return [];

        $where = [
            ['id','in',$rulesId],
            ['status', '=', 1],
        ];
        //读取用户组所有权限规则
        $rules = Db::name(static::rules())->where($where)
                         ->field(static::$fields)->select()->toArray();

        if($tree) return static::buildTree($rules,$pid);
        else return $rules;
    }

    /**
     * @param $uid
     * @return array
     * 返回一个用户的权限
     */
    public static function userAccessList($uid, $tree = true, $pid = 0)
    {
        $rulesId = static::userAccessListId($uid);
        //默认以树形结构返回用户所有的权限
        return static::getRulesList($rulesId, $tree, $pid);
    }

    /**
     * @param $uid
     * @return array
     * 返回用户权限的id
     */
    public static function userAccessListId($uid)
    {
        //用户所在的权限组，可以是多个
        $groups = static::getGroup($uid);
        // 用户所在的权限组所拥有的的id集合
        return static::getRulesId($groups);
    }

    /**
     * 返回所有权限
     */
    public static function allAccessList()
    {
        $where = [
            ['status', '=', 1],
        ];
        //读取用户组所有权限规则
       return  Db::name(static::rules())->where($where)
                       ->field(static::$fields)->select()->toArray();
    }

    /**
     * 检查用户是否有调用接口的权限
     */
    public static function checkAccess()
    {
        $where[] = [
            ['status', '=', 1],
            ['name', '=', Request::routeInfo()['route']]
        ];
        //用户当前访问的路由
        $route = Db::table('auth_rule')->where($where)->find();
        //如果查出来是null说明规则表中没有这项规则，就认为这个路由不需
        //要鉴权，任何用户都可以访问
        if(is_null($route)) return true;
        $routeId = $route['id'];

        $uid = JWTAuth::getAccount()['id'];
        //用户所有的rules id
        $userRulesId = static::userAccessListId($uid);

        if(!in_array($routeId, $userRulesId))
            throw new AuthException(11003);

        return true;
    }

    /**
     * 生成树
     */
    public static function buildTree(array &$elements, $pid = 0)
    {
        if(empty($elements)) return $elements;

        $branch = [];

        //所有父节点的id，因为下面要用递归构造“一棵树”，如果一个节点
        //是叶子节点那么就没有必要再使用递归去查找它的子节点了,这样能减少
        //将近3/4的循环
        $parentsId = static::parentNodeIds();

        foreach ($elements as &$element) {
            if ($element['pid'] == $pid) {
                if(in_array($element['id'], $parentsId))
                    //去找孩子。递归一直找到底
                    $element['children'] = static::buildTree($elements, $element['id']);
                else  $element['children'] = [];
                $branch[] = $element;
                unset($element);
            }
        }
        return $branch;
    }

    protected static function parentNodeIds()
    {
        if( !empty(static::$parentIds)) return static::$parentIds;
        $where = [
            ['status', '=', 1],
            //['level', '<', config('auth.max_level')]
            ['is_menu', '=', 1]
        ];

        //返回父节点的id
         static::$parentIds = Db::name(static::rules())->where($where)->column('id');

         return static::$parentIds;
    }

    /**
     * user-group表
     */
    public static function userGroup()
    {
        return config('auth.auth_group_access');
    }

    /**
     * group表
     */
    public static function groupRules()
    {
        return config('auth.auth_group');
    }

    /**
     * 规则表
     */
    public static function rules()
    {
        return config('auth.auth_rule');
    }

    /**
     * 用户表
     */
    public static function userInfo()
    {
        return config('auth.auth_user');
    }
}