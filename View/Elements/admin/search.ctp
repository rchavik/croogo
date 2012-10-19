<?php
if (empty($modelClass)) {
	$modelClass = Inflector::singularize($this->name);
}
if (!isset($className)) {
	$className = strtolower($this->name);
}
if (isset($searchFields) && $searchFields != array()):
?>

<div class="<?php echo $className; ?> filter form">
<?php
	echo $this->Form->create($modelClass, array(
		'url' => array_merge(array('action' => 'index'), $this->params['pass'])
		));
	$options = array('empty' => '');
	foreach ($searchFields as $field) {
		switch ($field) {
			case 'region_id':
				$options = Set::merge($options, array(
					'options' => $regions
					));
				$this->Form->unlockField($field);
				$out = $this->Form->input($field, $options);
				break;
			case 'role_id':
				$this->Form->unlockField($field);
				$options = Set::merge($options, array(
					'options' => $roles
					));
				$out = $this->Form->input($field, $options);
				break;
			default:
				$out = $this->Form->input($field);
				break;
		}
		echo $out;
	}

	//echo $this->Html->link('Clear', array('action' => 'index'), array('class' => 'button'));
	echo $this->Form->end(__('Filter'));
?>
<div class="clear">&nbsp;</div>
</div>
<?php endif; ?>
