<?php

/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace app\commands;

use yii\console\Controller;
use app\controllers\CasinoController as Casino;
use app\controllers\ApiBankController as ApiBank;
use yii\console\ExitCode;
use app\models\Transitions;

class GoTransitionsController extends Controller
{

	public function actionIndex($number)
	{
		$transitions = Transitions::find()->where(['is_sended' => 0])->limit($number);

		echo "Найдено: {$transitions->count()} \n";

		foreach ($transitions->each() as $transition)
		{
			if ($this->sendMoneyToBank($transition))
			{
				$transition->is_sended = 1;
				$transition->save();
				echo "( Удача ) Транзакция: $transition->id с суммой $transition->count р. на счет $transition->invoice отправлена \n";
			} else
			{

				echo "(Неудача) Транзакция: $transition->id с суммой $transition->count р. на счет $transition->invoice не отправлена \n";
			}
		}
		return ExitCode::OK;
	}

	// осуществление транзакций выиграных денег на банковский счет
	private function sendMoneyToBank($transition)
	{
		$response = ApiBank::sendMoney($transition->invoice, $transition->count);

		return $response;
	}

}
