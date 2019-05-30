<?php

namespace app\controllers;

use Yii;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\web\Response;
use yii\filters\VerbFilter;
use app\models\LoginForm;
use app\models\ContactForm;
use app\models\PrizesLimit;
use app\models\Transitions;
use app\models\Gifs;
use app\models\User;
use yii\db\Expression;

class CasinoController extends Controller
{
	private $range_points = [50, 1000]; // диапозон рандомных баллов
	private $range_money = [10, 500]; // диапозон рандомных денег
	public static $coefficient_to_ponts = 1.2;  // коэффициент перевода денег в баллы

	public function __construct($id, $module, $config = [])
	{
		parent::__construct($id, $module, $config);
	}

	public function actionIndex()
	{
		return $this->render('index');
	}

	/**
	 * Получение случайного приза
	 *
	 * @return html
	 */
	public function actionGetprize()
	{
		$error = false;
		$toolbox = false;
		$name_prizes = ['points', 'money', 'gifs'];
		$prize_name = $name_prizes[array_rand($name_prizes)];
		$post = Yii::$app->request->post();
		$user_model = User::findOne(Yii::$app->user->identity->id);

		if ($prize_name == 'points')
		{
			$points = rand($this->range_points[0], $this->range_points[1]);
			$prize_title = 'Баллы';
			$prize_value = $points + ' баллов';
			$user_model->points += $points;
			$user_model->save();
		}
		if ($prize_name == 'money')
		{
			$money = rand($this->range_money[0], $this->range_money[1]);
			$common_money = PrizesLimit::find()->where(['code' => 'money'])->one();

			// проверяем доступность денег
			if ($money > $common_money->count)
			{
				$error = [
					'code' => 'moneyLimit',
					'text' => 'данная сумма недоступна. Попробуйте выиграть меньшую сумму'
				];
			}
			$prize_title = 'Деньги';
			$prize_value = $money . ' ₽';
			$toolbox = [
				[
					'text' => 'Запросить перевод',
					'method' => 'request-transition'
				],
				[
					'text' => 'Конвертировать',
					'method' => 'request-convert'
				]
			];
		}
		if ($prize_name == 'gifs')
		{
			$common_gifs = PrizesLimit::find()->where(['code' => 'gifs'])->one();
			$gif = Gifs::find()->orderBy(new Expression('rand()'))->one();

			// проверяем доступность подарков
			if ($common_gifs->count <= 0)
			{
				$error = [
					'code' => 'gifsLimit',
					'text' => 'подарков больше нет'
				];
			} else
			{
				$common_gifs->count -= 1;
				$common_gifs->save();
			}
			$prize_title = 'Подарок';
			$prize_value = $gif->name;
			$toolbox = [
				'text' => 'Отправить мне на почту',
				'method' => 'send-gif-to-email'
			];
		}
		$params = [
			'prize_name' => $prize_name,
			'prize_title' => $prize_title,
			'prize_value' => $prize_value,
			'toolbox' => $toolbox,
			'error' => $error
		];
		return $this->renderPartial('prizes', $params);
	}

	// запрос на отправку денег в банк
	public function actionRequestTransition()
	{
		$error = false;
		$post = Yii::$app->request->post();
		$common_money = PrizesLimit::find()->where(['code' => 'money'])->one();
		preg_match('/\d+/i', $post['prize_value'], $count);
		$count = array_shift($count);

		if ($count > $common_money)
		{
			$error = 'Деньги закончились';
		} else
		{
			$transition = new Transitions();
			$transition->user_id = Yii::$app->user->identity->id;
			$transition->invoice = Yii::$app->user->identity->invoice;
			$transition->count = $count;
			$result = $transition->save();

			$common_money->count -= $count;
			$common_money->save();
		}
		if (!$result)
		{
			$error = 'Что-то пошло не так';
		}
		$json = [
			'text' => 'Запрос отправлен. Деньги на ваш банковский счет скоро поступят',
			'error' => $error
		];
		return json_encode($json);
	}

	// запрос конвертации денег в баллы
	public function actionRequestConvert()
	{
		$error = false;
		$post = Yii::$app->request->post();
		preg_match('/\d+/i', $post['prize_value'], $count);
		$count = array_shift($count);

		$user_model = User::findOne(Yii::$app->user->identity->id);
		$user_model->points += self::convertToPoints($count);
		$user_model->save();

		$json = [
			'text' => "Конвертация прошла успешна. Деньги сконвертированы по коэффициенту: " . self::$coefficient_to_ponts . ".<br>"
			. "Было " . Yii::$app->user->identity->points . " баллов, стало " . round($user_model->points),
			'error' => $error
		];
		return json_encode($json);
	}

	// конвертация денег в баллы
	public static function convertToPoints($money)
	{
		$points = $money * self::$coefficient_to_ponts;

		return $points;
	}

	// отправка баллов на почту
	public function actionSendGifToEmail()
	{
		$error = false;
		$post = Yii::$app->request->post();

		$message = Yii::$app->mailer->compose();

		if (Yii::$app->user->isGuest)
		{
			$message->setFrom('from@domain.com');
		} else
		{
			$message->setFrom(Yii::$app->params['adminEmail']);
		}
		$response = $message->setTo(Yii::$app->user->identity->email)
				->setSubject('CasinoBest: Вам пришел приз!')
				->setTextBody('В интернет казино CasinoBest.com вы выиграли приз: ' . $post['prize_value'])
				->send();

		if (!$response)
		{
			$error = 'При отправке что-то пошло не так..';
		}

		$json = [
			'text' => 'Подарок отпарвлен вам на почту: ' . Yii::$app->user->identity->email,
			'error' => $error
		];
		return json_encode($json);
	}

}
