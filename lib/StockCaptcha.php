<?php

class StockCaptcha
{
	public function display()
	{
		echo '<div class="form-group row pl-3 pr-3">
		<img class="col-6" src="/captcha">
		<input class="col-6 form-control" type="text" name="stock_captcha" />
		</div>';
	}

	public function verify()
	{
		return (isset($_POST['stock_captcha']) && $_POST['stock_captcha'] == $_SESSION['stock_captcha']);
	}
}