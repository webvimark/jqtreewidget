<?php
namespace webvimark\extensions\jqtreewidget;

use yii\web\AssetBundle;

/**
 * JQTreeWidget 
 * 
 * @version 1.0
 * @author vi mark <webvimark@gmail.com> 
 * @license MIT
 */
class JQTreeWidgetAsset extends AssetBundle
{
	public function init()
	{
		$this->sourcePath = __DIR__ . '/assets';
		$this->js = ['tree.jquery.js'];
		$this->css = ['jqtree.css', 'added.css'];

		$this->depends = [
			'yii\web\JqueryAsset',
		];

		parent::init();
	}
}
