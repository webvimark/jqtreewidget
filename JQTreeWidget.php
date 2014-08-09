<?php
namespace webvimark\extensions\jqtreewidget;

use yii\base\Widget;
use yii\helpers\Html;
use yii\web\ForbiddenHttpException;
use yii\web\NotFoundHttpException;
use Yii;

/**
 * JQTreeWidget 
 * 
 * @version 1.0
 * @author vi mark <webvimark@gmail.com> 
 * @license MIT
 */
class JQTreeWidget extends Widget
{
	/**
	 * Callback function. Accept $model as argument
	 *
	 * <example>
	 * 	'leafName'=>function($model){
	 *		return Html::a($model->name, ['/content/page/update', 'id'=>$model->id]);
	 *	},
	 * </example>
	 *
	 * @var string
	 */
	public $leafName;

        /**
         * models 
         *
         * For example: Category::model()->findAll();
         * 
         * @var array
         */
        public $models = array();

        /**
         * modelName 
         *
         * For example: 'Category'
         * 
         * @var string
         */
        public $modelName = '';

        /**
         * parentIdField 
         * 
         * @var string
         */
        public $parentIdField = 'parent_id';

        /**
         * statusField 
         * 
         * @var string OR "false"
         */
        public $statusField = false;

        /**
         * orderField 
         * 
         * @var string
         */
        public $orderField = 'sorter';

        /**
         * rollingThunder 
         *
         * @return string
         */
        public function run()
        {
		JQTreeWidgetAsset::register($this->view);

                $this->_handle_leafs_delete();
                $this->_handle_leafs_move();

                $jsonTree = $this->_getJsonTree();

                return $this->render('tree', compact('jsonTree'));
        }

        /**
         * handle_leafs_delete 
         */
        private function _handle_leafs_delete()
        {
                $modelName = $this->modelName;

                // Deleting elements
                if (isset($_POST['deleteLeafs'], $_POST['leaf']))
                {
                        if (count($_POST['leaf']) > 0) 
                        {
                                foreach ($_POST['leaf'] as $leafId) 
                                {
                                        $toDelte = $modelName::findOne($leafId);
                                        if ($toDelte) 
                                                $toDelte->delete();
                                }

				Yii::$app->session->setFlash('leafDeleted', 'Yep');
                        }

                        if (isset($_SERVER['HTTP_REFERER']))
                                Yii::$app->controller->redirect($_SERVER['HTTP_REFERER']);
                }
        }

        /**
         * handle_leafs_move 
         *
         * Tree in admin view calls this function every time elements has been moved.
         * It updates parent_id and sorter
         */
        private function _handle_leafs_move()
        {
                if (isset($_GET['jqtree_is_moved'])) 
                {
                        if (! Yii::$app->request->isAjax) 
                                throw new ForbiddenHttpException('Access via AJAX only');


                        $modelName     = $this->modelName;
                        $parentIdField = $this->parentIdField;
                        $orderField    = $this->orderField;


                        //=========== Parent_id update ===========
                        $moved = $modelName::findOne($_GET['moved_node_id']);

                        if (! $moved)
                                throw new NotFoundHttpException('Moved model not found');

                        if ($_GET['position'] == 'inside') 
                                $moved->$parentIdField = $_GET['target_node_id'];
                        else 
                                $moved->$parentIdField = $_GET['target_node_parent_id'];

                        $moved->save(false);


                        //=========== Sorter update ===========
                        $oldPositions = array_diff($_GET['oldPositions'], array($_GET['moved_node_id']));
                        $oldPositions = array_values($oldPositions);

                        // If inserting at the begining of the tree
                        if ($_GET['position'] == 'before') 
                        {
                                array_unshift($oldPositions, $_GET['moved_node_id']);
                        }
                        else
                        {
                                $movedElementPosition = array_search($_GET['target_node_id'], $oldPositions) + 1;

                                array_splice($oldPositions, $movedElementPosition, 0, $_GET['moved_node_id']);
                        }

                        for ($i = 0; $i < count($oldPositions); $i++) 
                        {
                                $model = $modelName::findOne($oldPositions[$i]);

                                $model->$orderField = $i;
                                $model->save(false);
                        }
                }
        }

        /**
         * getJsonTree 
         * 
         * @return json tree
         */
        private function _getJsonTree()
        {
                return json_encode($this->_getTree($this->statusField, $this->parentIdField));
        }

        /**
         * _getTree 
         * 
         * @param string $statusField 
         * @param string $parentIdField 
         * @param int $pid 
         *
         * @return array tree
         */
        private function _getTree($statusField, $parentIdField, $pid = 0)
        {
                $op = array();

                foreach( $this->models as $model )
                {
                        if( $model->$parentIdField == $pid ) 
                        {
                                $children = $this->_getTree($statusField, $parentIdField, $model->id);

                                if ($statusField)
                                        $status = ($model->$statusField == 1) ? '' : 'jqtree-inactive';
                                else
                                        $status = '';

                                $leafLink = "<label class='checkbox ".$status."'>";
				$leafLink .= Html::checkBox('leaf[]', false, array('value'=>$model->id)).' ';
				$leafLink .= call_user_func($this->leafName, $model);
				$leafLink .= "</label>";

                                if ($children) 
                                {
                                        $op[] = array(
                                                'id'        => $model->id,
                                                'name'      => $leafLink,
                                                'parent_id' => $model->$parentIdField,
                                                'children'  => $children,
                                        );
                                }
                                else
                                {
                                        $op[] = array(
                                                'id'        => $model->id,
                                                'name'      => $leafLink,
                                                'parent_id' => $model->$parentIdField,
                                        );
                                }
                        }
                }

                return $op;
        }
}
