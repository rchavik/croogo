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

/**
 * _controller
 *
 * @var Controller
 */
	protected $_controller = null;

	protected $_defaultSettings = array(
		'Auth' => array(
			'loginRedirect' => array(
				'plugin' => 'settings',
				'controller' => 'settings',
				'action' => 'dashboard',
			),
			'loginAction' => array(
				'plugin' => 'users',
				'controller' => 'users',
				'action' => 'login',
			),
			'logoutRedirect' => array(
				'plugin' => 'users',
				'controller' => 'users',
				'action' => 'login',
			),
		),
		'Acl' => array(
			'authenticate' => array(
				AuthComponent::ALL => array(
					'userModel' => 'Users.User',
					'fields' => array(
						'username' => 'username',
						'password' => 'password',
						),
					'scope' => array(
						'User.status' => 1,
						),
					),
				'Form',
			),
			'authorize' => array(
				AuthComponent::ALL => array(
					'actionPath' => 'controllers',
					'userModel' =>  'Users.User',
				),
				'Acl.AclCached',
			),
		)
	);

/**
 * @param object $controller controller
 * @param array  $settings   settings
 */
	public function initialize(Controller $controller) {
		$this->_controller =& $controller;
	}

/**
 * configure component settings
 *
 * @return void
 */
	protected function _configure() {
		$config = Configure::read('Acl');
		if (empty($config['authenticate'])) {
			$authenticate = $this->_defaultSettings['Acl']['authenticate'];
		} else {
			$authenticate = $config['authenticate'];
		}
		$this->_controller->Auth->authenticate = $authenticate;

		if (empty($config['authorize'])) {
			$authorize = $this->_defaultSettings['Acl']['authorize'];
		} else {
			$authorize = $config['authorize'];
		}
		$this->_controller->Auth->authorize = $authorize;

		if (empty($config['loginAction'])) {
			$loginAction = $this->_defaultSettings['Auth']['loginAction'];
		} else {
			$loginAction = $config['loginAction'];
		}
		$this->_controller->Auth->loginAction = $loginAction;

		if (empty($config['logoutRedirect'])) {
			$logoutRedirect = $this->_defaultSettings['Auth']['logoutRedirect'];
		} else {
			$logoutRedirect = $config['logoutRedirect'];
		}
		$this->_controller->Auth->logoutRedirect = $logoutRedirect;

		if (empty($config['loginRedirect'])) {
			$loginRedirect = $this->_defaultSettings['Auth']['loginRedirect'];
		} else {
			$loginRedirect = $config['loginRedirect'];
		}
		$this->_controller->Auth->loginRedirect = $loginRedirect;
	}

/**
 * acl and auth
 *
 * @return void
 */
	public function auth() {
		$this->_configure();
		$user = $this->_controller->Auth->user();
		if (!empty($this->_controller->Node->User->Role)) {
			$Role = $this->_controller->Node->User->Role;
		} else {
			$Role = ClassRegistry::init('Users.Role');
		}
		$Role->Behaviors->load('Aliasable');
		// Admin role is allowed to perform all actions, bypassing ACL
		if (!empty($user['role_id']) && $user['role_id'] == $Role->byAlias('admin')) {
			$this->_controller->Auth->allowedActions = array('*');
			return;
		}

		// authorization for authenticated user is handled by authorize object
		if ($user) {
			return;
		}

		// public access authorization
		// FIXME, do we still need this?
		$cacheName = 'permissions_public';
		if (($perms = Cache::read($cacheName, 'permissions')) === false) {
			$perms = $this->getPermissions('Role', $Role->byAlias('public'));
			Cache::write($cacheName, $perms, 'permissions');
		}
		if (!empty($perms['allowed'][$this->_controller->name])) {
			$this->_controller->Auth->allow(
				$perms['allowed'][$this->_controller->name]
			);
		}
	}

/**
 * getPermissions
 * retrieve list of permissions from database
 * @param string $model model name
 * @param string $id model id
 * @return array list of authorized and allowed actions
 */
	public function getPermissions($model, $id) {
		$Acl =& $this->_controller->Acl;
		$aro = array('model' => $model, 'foreign_key' => $id);
		$node = $Acl->Aro->node($aro);
		$nodes = $Acl->Aro->getPath($node[0]['Aro']['id']);

		$aros = Set::extract('/Aro/id', $node);
		if (!empty($nodes)) {
			$aros = Set::merge($aros, Set::extract('/Aro/id', $nodes));
		}

		$permissions = $Acl->Aro->Permission->find('all', array(
			'conditions' => array(
				'Permission.aro_id' => $aros,
				'Permission._create' => 1,
				'Permission._read' => 1,
				'Permission._update' => 1,
				'Permission._delete' => 1,
				)
			));

		$authorized = $allowedActions = array();
		foreach ($permissions as $permission) {
			$path = $Acl->Aco->getPath($permission['Permission']['aco_id']);
			if (count($path) == 4) {
				// plugin controller/action
				$controller = $path[2]['Aco']['alias'];
				$action = $path[3]['Aco']['alias'];
			} else {
				// app controller/action
				$controller = $path[1]['Aco']['alias'];
				$action = $path[2]['Aco']['alias'];
			}
			$allowedActions[$controller][] = $action;
			$authorized[] = implode('/', Set::extract('/Aco/alias', $path));
		}
		return array('authorized' => $authorized, 'allowed' => $allowedActions);
	}

}
