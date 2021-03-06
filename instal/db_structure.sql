CREATE DATABASE `fakturyonline` DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci;

CREATE TABLE `fakturyonline`.`core_templates` (
`id` INT( 11 ) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY ,
`parentid` INT( 11 ) UNSIGNED NULL DEFAULT NULL ,
`name` VARCHAR( 50 ) NOT NULL ,
`css` TEXT NULL ,
`js` TEXT NULL ,
`timecreated` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ,
`timemodified` TIMESTAMP NULL DEFAULT NULL ,
UNIQUE (`name`)
)
ENGINE = MYISAM ;

CREATE TABLE `fakturyonline`.`core_pages` (
`id` INT( 11 ) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY ,
`parentid` INT( 11 ) UNSIGNED NULL ,
`name` VARCHAR( 50 ) NOT NULL ,
`template` INT( 11 ) UNSIGNED NOT NULL ,
`timecreated` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ,
`timemodified` TIMESTAMP NULL DEFAULT NULL ,
`published` TINYINT( 1 ) NOT NULL DEFAULT '0',
`timeexpired` TIMESTAMP NULL DEFAULT NULL ,
`usecache` TINYINT( 1 ) NOT NULL DEFAULT '1',
`user` INT( 11 ) UNSIGNED NOT NULL
) ENGINE = MYISAM ;

CREATE TABLE `fakturyonline`.`core_lngpages` (
`id` INT( 11 ) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY ,
`lng` CHAR( 2 ) NULL DEFAULT NULL,
`page` INT( 11 ) UNSIGNED NOT NULL ,
`title` VARCHAR( 100 ) NOT NULL ,
`url` VARCHAR( 100 ) NOT NULL ,
`menutitle` VARCHAR( 100 ) NOT NULL,
`timecreated` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ,
`timemodified` TIMESTAMP NULL DEFAULT NULL
) ENGINE = MYISAM ;

CREATE TABLE `fakturyonline`.`core_navigations` (
`id` INT( 11 ) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY ,
`name` VARCHAR( 50 ) NULL ,
`title` VARCHAR( 100 ) NULL,
`lngpages` TEXT NULL,
UNIQUE (`name`) 
) ENGINE = MYISAM ;

CREATE TABLE `fakturyonline`.`core_roles` (
`id` INT( 11 ) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY ,
`parentid` INT( 11 ) UNSIGNED NULL ,
`name` VARCHAR( 50 ) NOT NULL ,
UNIQUE (`name`)
) ENGINE = MYISAM ;

INSERT INTO `fakturyonline`.`core_roles` (`name`) VALUES ('superadmin');

CREATE TABLE `fakturyonline`.`core_users` (
`id` INT( 11 ) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY ,
`name` VARCHAR( 100 ) NOT NULL ,
`surname` VARCHAR( 100 ) NOT NULL,
`role` INT( 11 ) UNSIGNED NOT NULL ,
`login` VARCHAR( 50 ) NOT NULL ,
`password` CHAR( 50 ) NOT NULL ,
UNIQUE (`login`)
) ENGINE = MYISAM ;

CREATE TABLE `fakturyonline`.`core_capabilities` (
`id` INT( 11 ) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY ,
`name` VARCHAR( 50 ) NOT NULL ,
`description` VARCHAR( 255 ) NULL ,
UNIQUE (`name`)
) ENGINE = MYISAM ;

CREATE TABLE `fakturyonline`.`core_role_capability` (
`id` INT( 11 ) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY ,
`role` INT( 11 ) UNSIGNED NOT NULL ,
`capability` INT( 11 ) UNSIGNED NOT NULL
) ENGINE = MYISAM ;

CREATE TABLE `fakturyonline`.`core_authentications` (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `user` int(11) unsigned NOT NULL,
  `timeinit` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ,
  `session` VARCHAR(32) NOT NULL
) ENGINE=MYISAM;

CREATE TABLE `fakturyonline`.`core_challenges` (
    `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `timecreated` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=MYISAM; 
