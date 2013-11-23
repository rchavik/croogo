
<table class="table table-striped">
<?php
	$tableHeaders = $this->Html->tableHeaders(array(
		'',
		__d('croogo', 'Id'),
		__d('croogo', 'Title'),
		__d('croogo', 'Slug'),
	));
?>
<thead>
	<?php echo $tableHeaders; ?>
</thead>
<?php
	$rows = array();

	foreach ($termsTree as $id => $title):

		// Title Column
		$titleCol = $title;
		if(isset($this->params->query['type'])){
			$titleCol = $this->Html->link($title,array(
			'plugin'=>'nodes',
			'controller'=>'nodes',
			'action'=>'term',
			'type'=>$this->params->query['type'],
			'slug'=>$terms[$id]['slug'],
			'admin'=>0
			),array(
				'class' => 'item-choose',
				'data-chooser_type' => 'Node',
				'data-chooser_id' => $id,
				'rel' => sprintf(
					'plugin:%s/controller:%s/action:%s/type:%s/slug:%s',
					'nodes',
					'nodes',
					'term',
					$this->params->query['type'],
					$terms[$id]['slug']
					),
			));
		}

		$rows[] = array(
			'',
			$id,
			$titleCol,
			$terms[$id]['slug'],
		);

	endforeach;

	echo $this->Html->tableCells($rows);

?>
</table>
<?php

$script =<<<EOF
$('.popovers').popover().on('click', function() { return false; });;
EOF;
$this->Js->buffer($script);
