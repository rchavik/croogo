<?php

Croogo::hookBehavior('User', 'Acl.UserAco');
Croogo::hookBehavior('Role', 'Acl.RoleAco');

if (Configure::read('Site.acl_plugin' == 'Acl')) {

	CroogoNav::add('users.children.permissions', array(
		'title' => __('Permissions'),
		'url' => array(
			'admin' => true,
			'plugin' => 'acl',
			'controller' => 'acl_permissions',
			'action' => 'index',
			),
		'access' => array('admin'),
		'weight' => 30,
		));

}
