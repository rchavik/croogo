<?php
/**
 * Roles Controller
 *
 * PHP version 5
 *
 * @category Controller
 * @package  Croogo
 * @version  1.0
 * @author   Fahad Ibnay Heylaal <contact@fahad19.com>
 * @license  http://www.opensource.org/licenses/mit-license.php The MIT License
 * @link     http://www.croogo.org
 */
class RolesController extends AppController {
/**
 * Controller name
 *
 * @var string
 * @access public
 */
    public $name = 'Roles';
/**
 * Models used by the Controller
 *
 * @var array
 * @access public
 */
    public $uses = array('Role');

    public function admin_index() {
        $this->set('title_for_layout', __('Roles', true));

        $this->Role->recursive = 0;
        $this->paginate['Role']['order'] = "Aro.lft ASC";

        $this->Role->bindModel(array(
            'hasOne' => array(
                'Aro' => array(
                    'className' => 'Aro',
                    'foreignKey' => false,
                    'conditions' => array(
                        "model = 'Role'",
                        'foreign_key = Role.id',
                    ),
                ),
            )), false);

        $roles = $this->paginate();
        foreach ($roles as &$role) {
            $role['Role']['level'] = count($this->Role->Aro->getpath($role['Aro']['id']));
        }
        $this->set(compact('roles'));
    }

    public function admin_add() {
        $this->set('title_for_layout', __('Add Role', true));

        if (!empty($this->data)) {
            $this->Role->create();
            if ($this->Role->save($this->data)) {
                $this->Session->setFlash(__('The Role has been saved', true), 'default', array('class' => 'success'));
                $this->redirect(array('action'=>'index'));
            } else {
                $this->Session->setFlash(__('The Role could not be saved. Please, try again.', true), 'default', array('class' => 'error'));
            }
        }
    }

    public function admin_edit($id = null) {
        $this->set('title_for_layout', __('Edit Role', true));

        if (!$id && empty($this->data)) {
            $this->Session->setFlash(__('Invalid Role', true), 'default', array('class' => 'error'));
            $this->redirect(array('action'=>'index'));
        }

        $this->Role->bindModel(array(
            'hasOne' => array(
                'Aro' => array(
                    'foreignKey' => false,
                    'conditions' => array(
                        "model = 'Role'",
                        'foreign_key = Role.id',
                    ),
                ),
            )), false);
        if (!empty($this->data)) {
            if ($this->Role->save($this->data)) {
                if (isset($this->data['Role']['parent_id'])) {
                    $aro = $this->Acl->Aro->node(array(
                        'model' => 'Role', 'foreign_key' => $this->Role->id
                        ));
                    if ($aro) {
                        $aro = $aro[0];
                        $aro['Aro']['parent_id'] = $this->data['Role']['parent_id'];
                        $this->Acl->Aro->save($aro);
                    }
                }
                $this->Session->setFlash(__('The Role has been saved', true), 'default', array('class' => 'success'));
                $this->redirect(array('action'=>'index'));
            } else {
                $this->Session->setFlash(__('The Role could not be saved. Please, try again.', true), 'default', array('class' => 'error'));
            }
        }
        if (empty($this->data)) {
            $this->data = $this->Role->read(null, $id);
            if (isset($this->data['Aro'])) {
                $parent = $this->Acl->Aro->getparentnode($this->data['Aro']);
                $this->data['Role']['parent_id'] = $parent['Aro']['id'];
            }
            $roles = $this->Role->find('all');
            $parents = array();
            foreach ($roles as &$role) {
                $parents[$role['Aro']['id']] = $role['Role']['title'];
            }
            $this->set(compact('parents'));
        }
    }

    public function admin_delete($id = null) {
        if (!$id) {
            $this->Session->setFlash(__('Invalid id for Role', true), 'default', array('class' => 'error'));
            $this->redirect(array('action'=>'index'));
        }
        if (!isset($this->params['named']['token']) || ($this->params['named']['token'] != $this->params['_Token']['key'])) {
            $blackHoleCallback = $this->Security->blackHoleCallback;
            $this->$blackHoleCallback();
        }
        if ($this->Role->delete($id)) {
            $this->Session->setFlash(__('Role deleted', true), 'default', array('class' => 'success'));
            $this->redirect(array('action'=>'index'));
        }
    }

}
?>