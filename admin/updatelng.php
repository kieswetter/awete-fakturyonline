<?php 
if(!isset($_index_rights)){
	header("Location: ".getUrl()."admin");
}
?>

<p>This form update arrays in language files in lng/</p>
<fieldset>
	<form action="">
	<?php
	foreach(cCfg::$aLangs as $lng) {
		print $lng.': <input type="checkbox" value="'.$lng.'" /><br />';
	}
	?>
	<input type="submit" value="Update language" />
	</form>
	TODO
</fieldset>