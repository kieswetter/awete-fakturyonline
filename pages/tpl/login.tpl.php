<div>
	<div id="login_js_missing" class="error"><?php print getString('Pro přihlášení je vyžadován zapnutý Javascript!','login')?></div>
	<?php if($login->errors) :?>
		<?php foreach($login->errors as $err):?>
			<div class="error"><?php print $err;?></div>
		<?php endforeach;?>
	<?php endif;?>
	
	<?php if(isset($login->challenge)) :?>
		<form action="<?php print $login->href; ?>" name="form_authentication" id="form_authentication" method="post" >
		<fieldset>
		<input type="hidden" name="challenge" value="<?php echo $login->challenge; ?>" />
		<input type="hidden" name="password_hmac" value="" />
		Login: <input name="login" />
		Heslo: <input type="password" name="password" />
		<input type="submit" value="Přihlásit se" />
		</fieldset>
		</form>
	<?php else:?>
		<p>Uživatel je již přihlášen.</p>
		<a href="?logout">odhlásit</a>
	<?php endif;?>
</div>