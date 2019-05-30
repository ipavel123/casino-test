<?php

// дебаг вывода
function dd($out, $is_die = false)
{
	echo "<pre class='debag-dd'>";
	var_dump($out);
	echo '</pre>';

	if ($is_die)
	{
		die();
	}
}

// xml объект перебразует в массив
function json_array(object $data)
{
	$result = json_decode(json_encode($data, JSON_UNESCAPED_UNICODE), 1);

	return $result;
}
