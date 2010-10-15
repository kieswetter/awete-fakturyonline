<?php
function admin_recordExists($table,$column,$value) {
	$recExists = cDb::select($table, '*', array($column,'=',trim($value)));
	if(is_array($recExists) && count($recExists)) {
		return $recExists[0];
	}
	return false;
}
function admin_pageExists($name) {
	return admin_recordExists('core_pages','name',$name);
}

function admin_tempExists($name) {
	return admin_recordExists('core_templates','name',$name);
}

function admin_roleExists($name) {
	return admin_recordExists('core_roles','name',$name);
}

function admin_capabExists($name) {
	return admin_recordExists('core_capabilities','name',$name);
}

function admin_userLoginExists($name) {
	return admin_recordExists('core_users','login',$name);
}

function admin_getAllPages() {	
	$return = array();
	$aPages = cDb::select(array('core_pages',
								array('core_pages.id','=','core_lngpages.page','LEFT'),
								'core_lngpages',
								array('core_pages.template','=','temp.id','LEFT'),
								'core_templates as temp'
								),
						array('core_pages.*','lng','title','url','menutitle',
								'core_lngpages.id as lngpageid','core_lngpages.timecreated as lngpagetimecreated','core_lngpages.timemodified as lngpagetimemodified',
								'temp.name as tempname'
							)
						);
	return admin_parsePageData($aPages);
}

function admin_getAllTemps() {	
	$aTemps = cDb::select('core_templates',	'*');
	if(is_array($aTemps)){
		return $aTemps; 
	}
	return array();
}

function admin_createPageUrl($sPage) {
	$url = parseCorectUrl($sPage);
	$aUrls = cDb::select('core_lngpages', 'url', array('url','LIKE',$url."%"));
	
	if(count($aUrls) > 1) {
		$aPages = array();
		foreach($aUrls as $page) {
			$aPages[] = $page['url'];
		}
		$i=1;
		$urlnew = $url;
		while(in_array($urlnew,$aPages)) {
			$urlnew = $url.$i;
			$i++;
		}
	} else if(count($aUrls) && $aUrls['url'] == $url) {
		$urlnew .= "1";		
	}
	return (isset($urlnew) ? $urlnew : $url);
}

function admin_getPageData($id) {
	$id = (int)trim($id);
	$aPages = cDb::select(array('core_pages',array('core_pages.id','=','core_lngpages.page','LEFT'),'core_lngpages'),
						array('core_pages.*','lng','title','url','menutitle',
								'core_lngpages.id as lngpageid','core_lngpages.timecreated as lngpagetimecreated','core_lngpages.timemodified as lngpagetimemodified' ),
						array('core_pages.id','=',$id)
						);
	$page = admin_parsePageData($aPages);
	return $page[$id];
}

function admin_parsePageData($aPages) {
	$ret = array();
	foreach($aPages as $page) {
		$id = $page['id'];
		if(!isset($ret[$id])) {
			$ret[$id] = array(	'id'=>(int)$page['id'],
							'parentid'=>(int)$page['parentid'],
							'name'=>$page['name'],
							'template'=>(int)$page['template'],
							'tempname'=>$page['tempname'],
							'timecreated'=>$page['timecreated'],
							'timemodified'=>$page['timemodified'],
							'published' => (int)$page['published'],
 							'timeexpired'=>$page['timeexpired'],
							'usecache'=>(int)$page['usecache'],
							'user'=>(int)$page['user'],
							'lng' => array()
						);
		}
		$lng = array('title'=>$page['title'],
					'menutitle'=>$page['menutitle'],
					'url'=>$page['url'],
					'id'=>(int)$page['lngpageid'],
					'timecreated' => $page['lngpagetimecreated'],
					'timemodified' => $page['lngpagetimemodified']
					);
		if(!is_null($page['lng'])) {
			$ret[$id]['lng'][$page['lng']] = $lng;
		}else{
			$ret[$id]['lng'][] = $lng;
		}
	
	}
	return $ret;
}

function admin_getTempData($id) {	
	global $Logs;
	$temp = cDb::select('core_templates','*',array('id','=',$id));
	if(is_array($temp)){
		return $temp[0]; 
	}
	return false;
}

function admin_getAllRoles() {	
	$aData = cDb::select('core_roles',	'*');
	if(is_array($aData)){
		return $aData; 
	}
	return array();
}

function admin_getAllUsers() {	
	$aData = cDb::select('core_users', 'id, name, surname, login, role, active');
	if(is_array($aData)){
		return $aData; 
	}
	return array();
}

function admin_getAllCapabWithRights() {	
	$aData = cDb::select(array(	'core_capabilities as c',
								array('c.id','=','capability','LEFT'),
								'core_role_capability as rc',
								),
						array(	'rc.id','rc.role as roleid','c.id as capid','c.name','description')
						);
	if(is_array($aData)){
		return admin_parseCapabData($aData); 
	}
	return array();
}

function admin_parseCapabData($aData) {
	$ret = array();
	foreach($aData as $cap) {
		$id = $cap['capid'];
		if(!isset($ret[$id])) {
			$ret[$id] = array(	'id'=>(int)$id,
							'name'=>$cap['name'],
							'description'=>$cap['description'],
							'aRoles'=>array()
						);
		}
		if(!is_null($cap['roleid'])){
			$ret[$id]['aRoles'][] = $cap['roleid'];
		}	
	}
	return $ret;
}

function admin_getErrorToPrint($field, $error) {
	return "<strong>$field</strong>: " . implode("<br /><strong>$field</strong>: ",$error['msg']);	
}

?>