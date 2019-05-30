<?php
/* @var $this yii\web\View */

use yii\helpers\Html;

$this->title = 'Выдача случайного приза';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class='casino-wrap-50'>
	<h1><?= Html::encode($this->title) ?></h1>
	<div class="casino-random-prize">

		<div class="getButton">
			Получить случайный приз
		</div>
		<div class='content'></div>
		<div class='loader'></div>
		<div class='try-again'><a href='/'>Испытать удачу снова!</a></div>
	</div>
</div>
