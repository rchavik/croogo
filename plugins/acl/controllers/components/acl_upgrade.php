<?php

class AclUpgradeComponent extends Object {

	var $__acosToMove = array(
		'Acl' => array('AclActions', 'AclAros', 'AclPermissions'),
		'Extensions' => array('ExtensionsLocales', 'ExtensionsPlugins', 'ExtensionsThemes'),
		);

	var $controller = false;

	function initialize(&$controller) {
		$this->controller =& $controller;
	}

	function upgrade() {
		$controller =& $this->controller;
		$Auth =& $controller->Auth;
		$Aco =& $controller->Acl->Aco;
		$actionPath = $Auth->actionPath;

		$root = $Aco->node(str_replace('/', '', $actionPath));
		if (empty($root)) {
			return __('No root node found', true);
		} else {
			$root = $root[0];
		}

		$errors = array();
		$Aco->begin();
		foreach ($this->__acosToMove as $plugin => $controllers) {
			$pluginPath = $actionPath . $plugin;
			$pluginNode = $Aco->node($pluginPath);
			if (empty($pluginNode)) {
				$Aco->create(array(
					'parent_id' => $root['Aco']['id'],
					'model' => null,
					'alias' => $plugin,
					));
				$pluginNode = $Aco->save();
			} else {
				$pluginNode = $pluginNode[0];
			}
			foreach ($controllers as $controllerName) {
				$controllerPath = $actionPath . $controllerName;
				$controllerNode = $Aco->node($controllerPath);
				if ($controllerNode) {
					$controllerNode = $controllerNode[0];
					$controllerNode['Aco']['parent_id'] = $pluginNode['Aco']['id'];
					$Aco->save($controllerNode);
				} else {
					$correctControllerPath = $actionPath . $plugin . '/' . $controllerName;
					$correctControllerNode = $Aco->node($correctControllerPath);
					if (empty($correctControllerNode)) {
						$errors[] = $controllerPath . __(' not found', true);
					}
				}
			}
		}
		if (!empty($errors)) {
			$Aco->rollback();
			return $errors;
		}
		$Aco->commit();
		return true;
	}

}

?>
