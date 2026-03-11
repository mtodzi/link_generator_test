<?php

use yii\helpers\Html;
use yii\bootstrap5\ActiveForm;
use app\assets\ShortLinkAsset;

/** @var yii\web\View $this */
/** @var app\models\ShortLinkForm $model */

ShortLinkAsset::register($this); // Регистрируем наш Asset Bundle
$this->title = 'Генератор коротких ссылок';
?>
<div class="generatinga_short_link">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>Введите URL, чтобы его сократить.</p>

    <?php $form = ActiveForm::begin([
        'id' => 'short-link-form', // ID для нашего JS
        'action' => ['site/create-short-link'], // Экшен для AJAX-запроса
    ]); ?>

        <?= $form->field($model, 'original_url')->textInput(['autofocus' => true]) ?>

        <div class="form-group">
            <?= Html::submitButton('Сократить', ['class' => 'btn btn-primary']) ?>
        </div>
    <?php ActiveForm::end(); ?>

    <div id="result" class="mt-3"></div>

</div><!-- generatinga_short_link -->