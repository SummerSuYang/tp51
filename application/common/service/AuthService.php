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
  UNIQUE KEY `uid_group_id` (`uid`,`group_id`),
  KEY `uid` (`uid`),
  KEY `group_id` (`group_id`)
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
  KEY `pid` (`pid`),
  KEY `weigh` (`weigh`),
  KEY `path` (`path`(191)),
  KEY `name` (`name`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 ROW_FORMAT=COMPACT COMMENT='节点表';
*/

namespace app\common\service;

use app\common\model\AuthAccessModel;
use app\common\model\AuthGroupModel;
use app\common\model\AuthRuleModel;
use app\lib\exception\AuthException;
use think\facade\Request;

class AuthService
{
    protected $parentIds;
    private static $instance = null;

    /**
     * @param string $userId
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * 返回一个用户所在的所有权限组
     */
    protected function getGroups($userId = '')
    {
        if(isPositiveInteger($userId)) $where['uid'] = $userId;

        $groupId = AuthAccessModel::where($where)->column('group_id');

        $where = [['status', '=', 1], ['id', 'in', $groupId]];

        return AuthGroupModel::where($where)->select()->toArray();
    }

    /**
     * @param $groups
     * @return array
     * 组装权限组的id
     */
    protected  function groupRulesId($groups)
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
     * @param $userId
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
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
     * @param $rulesId
     * @param bool $login
     * @param int $pid
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * 权限列表，树形结构
     */
    protected function treeRulesList($rulesId, $login = true, $pid = 0)
    {
        $rules = $this->rulesList($rulesId);

        if($login) return $this->loginTree($rules,$pid);
        else return $this->buildTree($rules, $pid);
    }

    /**
     * @param $rulesId
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * 权限列表，平行结构
     */
    protected function rulesList($rulesId)
    {
        if(empty($rulesId)) return [];

        return AuthRuleModel::where('id', 'in', $rulesId)->select()->toArray();
    }

    /**
     * @param $userId
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * 一个用户所有的权限
     */
    protected function userRulesList($userId)
    {
        $rulesId =$this->userRulesId($userId);
        return $this->treeRulesList($rulesId);
    }

    /**
     * @param array $elements
     * @param int $pid
     * @return array
     * 登录成功时的构造树方法
     */
    public  function loginTree(array &$elements, $pid = 0)
    {
        if(empty($elements)) return $elements;

        $tree = [];
        $parentsId =$this->parentNodeIds();

        foreach ($elements as $key => $element) {
            if ($element['pid'] == $pid) {
                if(in_array($element['id'], $parentsId)){
                    $children = $this->loginTree($elements, $element['id']);
                    //children是叶子节点
                    if(is_string($children))$tree[$element['action']][] = $children;
                    //如果没有子节点
                    //else if(empty($children)) $tree = $element['title'];
                    //children是个集合
                    else $tree[$element['action']] = $children;
                    unset($elements[$key]);
                }
                else $tree[] = $element['action'];
            }
        }

        return $tree;
    }

    /**
     * @param array $elements
     * @param int $pid
     * @return array
     */
    public function buildTree(array &$elements, $pid = 0)
    {
        if(empty($elements)) return $elements;

        $tree = [];

        //所有父节点的id，因为下面要用递归构造“一棵树”，如果一个节点
        //是叶子节点那么就没有必要再使用递归去查找它的子节点了
        $parentsId = $this->parentNodeIds();

        foreach ($elements as &$element) {
            if ($element['pid'] == $pid) {
                if(in_array($element['id'], $parentsId))
                    //去找孩子。递归一直找到底
                    $element['children'] = $this->buildTree($elements, $element['id']);
                else  $element['children'] = [];
                $tree[] = $element;
                unset($element);
            }
        }
        return $tree;
    }

    /**
     * @return array
     * 获取所有的非叶子节点的id
     */
    protected function parentNodeIds()
    {
        if( !empty($this->parentIds)) return $this->parentIds;

        //叶子结点的level
        $maxLevel = AuthRuleModel::max('level');

        $where = [
            ['status', '=', 1],
            ['level', '<', $maxLevel]
        ];

        $this->parentIds = AuthRuleModel::where($where)->column('id');

        return $this->parentIds;
    }

    /**
     * @return bool
     * @throws AuthException
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * 检查一个权限
     */
    public function checkAccess()
    {
        $where[] = [
            ['status', '=', 1],
            ['name', '=', Request::routeInfo()['route']]
        ];
        //用户当前访问的路由
        $route = AuthRuleModel::get($where);
        //如果查出来是null说明规则表中没有这项规则，就认为这个路由不需
        //要鉴权，任何用户都可以访问
        if(is_null($route)) return true;
        $routeId = $route->id;

        $uid = JWTAuth::getAccount()['id'];
        //用户所有的rules id
        $userRulesId = $this->userRulesId($uid);

        if(!in_array($routeId, $userRulesId))
            throw new AuthException(11003);

        return true;
    }

    /**
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
   protected function groupRules()
    {
        if(!empty(Request::param('group_id')))
            return $this->updateGroupRules();
        else
            return $this->createGroupRules();
    }

    /**
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
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

        $group = AuthGroupModel::get(['id' => $groupId, 'status' => 1]);

        $allRules = $this->rulesList($this->allRulesId());

        $groupRulesId = explode(',', $group->rules);

        $this->markRules($allRules, $groupRulesId);

        return $this->buildTree($allRules);
    }

    /**
     * @param $allRules
     * @param $groupRulesId
     */
    public function markRules(&$allRules, $groupRulesId)
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
        return AuthRuleModel::where('status',1)->column('id');
    }

    public static function __callStatic($name, $arguments)
    {
        if(is_null(self::$instance))
            self::$instance = new self();

        return call_user_func_array([self::$instance, $name], $arguments);
    }
}