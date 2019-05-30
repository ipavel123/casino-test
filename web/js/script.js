;
$(function ()
{
	var controller = '/casino/';
	var $getPrizeStart = $('.casino-random-prize');
	var $loader = $('.loader');

	function sleep(fn, msec)
	{
		msec = msec || 500;
		setTimeout(fn, msec);
	}

	// действие на получение случайного приза
	$('.getButton', $getPrizeStart).on('click', function ()
	{
		$(this).hide();

		var $content = $('.content', $getPrizeStart);
		$loader.addClass('active');

		sleep(function ()
		{
			$.post(controller + 'getprize', function (html)
			{
				$('.casino-wrap-50 > h1').text('Вы выиграли!!!');
				$content.html(html);
				$loader.removeClass('active');

				if ($(html).find('.casino-prize-box').is('.points') && $(html).find('.error').length == 0)
				{
					$('.header-my-points a').append(' (+ ' + $('.prize-value').data('prize-value') + ')');
				}
				$('.try-again').show();
			});
		});
	});

	// действие на при получении приза
	$($getPrizeStart).on('click', '.toolbox .result-button', function ()
	{
		var $getPrizeResult = $('.casino-prizes-result');
		var method = $(this).data('method');

		var count = $('.prize-value', $getPrizeResult).data('prize-value');
		$('.toolbox', $getPrizeResult).hide();
		$loader.addClass('active');

		sleep(function ()
		{
			$.post(controller + method, {prize_value: count || 0}, function (result)
			{
				$loader.removeClass('active');
				$('.response', $getPrizeResult).html(result.error || result.text).show();
			}, 'json');
		});
	});
})