<?php

namespace tests\unit\models;

use app\models\User;
use app\controllers\CasinoController as Casino;

class ConvertTest extends \Codeception\Test\Unit
{

	// проверка метода convertToPoints на рассчет конвертации из денег в баллы
	public function testConvert()
	{
		$money = 500;
		$koof = Casino::$coefficient_to_ponts;
		expect_that($koof > 0);
		$expect_points = $money * $koof;
		$points = Casino::convertToPoints($money);
		expect($points)->equals($expect_points);

		expect_not(is_null($points));
	}

}
