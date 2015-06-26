<?php

namespace Croogo\Menus\Event;

use Cake\Cache\Cache;
use Cake\Event\Event;
use Cake\Event\EventListenerInterface;

/**
 * MenusEventHandler
 *
 * @package  Croogo.Menus.Event
 * @license  http://www.opensource.org/licenses/mit-license.php The MIT License
 * @link     http://www.croogo.org
 */
class MenusEventHandler implements EventListenerInterface {

/**
 * implementedEvents
 */
	public function implementedEvents() {
		return array(
			'Controller.Links.afterPublish' => array(
				'callable' => 'onAfterBulkProcess',
			),
			'Controller.Links.afterUnpublish' => array(
				'callable' => 'onAfterBulkProcess',
			),
			'Controller.Links.afterDelete' => array(
				'callable' => 'onAfterBulkProcess',
			),
		);
	}

/**
 * Clear Links related cache after bulk operation
 *
 * @param Event $event
 * @return void
 */
	public function onAfterBulkProcess(Event $event) {
		Cache::clearGroup('menus', 'croogo_menus');
	}

}
