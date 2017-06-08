<?php

use yii\helpers\Html;
use yii\widgets\DetailView;
use frontend\models\Comments;
use frontend\components\Helper;

/* @var $this yii\web\View */
/* @var $model frontend\models\Comments */

$this->title = $model->idComment;
$this->params['breadcrumbs'][] = ['label' => 'Comments', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="comments-view">

    <h1><?= Html::encode($this->title)?></h1>

    <?php
        $comment = new Comments();

        $helper = new frontend\components\Helper();

        $helper->createdTableHyperLinks();

    ?>

</div>
