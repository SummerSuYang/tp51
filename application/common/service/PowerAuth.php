<?php
/*
|--------------------------------------------------------------------------
| Creator: SuYang
| Date: 2018/4/12 0012 17:41
|--------------------------------------------------------------------------
|                                                 权限管理类
|--------------------------------------------------------------------------
auth_group表
CREATE TABLE `auth_group` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `pid` bigint(20) unsigned NOT NULL DEFAULT '0' COMMENT '父组别',
  `name` varchar(100) NOT NULL DEFAULT '' COMMENT '组名',
  `accounts` smallint(255) unsigned NOT NULL DEFAULT '0' COMMENT '账号数量',
  `rules` text COMMENT '规则ID',
  `status` tinyint(3) unsigned NOT NULL DEFAULT '1' COMMENT '状态;',
  `create_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `update_time` int(10) unsigned DEFAULT '0' COMMENT '更新时间',
  `delete_time` int(10) unsigned DEFAULT NULL COMMENT '删除时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='分组表';

auth_group_access表
CREATE TABLE `auth_group_access` (
  `uid` bigint(20) unsigned NOT NULL COMMENT '会员ID',
  `group_id` bigint(20) unsigned NOT NULL COMMENT '级别ID',
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='权限分组表';

auth_rule表
CREATE TABLE `auth_rule` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `pid` bigint(20) unsigned NOT NULL DEFAULT '0' COMMENT '父ID',
  `path` varchar(200) NOT NULL DEFAULT '' COMMENT '路径:0-1-N',
  `level` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '层级',
  `name` varchar(100) NOT NULL DEFAULT '' COMMENT '规则名称',
  `title` varchar(50) NOT NULL DEFAULT '' COMMENT '规则名称',
  `action` varchar(50) NOT NULL DEFAULT '' COMMENT '方法名',
  `icon` varchar(50) NOT NULL DEFAULT '' COMMENT '图标',
  `condition` varchar(255) NOT NULL DEFAULT '' COMMENT '条件',
  `remark` varchar(255) NOT NULL DEFAULT '' COMMENT '备注',
  `is_menu` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '是否为菜单',
  `weigh` int(11) NOT NULL DEFAULT '0' COMMENT '权重',
  `status` tinyint(3) unsigned NOT NULL DEFAULT '1' COMMENT '状态;',
  `create_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `update_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
  `delete_time` int(10) unsigned DEFAULT NULL COMMENT '删除时间',
  PRIMARY KEY (`id`),
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 ROW_FORMAT=COMPACT COMMENT='节点表';
|--------------------------------------------------------------------------
|userRulesId 获取用户的权限树，无论是登陆成功后还是刷新都是一样的
|checkAccess 判断用户有没有当前接口的访问权限
|groupRules 新建或更新权限组时使用的方法
|--------------------------------------------------------------------------
*/

namespace app\common\service;

use app\common\model\AuthAccessModel;
use app\common\model\AuthGroupModel;
use app\common\model\AuthRuleModel;
use app\lib\exception\AuthException;
use think\facade\Request;
use think\Exception;
use app\lib\enum\Status;

class PowerAuth
{
    protected $parentIds;
    private static $instance = null;

    /**
     * 返回一个用户所在的所有权限组
     */
    protected function getGroups($userId = '')
    {
        $where = [];
        if(isPositiveInteger($userId)) {
            $where['uid'] = $userId;
        }

        $groupId = AuthAccessModel::where($where)->column('group_id');

        $where = [['status', '=', Status::NORMAL], ['id', 'in', $groupId]];

        return AuthGroupModel::where($where)->select()->toArray();
    }

    /**
     * 组装权限组的id
     */
    protected  function groupRulesId($groups)
    {
        if(empty($groups)) {
            return [];
        }
        $rulesId = [];
        //一个用户可能在多个组里面
        foreach ($groups as $item){
            $rulesId = array_merge($rulesId, explode(',',$item['rules']));
        }
        if(empty($rulesId)) {
            return [];
        }

       return array_unique($rulesId);
    }

    /**
     * 一个用户所有的权限id
     */
    protected function userRulesId($userId)
    {
        //用户所在的权限组，可以是多个
        $groups =$this->getGroups($userId);
        // 用户所在的权限组所拥有的的id集合
        return $this->groupRulesId($groups);
    }

    /**
     * 一个用户所有的权限
     * 用户登录成功后以及刷新页面时调用此接口
     */
    public function userRulesList($userId)
    {
        $rulesId =$this->userRulesId($userId);
        return $this->treeRulesList($rulesId);
    }

    /**
     * 权限列表，树形结构(树形结构分为两种)
     */
    protected function treeRulesList($rulesId, $login = true, $pid = 0)
    {
        $rules = $this->rulesList($rulesId);

        if($login) {
            return $this->loginTree($rules,$pid);
        }

        return $this->buildTree($rules, $pid);
    }

    /**
     * 权限列表，平行结构
     */
    protected function rulesList($rulesId)
    {
        if(empty($rulesId)) {
            return [];
        }

        return AuthRuleModel::where('id', 'in', $rulesId)->select()->toArray();
    }

    /**
     * 登录成功时返回给前端的权限列表,适合判断
     */
    protected  function loginTree(array &$elements, $pid = 0)
    {
        if(empty($elements)) {
            return $elements;
        }

        $branch = [];

        $parentsId =$this->parentNodeIds();

        foreach ($elements as $key => $element) {
            if ($element['pid'] == $pid) {
                //如果是非叶子节点就继续找其子节点
                if(in_array($element['id'], $parentsId)){
                    $children = $this->loginTree($elements, $element['id']);
                    $branch[$element['action']] = $children;
                }
                //如果是叶子节点
                else {
                    $branch[] = $element['action'];
                }

                unset($elements[$key]);
            }
        }

        return $branch;
    }

    /**
     * 新建或者更新权限组的时候返回的权限列表，适合循环
     */
    protected function buildTree(array &$elements, $pid = 0)
    {
        if(empty($elements)) return $elements;

        $branch = [];

        $parentsId = $this->parentNodeIds();

        foreach ($elements as &$element) {
            if ($element['pid'] == $pid) {
                if(in_array($element['id'], $parentsId)){
                    $element['children'] = $this->buildTree($elements, $element['id']);
                }
                else{
                    $element['children'] = [];
                }
                $branch[] = $element;
                unset($element);
            }
        }
        return $branch;
    }

    /**
     * 获取所有的非叶子节点的id
     */
    protected function parentNodeIds()
    {
        if( !empty($this->parentIds)) {
            return $this->parentIds;
        }

        //叶子结点的level
        $maxLevel = AuthRuleModel::max('level');

        $where = [
            ['status', '=', Status::NORMAL],
            ['level', '<', $maxLevel]
        ];

        $this->parentIds = AuthRuleModel::where($where)->column('id');

        return $this->parentIds;
    }

    /**
     * 检查一个权限
     */
    public function checkAccess()
    {
        $where[] = [
            ['name', '=', strtolower(Request::routeInfo()['route'])],
            ['status', '=', Status::NORMAL],
        ];
        //用户当前访问的路由
        $route = AuthRuleModel::get($where);
        //如果查出来是null说明规则表中没有这项规则，就认为这个路由不需
        //要鉴权，任何用户都可以访问
        if(is_null($route)) {
            return true;
        }

        $routeId = $route->id;

        $uid = CurrentUser::getAccountAttribute('id');
        //用户所有的rules id
        $userRulesId = $this->userRulesId($uid);

        if(!in_array($routeId, $userRulesId)){
            throw new AuthException(11003);
        }

        return true;
    }

    /**
     * 返回一个权限组的所有权限的列表
     * 新建或更新权限组权限的时候调用此接口
     */
    public function groupRules()
    {
        if(Request::has('group_id')) {
            return $this->updateGroupRules();
        }
        else {
            return $this->createGroupRules();
        }
    }

    /**
     * 新建权限组时的权限列表
     */
    protected function createGroupRules()
    {
        $allRulesId = $this->allRulesId();

        return $this->treeRulesList($allRulesId, false);
    }

    /**
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * 更新权限组时的权限列表
     */
    protected function updateGroupRules()
    {
        $groupId = Request::param('group_id');

        $group = AuthGroupModel::get(['id' => $groupId, 'status' => Status::NORMAL]);

        $groupRulesId = explode(',', $group->rules);

        $allRules = $this->rulesList($this->allRulesId());

        $this->markRules($allRules, $groupRulesId);

        return $this->buildTree($allRules);
    }

    /**
     * @param $allRules
     * @param $groupRulesId
     */
   protected function markRules(&$allRules, $groupRulesId)
    {
        array_walk($allRules, function(&$v, $k) use ($groupRulesId){
            if(in_array($v['id'], $groupRulesId))
                $v['have'] = 1;
            else $v['have'] = 0;
        });
    }

    /**
     * @return array
     * 所有权限的id
     */
    protected function allRulesId()
    {
        return AuthRuleModel::where('status',Status::NORMAL)->column('id');
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