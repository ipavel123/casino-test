<?php

namespace app\controllers;

use Yii;
use yii\web\Controller;
use yii\web\Response;
use yii\db\Expression;

// api банка в качестве примера

class ApiBankController extends Controller
{
	static private $url_host = 'bank.com'; // хост банка
	static private $path_invoice = '/isinvoice/'; // путь метода проверки существования счета
	static private $path_send_money = '/sendmoney/'; // путь метода проверки существования счета
	static private $token = 'thereMustBeToken'; // токен авторизации в системе банка

	// проверка счета

	private function isInvoiceExist($invoice)
	{
		$data = [
			'invoice' => $invoice
		];
		$verify = self::sendSoapRequest(self::$url_host . self::$path_invoice);

		return $verify;
	}

	// отправка денег на счет банка
	// return $response = 0 - fail, 1 - success
	static public function sendMoney($invoice, $money)
	{
		$relult = false;

		if (self::isInvoiceExist($invoice))
		{
			$data = [
				'invoice' => $invoice,
				'money' => $money,
				'token' => $this->token
			];
			$response = self::sendSoapRequest(self::$url_host . self::$path_send_money, $data);
		}
		// подразумевается, что возвращает всегда 1 - success
		return 1;
	}

	// отправка http запроса
	function sendSoapRequest($url, $data = [], $method = 'GET', $attr = [])
	{
		$ch = curl_init();

		$_attr['is_return'] = true;
		$_attr['headers'] = [];

		$attr = array_merge($_attr, $attr);

		if ($method == 'GET')
		{
			$params = http_build_query($data);
			$url = $url . '?' . ($params ?: '');
		}
		if (in_array($method, ['PUT', 'POST']))
		{
			curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
			// CURLOPT_POSTFIELDS подмассивы не передаёт, поэтому преобразоваваем в формат "параметров запроса"
			curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
		}
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_HTTPHEADER, $attr['headers']);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, $_attr['is_return']);

		// $output = curl_exec($ch); допустим отправили
		curl_close($ch);

		return $output;
	}

}
