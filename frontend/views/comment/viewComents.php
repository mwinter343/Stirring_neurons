<?php

use yii\helpers\Html;
use yii\widgets\DetailView;
use frontend\models\Comments;

/* @var $this yii\web\View */
/* @var $model frontend\models\Comments */

$this->title = $model->idComment;
$this->params['breadcrumbs'][] = ['label' => 'Comments', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>




