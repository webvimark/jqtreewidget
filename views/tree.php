<?php
/**
 * @var $this yii\web\View
 * @var $jsonTree array
 * @var $withChildren boolean
 * @var $showExpandAndCollapse boolean
 * @var $treeId string
 */

use yii\helpers\Html;
?>

<?php if ( $withChildren AND $showExpandAndCollapse ): ?>
	<span class='btn btn-sm btn-default expand-all'>
		<i class="fa fa-plus"></i>
			<?= 'Expand' ?>
	</span>
		<span class='btn btn-sm btn-default collapse-all'>
		<i class="fa fa-minus"></i>
			<?= 'Collapse' ?>
	</span>
<?php endif; ?>


<span class='label label-default mall'>
        <?php echo "You can drag & drop elements."; ?>
</span>
<hr>

<?php if(Yii::$app->session->hasFlash('leafDeleted')): ?>
        <h4 class='alert alert-success text-center'>
                <?php echo "Elements have been deleted"; ?>
        </h4>
<?php endif ?>

<?php echo Html::beginForm(); ?>

<?php echo Html::hiddenInput('deleteLeafs', '1'); ?>

<div id='<?= $treeId ?>'>
</div>

<hr>

<?php echo Html::submitButton(
        "Delete selected",
        array(
                'class'   => 'btn btn-sm btn-danger',
                'data-confirm' => "Are you sure ? \nAll child elements will be deleted as well."
        )
); ?>

<?php echo Html::endForm(); ?>

<?php
// Since $withChildren = false make js crash - use replacement
$withChildren = $withChildren ? 1 : 0;
$js = <<<JS

var treeSelector = $('#$treeId');

treeSelector.tree({
	data: $jsonTree,
	autoEscape: false,
	dragAndDrop: true,
	autoOpen: true,
	onCanMoveTo: function(moved_node, target_node, position) {
		return $withChildren == 1 || position!='inside';
	},
	useContextMenu: false
});

treeSelector.bind(
	'tree.move',
	function(event) {
		var oldPositions = [];

		$('input[type="checkbox"]').each(function(){
			oldPositions.push($(this).val());
		});

		$.get('', {
			jqtree_is_moved : 'yes it is',
			moved_node_id : event.move_info.moved_node.id,

			target_node_id : event.move_info.target_node.id,
			target_node_parent_id : event.move_info.target_node.parent_id,

			position : event.move_info.position,

			oldPositions : oldPositions
		});
	}
);

$('.expand-all').on('click', function(){
	$('.jqtree-closed').click();
});

$('.collapse-all').on('click', function(){
	$('.jqtree-toggler').each(function(){
		if (! $(this).hasClass('jqtree-closed'))
		{
			$(this).click();
		}
	});
});
JS;

$this->registerJs($js);
?>


