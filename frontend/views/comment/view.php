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
<div class="comments-view">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a('Update', ['update', 'id' => $model->idComment], ['class' => 'btn btn-primary']) ?>
        <?= Html::a('Delete', ['delete', 'id' => $model->idComment], [
            'class' => 'btn btn-danger',
            'data' => [
                'confirm' => 'Are you sure you want to delete this item?',
                'method' => 'post',
            ],
        ]) ?>
    </p>

    <?= DetailView::widget([
        'model' => $model,
        'attributes' => [
            'idComment',
            'idUser',
            'comment',
            'Created',
            'Archived',
        ],
    ]) ?>

    <?php
        $comment = new Comments();

        echo "" . $comment->showComments(2);
    ?>

</div>
