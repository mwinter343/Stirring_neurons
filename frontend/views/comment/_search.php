<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model frontend\models\CommentsSearch */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="comments-search">

    <?php $form = ActiveForm::begin([
        'action' => ['index'],
        'method' => 'get',
    ]); ?>

    <?= $form->field($model, 'idComment') ?>

    <?= $form->field($model, 'idUser') ?>

    <?= $form->field($model, 'comment') ?>

    <?= $form->field($model, 'Created') ?>

    <?= $form->field($model, 'Archived') ?>

    <div class="form-group">
        <?= Html::submitButton('Search', ['class' => 'btn btn-primary']) ?>
        <?= Html::resetButton('Reset', ['class' => 'btn btn-default']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
