<?php
/**
 * SET CORRECT:
 *   HTTP_PATH and ROOT_PATH
 *   DB_SERVER
 *   DB_DATABASE
 *   DB_ADMIN_USER   
 *   DB_ADMIN_PASSWORD
 *    
 *  'http://localhost/_core_' by your domain (http://www.domain.cz)
 *  'localhost/_core_' by your domain (domain.cz)
 *  
 *OTHER OPTIONS TO BE SET:
 * 	ENCODING
 * 	TITLE
 * 	MK_DEBUG
 * 	DB_DEBUG  
 *  REDIRECT_AFTER_LOGIN
 */
    
// debug mode
define ("MK_DEBUG", true);
define ("DB_DEBUG", true); /// povoluje zapis Logu z tridy cDb do souboru
define ("REDIRECT_AFTER_LOGIN", true); /// allows redirect to page(defined in cCfg) after login directly from cAuthentication

/// set to true to protect(then available only for 'superadmin' role ///
define ("ADMIN_PAGE_ACCESS_AUTHORIZIED", true); 

// endoding
define ("ENCODING", "utf-8");

// main title
define ("TITLE", "Fakturaceonline.cz");

// http
define ("HTTP_PATH", "http://localhost/fakturyonline/");
// root
define ("ROOT_PATH", "D:/XAMPP/xampp/htdocs/fakturyonline/");

// DS - oddelovac pro adresare
define ('DS', DIRECTORY_SEPARATOR);
// paths for __autoload
set_include_path
(
ROOT_PATH.'core'.DS.'class'.DS.PATH_SEPARATOR. // core/class/
ROOT_PATH.'pages'.DS.'actions'.DS.PATH_SEPARATOR. // pages/actions/
ROOT_PATH.'pages'.DS.'tpl'.DS.'php'.DS.PATH_SEPARATOR. // pages/tpl/php/
get_include_path()
);

// timeout autoodhlasenia (v sekundach)
define ("AUTH_TIMEOUT", 60*60*4);

// i_user administratora - admin
define ("ADMINID", 0);

define ("DB_SERVER", 'localhost');
define ("DB_DATABASE",'fakturyonline');
define ("DB_ADMIN_USER", 'root');
define ("DB_ADMIN_PASSWORD", 'root');

// platnost cookie
define ("AUTH_EXPIRECOOKIE", 60*60*24);
?>
