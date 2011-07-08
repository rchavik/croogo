<?php
/**
 * AclFilter Component
 *
 * PHP version 5
 *
 * @category Component
 * @package  Croogo
 * @version  1.0
 * @author   Fahad Ibnay Heylaal <contact@fahad19.com>
 * @license  http://www.opensource.org/licenses/mit-license.php The MIT License
 * @link     http://www.croogo.org
 */
class AclFilterComponent extends Component {

    protected $controller = null;

/**
 * @param object $controller controller
 * @param array  $settings   settings
 */
    public function initialize(&$controller) {
        $this->controller =& $controller;
    }

/**
 * Configure Auth component
 * Auth settings can be configured using Acl.Auth keys.
 * Currently, the following settings are applicable:
 *   - loginAction
 *   - loginRedirect
 *   - scope
 *   - authError
 *   - fields
 */
    protected function _setupAuth() {
        //Configure AuthComponent
        $userScope = array('User.status' => 1);
        $userScope = Set::merge($userScope, Configure::read('Acl.Auth.userScope'));

        $userModel = Configure::read('Acl.Auth.userModel');
        if (empty($userModel)) {
            $userModel = 'User';
            Configure::write('Acl.Auth.userModel', $userModel);
        }

        $fields = Configure::read('Acl.Auth.fields');
        if (empty($fields)) {
            $fields = array('username' => 'username', 'password' => 'password');
            Configure::write('Acl.Auth.fields', $fields);
        }
        $this->controller->Auth->fields = $fields;

        $this->controller->Auth->userModel = $userModel;
        $this->controller->Auth->authenticate = array(
            AuthComponent::ALL => array(
                'userModel' => $userModel,
                'fields' => $fields,
                'scope' => $userScope,
                ),
            'Form',
            );
        $actionPath = 'controllers';
        $this->controller->Auth->authorize = array(
            AuthComponent::ALL => array('actionPath' => $actionPath),
            'Actions',
            );

        $loginAction = array(
            'plugin' => null,
            'controller' => 'users',
            'action' => 'login',
        );
        if (!isset($this->controller->params['admin'])) {
            $loginAction = Set::merge($loginAction, Configure::read('Acl.Auth.loginAction'));
        }
        $this->controller->Auth->loginAction = $loginAction;

        $loginRedirect = Configure::read('Acl.Auth.loginRedirect');
        if (empty($loginRedirect)) {
            $loginRedirect = array(
                'plugin' => null,
                'controller' => 'users',
                'action' => 'index',
                );
        }
        $this->controller->Auth->loginRedirect = $loginRedirect;

        if ($authError = Configure::read('Acl.Auth.authError')) {
            $this->controller->authError = $authError;
        }

    }

/**
 * acl and auth
 *
 * @return void
 */
    public function auth() {
        $this->_setupAuth();

        if ($this->controller->Auth->user() && $this->controller->Auth->user('role_id') == 1) {
            // Role: Admin
            $this->controller->Auth->allowedActions = array('*');
        } else {
            if ($this->controller->Auth->user()) {
                $roleId = $this->controller->Auth->user('role_id');
            } else {
                $roleId = 3; // Role: Public
            }

            $aro = $this->controller->Acl->Aro->find('first', array(
                'conditions' => array(
                    'Aro.model' => 'Role',
                    'Aro.foreign_key' => $roleId,
                ),
                'recursive' => -1,
            ));
            $aroId = $aro['Aro']['id'];
            $thisControllerNode = $this->controller->Acl->Aco->node($actionPath .'/'.  $this->controller->name);
            if ($thisControllerNode) {
                $thisControllerNode = $thisControllerNode['0'];
                $thisControllerActions = $this->controller->Acl->Aco->find('list', array(
                    'conditions' => array(
                        'Aco.parent_id' => $thisControllerNode['Aco']['id'],
                    ),
                    'fields' => array(
                        'Aco.id',
                        'Aco.alias',
                    ),
                    'recursive' => '-1',
                ));
                $thisControllerActionsIds = array_keys($thisControllerActions);
                $allowedActions = $this->controller->Acl->Aco->Permission->find('list', array(
                    'conditions' => array(
                        'Permission.aro_id' => $aroId,
                        'Permission.aco_id' => $thisControllerActionsIds,
                        'Permission._create' => 1,
                        'Permission._read' => 1,
                        'Permission._update' => 1,
                        'Permission._delete' => 1,
                    ),
                    'fields' => array(
                        'id',
                        'aco_id',
                    ),
                    'recursive' => '-1',
                ));
                $allowedActionsIds = array_values($allowedActions);
            }

            $allow = array();
            if (isset($allowedActionsIds) &&
                is_array($allowedActionsIds) &&
                count($allowedActionsIds) > 0) {
                foreach ($allowedActionsIds AS $i => $aId) {
                    $allow[] = $thisControllerActions[$aId];
                }
            }
            $this->controller->Auth->allowedActions = $allow;
        }
    }

}
?>