<?php
if(!isset($_index_rights) || !isset($_GET['id'])){
	header("Location: ".getUrl()."admin");
}

function editpage_alterPage($pageid) {
	global $DB;
	
	$tempid = intval($_POST['temp']);
	
	$bPage = false;
	/// instance of page 'core_pages' ///
	$aVals = array(	array('template',$tempid,false),
					//array('user',cCfg::getUserData('id'),false)
					array('user',0,false)
					);
	
	if($_POST['parent_page']!='null'){
		$aVals[] = array('parentid',intval($_POST['parent_page']),false);
	}else{
		$aVals[] = array('parentid','NULL',false);
	}
	
	if(isset($_POST['cache'])) {
		$aVals[] = array('usecache',1,false);
	}else{
		$aVals[] = array('usecache',0,false);
	}
	
	if(isset($_POST['publish'])){
		$aVals[] = array('published',1,false);
	}else{
		$aVals[] = array('published',0,false);
	}
	$aVals[] = array('timemodified',getDateToDb());	 
	$bPage = $DB->update('core_pages', $aVals, array('id','=',$pageid));
	
	/// language pages 'core_lngpages'///
	if($bPage) {
		$bPageLng = array();
		foreach(cCfg::$aLangs as $lng) {			
			if(isset($_POST['lng_'.$lng])){
				$title = $_POST['title_'.$lng];
				$menutitle = $_POST['menu_title_'.$lng];
				$url = admin_createPageUrl($_POST['title_'.$lng]);
				if($menutitle == "") {
					$menutitle = $title;
				}
				$lngExists = $DB->select('core_lngpages','id',
										array(array('lng','=',$lng), 'AND', array('page','=',$pageid))
										); 
				if(count($lngExists)) {					
					$bPageLng[$lng] = $DB->update('core_lngpages', 
												array(	array('title',$title),
														array('menutitle',$menutitle),
														array('url',$url),
														array('timemodified',getDateToDb())
													),
												array(array('lng','=',$lng), 'AND', array('page','=',$pageid))
												);
				/// no exists record in core_lngpages of this LNG for this page ///
				}else{
					$bPageLng[$lng] = $DB->insert('core_lngpages', 
													array(	array('title',$title),
															array('menutitle',$menutitle),
															array('url',$url),
															array('page',$pageid,false),
															array('lng',$lng),
														)
												);
				}												
			}else{
				$DB->delete('core_lngpages',array(array('lng','=',$lng), 'AND', array('page','=',$pageid)));
			}	
		}
		
		if(!count($bPageLng) || isset($_POST['lng'])) {
			if($_POST['menut_title'] == "") {
				$_POST['menut_title'] = $_POST['title'];
			}
			$lngExists = $DB->select('core_lngpages','id',
										array(array('lng','=',null), 'AND', array('page','=',$pageid))
										);
			if(count($lngExists)){
				$bPageLng[] = $DB->update(	'core_lngpages', 
										array( 	array('title',$_POST['title']),
												array('menutitle',$_POST['menu_title']),
												array('url',admin_createPageUrl($_POST['title'])),
												array('timemodified',getDateToDb())
											),
										array(array('lng','=',null), 'AND', array('page','=',$pageid))												
									);
			}else{
				$bPageLng[] = $DB->insert('core_lngpages',
										array( 	array('title',$_POST['title']),
												array('page',$pageid,false),
												array('menutitle',$_POST['menu_title']),
												array('url',admin_createPageUrl($_POST['title'])))												
										);
			}
		}else{
			$DB->delete('core_lngpages',array(array('lng','=',null), 'AND', array('page','=',$pageid)));
		}
	}
	
	return ($bPage && count($bPageLng));
}

##########################################################################################
###################################### code ##############################################

$Logs = new cLogs("editpage.php");
$Logs->on();
$aErrors = array();
$aAlerts = array();

/// POST data to insert new page are sent ///
if(isset($_POST['submit']) && (isset($_POST['pageid']) && is_numeric($_POST['pageid']))) {
	foreach($_POST as $k=>$v){
		$_POST[$k] = (get_magic_quotes_gpc() ? trim($v) : trim(addslashes($v)));
	}
	$Check = new cCheckForm();
	
	/// existing templates wasn't selected ///
	$Check->check('temp', '$test !== "null"','TEMPLATE is required!');
	$bTitle = false;
	foreach($_POST as $k=>$v) {
		if(substr($k,0,3) == 'lng'){
			$Check->check('title'.substr($k,3), 'strlen($test)>0','Title of PAGE'.strtoupper(substr($k,3)).' is required!');
			$bTitle = true;
		}
	}
	$Check->check('page_title', '$test == true', 'Title of PAGE is required!',$bTitle);
	$Logs->addLog($Check->isValid(), 'valid');
	//$Logs->addLog($Check->getErrors(),'errors');	
	
	if(!$Check->isValid()) {			
		foreach($Check->getErrors() as $k=>$error) {
			$aErrors[]= admin_getErrorToPrint($k,$error);
		}
		$result = false;
	}else{
		$result = editpage_alterPage($_POST['pageid']);
	}
	
	if($result){
		$aAlerts[] = "Template was succesfully saved.";
	}else{
		$aErrors[] = "Template wasn't saved.";
	}
	
	$Logs->addLog($result, 'alter page RESULT');
}

$allTemps = admin_getAllTemps();
//$Logs->addLog($allTemps, "allTemps");
$allPages = admin_getAllPages();//add_getAllPages();
//$Logs->addLog($allPages, "allPages");

$aPageData = admin_getPageData($_GET['id']);
$Logs->addLog($aPageData, "data");
###########################################################################################
##################################### code to print #######################################
/*
foreach($aErrors as $error) {
	print "<div class='alert'>$error</div>";
}
foreach($aAlerts as $alert) {
	print "<div class='alert'>$alert</div>";
}
*/
?>
<form action="" name="form_editpage" method="post">
	<input type="hidden" name="pageid" value="<?php print $_GET['id'];?>" /> 
<fieldset>
	<p>
	TEMPLATE: 
		<select name="temp">
		<option value="null">---</option>
		<?php foreach($allTemps as $temp): ?>
			<?php $selected = (($aPageData['template'] == $temp['id']) ? 'selected="selected" ' : ''); ?>
			<option value="<?php print $temp['id'];?>" <?php print $selected; ?>><?php echo $temp['name'];?></option>	
		<?php endforeach;?>
		</select>
	</p><p>
	Name of PAGE: <strong><?php print $aPageData['name']; ?></strong>
	</p><p>
	Parent PAGE (in url and pages hierarchy): 
		<select name="parent_page">
		<option value="null">---</option>
		<?php foreach($allPages as $temp): ?>
			<?php $selected = (($aPageData['parentid'] == $temp['id']) ? 'selected="selected" ' : ''); ?>
			<option value="<?php print $temp['id'];?>" <?php print $selected; ?>>
				<?php 	print $temp['name']." [";
						foreach($temp['lng'] as $lng=>$aLng) {
							print $aLng['url'];
							if(is_string($lng)){
								print "($lng) ";
							}							
						}
						print "]";
				?>
			</option>	
		<?php endforeach;?>
		</select>
	</p><p>
	<?php $checked = (($aPageData['usecache'] == 1) ? 'checked="checked" ' : ''); ?>
	Use CACHE: <input type="checkbox" name="cache" <?php print $checked;?>/>
	</p>
	<fieldset>
		Language (if NONE is checked, page will be available for all languages but with same title):<br />
		<?php 
			$checked = '';
			$title = '';
			$menuttitle = '';
			$url = '';
			if(isset($aPageData['lng'][0])) {
				$checked = 'checked="checked"';
				$menutitle = $aPageData['lng'][0]['menutitle'];
				$title = $aPageData['lng'][0]['title'];
				$url = "[part in URL: ".$aPageData['lng'][0]['url']."]";			
			}
		?>
		<p>
			None: <input name="lng" type="checkbox" value="null" <?php print $checked;?>/>
			Title: <input name="title" value="<?php print $title;?>" maxLength="100" />
			<?php print $url; ?><br/>
			Title for menu: <input name="menu_title" value="<?php print $menutitle;?>" maxLength="100" />
		</p>
		<?php foreach(cCfg::$aLangs as $lng): 
			$checked = '';
			$title = '';
			$menutitle = '';
			$url = '';
			if(isset($aPageData['lng'][$lng])) {
				$checked = 'checked="checked"';
				$menutitle = $aPageData['lng'][$lng]['menutitle'];
				$title = $aPageData['lng'][$lng]['title'];
				$url = "[part in URL: ".$aPageData['lng'][$lng]['url']."]";			
			}
		?>
			<p>
			<?php print $lng?>: <input name="lng_<?php print $lng?>" type="checkbox" value="<?php print $lng;?>" <?php print $checked; ?>/>
			Title: <input name="title_<?php print $lng;?>" value="<?php print $title;?>" maxLength="100" />
			<?php print $url; ?><br/>
			Title for menu: <input name="menu_title_<?php print $lng;?>" value="<?php print $menutitle;?>" maxLength="100" />
			</p>
		<?php endforeach;?>		
	</fieldset>
	<p>
		<?php $checked = ($aPageData['published']) ? 'checked="checked" ' : ''; ?>
		Published: <input type="checkbox" name="publish" <?php print $checked; ?>/>
	</p>
	<input type="submit" name="submit" value="submit" />
</fieldset>
</form> 
