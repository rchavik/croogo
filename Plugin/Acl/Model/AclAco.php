<?php

App::uses('AclNode', 'Model');

/**
 * AclAco Model
 *
 * PHP version 5
 *
 * @category Model
 * @package  Croogo
 * @version  1.0
 * @author   Fahad Ibnay Heylaal <contact@fahad19.com>
 * @license  http://www.opensource.org/licenses/mit-license.php The MIT License
 * @link     http://www.croogo.org
 */
class AclAco extends AclNode {

/**
 * name
 *
 * @var string
 */
	public $name = 'AclAco';

/**
 * useTable
 *
 * @var string
 */
	public $useTable = 'acos';

/**
 * actsAs
 *
 * @var array
 */
	public $actsAs = array('Tree');

	public $alias = 'Aco';

	public function getChildren($acoId) {
		$fields = array('id', 'parent_id', 'alias');
		$acos = $this->children($acoId, true, $fields);
		foreach ($acos as &$aco) {
			$aco[$this->alias]['children'] = $this->childCount($aco[$this->alias]['id'], true);
		}
		return $acos;
	}
}
