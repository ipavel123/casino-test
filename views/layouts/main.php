<?php
/* @var $this \yii\web\View */
/* @var $content string */

use app\widgets\Alert;
use yii\helpers\Html;
use yii\bootstrap\Nav;
use yii\bootstrap\NavBar;
use yii\widgets\Breadcrumbs;
use app\assets\AppAsset;
use app\models\User;
use app\models\PrizesLimit;

AppAsset::register($this);
Yii::$app->name = 'CasinoBest';
$user_model = User::findOne(Yii::$app->user->identity->id);
$money_model = PrizesLimit::find()->where(['code' => 'money'])->one();
$gifs_model = PrizesLimit::find()->where(['code' => 'gifs'])->one();
?>
<?php $this->beginPage() ?>
<!DOCTYPE html>
<html lang="<?= Yii::$app->language ?>">
	<head>
		<meta charset="<?= Yii::$app->charset ?>">
		<meta http-equiv="X-UA-Compatible" content="IE=edge">
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<?php $this->registerCsrfMetaTags() ?>
		<title><?= Html::encode($this->title) ?></title>
		<?php $this->head() ?>
	</head>
	<body>
		<?php $this->beginBody() ?>

		<div class="wrap">
			<?php
			NavBar::begin([
				'brandLabel' => Yii::$app->name,
				'brandUrl' => Yii::$app->homeUrl,
				'options' => [
					'class' => 'navbar-inverse navbar-fixed-top',
				],
			]);
			echo Nav::widget([
				'options' => ['class' => 'navbar-nav navbar-right'],
				'items' => [
					//['label' => 'About', 'url' => ['/site/about']],
					!Yii::$app->user->isGuest ? "<li class='header-my-points'><a>Мои баллы: $user_model->points</a></li>" : '',
					"<li class='header-money'><a>Деньги казино: $money_model->count</a></li>",
					"<li class='header-gifs'><a>Подарки казино: $gifs_model->count</a></li>",
					//['label' => 'Контакты', 'url' => ['/site/contact']],
					Yii::$app->user->isGuest ? (
							['label' => 'Войти', 'url' => ['/site/login']]
							) : (
							'<li>'
							. Html::beginForm(['/site/logout'], 'post')
							. Html::submitButton(
									'Выйти (' . Yii::$app->user->identity->username . ')', ['class' => 'btn btn-link logout']
							)
							. Html::endForm()
							. '</li>'
							)
				],
			]);
			NavBar::end();
			?>

			<div class="container">
				<?=
				Breadcrumbs::widget([
					'links' => isset($this->params['breadcrumbs']) ? $this->params['breadcrumbs'] : [],
				])
				?>
				<?= Alert::widget() ?>
				<?= $content ?>
			</div>
		</div>

		<footer class="footer">
			<div class="container">
				<p class="pull-left">&copy; My Company <?= date('Y') ?></p>

				<p class="pull-right"><?= Yii::powered() ?></p>
			</div>
		</footer>

		<?php $this->endBody() ?>
	</body>
</html>
<?php $this->endPage() ?>
