<?php
/* @var $this yii\web\View */

use yii\helpers\Html;

if ($toolbox && !isset($toolbox[0]))
{
	$toolbox = [$toolbox];
}
?>
<div class="casino-prizes-result">

	<div class='casino-prize-box shadow <?= $prize_name ?>'>
		<div class='title'><?= $prize_title ?></div>
		<div class='prize-value' data-prize-value='<?= $prize_value ?>'><?= $prize_value ?></div>
	</div>
	<?php
	if ($error)
	{
		?>
		<div class='error shadow'>К сожалению, <?= $error['text'] ?></div>
		<?php
	}
	if ($toolbox && !$error)
	{
		foreach ($toolbox as $tool)
		{
			?>
			<div class='toolbox shadow'>
				<div class='result-button' data-method='<?= $tool['method'] ?>'><?= $tool['text'] ?></div>
			</div>
		<?php } ?>
		<div class='response shadow'></div>
	<?php } ?>
</div>

