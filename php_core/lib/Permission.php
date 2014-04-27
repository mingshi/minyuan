<?php
class Permission
{
    //当前用户的权限表
    protected $_permissions = array();

    //当前用户的角色
    protected $_roles = '';

    /**
     * 权限表定义
     */
    protected $_permissionTable = array(
        'roles' => array(
            /*
            self::ROLE_ADMIN => array(
                '__name' => '管理员',
                '__all',
            ),
            */
            // Example
            /*
            //self::SALE 是角色的定义常量值，一个用户可以是多个角色，故角色常量定义为1,2,4,8,16...
            //其中1默认为管理员，可做任何操作
            self::SALE => array(
                '__name' => '销售', //角色的显示名称

                //资源权限定义，默认是定义拥有的资源权限
                //资源组权限，资源组在下面定义
                '__group.read_common',

                //一个Controller下所有的资源
                'account',

                //一个Controller下的某几个Action
                'account.list',

                //排除的资源列表，优先级比拥有的资源高
                '__exclude' => array(
                    'account.delete',
                    'account.__post', //不允许提交post表单
                 ),
            ),
            */
        ),
        'public' => array(
            'index', 'login', 'logout'
        ),
        'groups' => array(
            //定义权限组: 'groupname' => array('resource', 'resource'),
            //在其它地方使用组: array('__group.groupname', 'resource')
            /*
            'read_common' => array(
                'slot.slist',
                'slot.grouplist',
                'schedule.index',
                'delivery.lists',
                'material.lists',
            )
            */
        ),
    );

    //所有角色的名称表
    protected $_roleMap = array();
    protected $_roleNames = array();

    public function __construct($permissionTable = NULL)
    {
        if ($permissionTable) {
            $this->setPermissionTable($permissionTable);
        }
    }

    public function setPermissionTable($permissionTable)
    {
        $this->_permissionTable = $permissionTable;

        foreach ($permissionTable['roles'] as $roleId => $roleConfig) {
            $this->_roleNames[$roleId] = $roleConfig['__name'];

            $parent = isset($roleConfig['__parent']) ? $roleConfig['__parent'] : 0;

            $this->_roleMap[$roleId] = array(
                'name' => $roleConfig['__name'],
                'desc' => isset($roleConfig['__desc']) ? $roleConfig['__desc'] : '',
                'parent' => $parent,
            );
        }

        # Build role tree
        foreach ($this->_roleMap as $roleId => &$role) {
            $parent = $role['parent'];
            if ($parent != 0) {
                $this->_roleMap[$parent]['children'][] = $roleId;
            }
        }
    }
    
    public static function checkRoles()
    {
        $args = func_get_args(); 

        $roles = explode(',', array_shift($args));
        
        foreach ($args as $role) {
            if (in_array($role, $roles)) {
                
                return TRUE;
            }
        }

        return FALSE;
    }

    public function isRole($role)
    {
        $roles = explode(',', $this->_roles);
        return in_array($role, $roles);
    }

    public function setRoles($roles)
    {
        $this->_roles = $roles;
        $this->_permissions = $this->_getPermissions($roles);
    }

    /**
     * 是否是一个（多个）有效的角色
     */
    public function isValidRoles($roles)
    {
        if ( ! is_array($roles)) {
            $roles = explode(',', $roles);
        }
        foreach ($roles as $role) {
            if ( ! isset($this->_permissionTable['roles'][$role])) {
                return FALSE;
            }
        }
        return TRUE;
    }

    public function getRoleMap()
    {
        return $this->_roleMap;
    }
    
    public function getChildRoles($roleIds = NULL)
    {
        if (is_null($roleIds)) {
            $roleIds = $this->_roles;
        }

        if (empty($roleIds)) {

            return array();
        }

        $children = array();

        $roleIds = explode(',', $roleIds);

        while ($roleId = array_shift($roleIds)) {
            $role = $this->_roleMap[$roleId];

            if (empty($role['children'])) {
                continue;
            }

            $children = array_merge($children, $role['children']);
            
            $roleIds = array_merge($roleIds, $role['children']);
        }

        return array_unique($children);
    }

    public function getRoleNames($roleId = NULL)
    {
        if ($roleId === NULL) {
            return $this->_roleNames;
        }
        $roleNames = array();
        foreach (explode(',', "$roleId") as $rid) {
            $name = isset($this->_roleNames[(int) $rid]) ? $this->_roleNames[(int) $rid] : '';
            if ($name) {
                $roleNames[] = $name;
            }
        }
        return implode(',', $roleNames);
    }

    /**
     * 检查资源权限
     * @param $reource 资源ID
     * @param $strict 严格模式，为TRUE表示确认拥有所有权限，为FALSE确认拥有相关权限（部分子权限）
     * @return bool
     */
    public function checkPermission($resource, $strict = TRUE)
    {
        $segments = explode('.', strtolower($resource));
        $n = count($segments);
        $permissionList = $this->_permissions;

        foreach ($permissionList as $permissions) {
            if ( ! empty($permissions['__exclude'])) {
                $exclude = $permissions['__exclude'];
                if ($this->_inPermissionMap($resource, $exclude)) {
                    continue;
                }
            }

            if ( ! empty($permissions['__all'])) {
                return TRUE;
            }

            if ($this->_inPermissionMap($resource, $permissions, $strict)) {
                return TRUE;
            }
        }

        return FALSE;
    }

    protected function _inPermissionMap($resource, $permMap, $strict = TRUE)
    {
        $isPost = ! empty($_POST);
        $segments = explode('.', strtolower($resource));
        $n = count($segments);
        
        if (preg_match('@__post$@', $resource)) {
            $isPost = TRUE;
        }

        if ($isPost && ! empty($permMap['__post'])) {
            return TRUE;
        }

        for ($i = 0; $i < $n; $i++) {
            $segment = $segments[$i];
            if (empty($permMap[$segment])) {
                return FALSE;
            }
            if ($isPost && ! empty($permMap[$segment]['__post'])) {
                return TRUE;
            }
            if ( ! empty($permMap[$segment]['__all'])) {
                return TRUE;
            }
            $permMap = $permMap[$segment];
        }

        return $strict ? FALSE : TRUE;
    }

    /**
     * 获取角色的权限列表
     */
    protected function _getPermissions($roles)
    {
        if ( ! $this->isValidRoles($roles, TRUE)) {
            return array();
        }

        $roles = explode(',', "$roles");
        $rolePermissions = array($this->_permissionTable['public']);
        foreach ($roles as $role) {
            $rolePermissions[] = $this->_permissionTable['roles'][$role];
        }

        $rolePermissionMapList = array();
        foreach ($rolePermissions as $rolePermission) {
            $exclude = array();
            if (isset($rolePermission['__exclude'])) {
                $exclude = $rolePermission['__exclude'];
                unset($rolePermission['__exclude']);
                $exclude = $this->_getPermissionMap($exclude);
            }
            unset($rolePermission['__name']);
            unset($rolePermission['__desc']);
            unset($rolePermission['__parent']);
            $rolePermissionMap = $this->_getPermissionMap($rolePermission);
            $rolePermissionMap['__exclude'] = $exclude;
            $rolePermissionMapList[] = $rolePermissionMap;
        }

        return $rolePermissionMapList;
    }

    private function _getPermissionMap($perms)
    {
        $permMap = array();
        $expandPerms = array();
        while ($perms) {
            foreach ($perms as $perm) {
                $perm = strtolower($perm);
                $segments = explode('.', $perm);
                if ($segments[0] == '__group') {
                    $group = $segments[1];
                    $groupPerms = $this->_permissionTable['groups'][$group];
                    $expandPerms = array_merge($expandPerms, $groupPerms);
                    continue;
                }

                $n = count($segments);
                $map = &$permMap;
                for ($i = 0; $i < $n; $i++) {
                    $segment = $segments[$i];
                    if ( ! empty($map['__all'])) {
                        break;
                    }
                    if ( ! isset($map[$segment])) {
                        $map[$segment] = array();
                    }
                    if ($i == $n - 1) {
                        $map[$segment]['__all'] = TRUE;
                    }
                    $map = &$map[$segment];
                }
                unset($map);
            }
            $perms = $expandPerms;
            $expandPerms = array();
        }
        return $permMap;
    }
}
