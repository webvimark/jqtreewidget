# JQTreeWidget

Wrapper for awesome http://mbraak.github.io/jqTree/ 

--

Lets say you have model Category with following structure:

* id
* parent_id
* active
* sorter

Then name, description and so on.

This widget will help you to create nice tree, which allows you to sort and change structre by drug and drop

**Twitter bootstrap 2** would be nice, but not necessary

## Installation

1) Place this widget in "extensions" folder
2) Probably you have folder named **"jqtreewidget"** in lowercase. Rename it to **"JQTreeWidget"**

## Usage

If your model doesn't have _'status'_ or similar field, then just remove **'statusField'**

```php

<?php $this->widget('ext.JQTreeWidget.JQTreeWidget', array(
        'models'        => Category::find()->orderBy('sorter')->all(), // It has to be ordered by **'orderField'**
        'modelName'     => 'Category',
        'parentIdField' => 'parent_id',
        'statusField'   => 'active',
        'orderField'    => 'sorter',
        'withChildren'    => false, //default = true
        'leafName'      => function($model){
			return Html::a($model->name, ['/content/page/update', 'id'=>$model->id]);
		},
)) ?>

```
