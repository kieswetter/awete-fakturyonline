CREATE TABLE `fakturyonline`.`useraccount` (
`id` INT( 11 ) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY ,
`user` INT( 11 ) UNSIGNED NOT NULL ,
`title` VARCHAR( 255 ) NULL ,
`phone` VARCHAR( 50 ) NOT NULL DEFAULT '',
`email` VARCHAR( 100 ) NOT NULL DEFAULT '',
`typ_fak_def` TINYINT( 1 ) NOT NULL DEFAULT '0',
`splatnost_def` SMALLINT( 3 ) UNSIGNED NOT NULL DEFAULT '0',
`zaokr` TINYINT( 1 ) UNSIGNED NOT NULL DEFAULT '0',
`uzit_cisl_zak` TINYINT( 1 ) UNSIGNED NOT NULL DEFAULT '0',
`title_from` VARCHAR( 100 ) NULL DEFAULT NULL,
`email_from` VARCHAR( 100 ) NULL DEFAULT NULL,
`odkazy` TEXT NULL,
`logo` VARCHAR( 100 ) NULL DEFAULT NULL
) ENGINE = MYISAM ;

CREATE TABLE `fakturyonline`.`dodavatele` (
`id` INT( 11 ) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY ,
`useracc` INT( 11 ) UNSIGNED NULL DEFAULT NULL,
`nazev` VARCHAR( 255 ) NOT NULL ,
`ulice` VARCHAR( 255 ) NOT NULL ,
`cislo` VARCHAR( 50 ) NOT NULL DEFAULT '',
`mesto` VARCHAR( 255 ) NOT NULL ,
`psc` VARCHAR( 50 ) NOT NULL ,
`ucet` VARCHAR( 50 ) NULL DEFAULT NULL,
`bankod` INT( 4 ) NULL DEFAULT NULL,
`web` VARCHAR( 255 ) NOT NULL DEFAULT '',
`email` VARCHAR( 100 ) NOT NULL DEFAULT '',
`tel` VARCHAR( 50 ) NOT NULL DEFAULT '',
`mobil` VARCHAR( 50 ) NOT NULL DEFAULT '',
`fax` VARCHAR( 50 ) NOT NULL DEFAULT '',
`ico` INT( 11 ) UNSIGNED NOT NULL, 
`dic` VARCHAR( 50 ) NULL DEFAULT NULL,
`soud` VARCHAR( 255 ) NOT NULL DEFAULT '',
`spis_zn` VARCHAR( 50 ) NOT NULL DEFAULT '',
`platce_dph` TINYINT( 1 ) NOT NULL DEFAULT '0',
INDEX ( `useracc` )
) ENGINE = MYISAM ;

CREATE TABLE `fakturyonline`.`odberatele` (
`id` INT( 11 ) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY ,
`useracc` INT( 11 ) UNSIGNED NULL DEFAULT NULL ,
`nazev` VARCHAR( 255 ) NOT NULL ,
`ulice` VARCHAR( 255 ) NOT NULL ,
`mesto` VARCHAR( 255 ) NOT NULL ,
`psc` VARCHAR( 50 ) NOT NULL ,
`ico` INT( 11 ) UNSIGNED NULL DEFAULT NULL, 
`dic` VARCHAR( 50 ) NULL DEFAULT NULL,
`email` VARCHAR( 100 ) NOT NULL DEFAULT '',
`tel` VARCHAR( 50 ) NOT NULL DEFAULT '',
INDEX ( `useracc` )
) ENGINE = MYISAM ;

CREATE TABLE `fakturyonline`.`faktury` (
`id` INT( 11 ) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY ,
`useracc` INT( 11 ) UNSIGNED NULL DEFAULT NULL,
`dodavatel` INT( 11 ) UNSIGNED NULL ,
`odberatel` INT( 11 ) UNSIGNED NULL ,
`cislo` VARCHAR( 255 ) NOT NULL ,
`splatnost` INT( 11 ) UNSIGNED NOT NULL DEFAULT '0', 
`datum_vyst` TIMESTAMP NOT NULL ,
`datum_splat` TIMESTAMP NOT NULL ,
`zpusob_uhr` TINYINT( 1 ) NOT NULL DEFAULT '1',
`varsymbol` VARCHAR( 255 ) NOT NULL ,
`vystavil` VARCHAR( 255 ) NOT NULL ,
`vystavil_tel` VARCHAR( 255 ) NOT NULL ,
`typ` TINYINT( 1 ) NOT NULL DEFAULT '1',
`finished` TINYINT( 1 ) NOT NULL DEFAULT '1',
`ip` VARCHAR(39) NULL DEFAULT NULL,
INDEX ( `useracc` , `odberatel` , `dodavatel` )
) ENGINE = MYISAM ;
 
CREATE TABLE `fakturyonline`.`faktury_items` (
`id` INT( 11 ) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY ,
`faktura` INT( 11 ) UNSIGNED NOT NULL ,
`cislo` INT( 11 ) UNSIGNED NOT NULL ,
`popis` VARCHAR( 255 ) NOT NULL ,
`pocet_jedn` INT( 11 ) UNSIGNED NOT NULL ,
`cena_jedn` INT( 11 ) UNSIGNED NOT NULL ,
`mena` VARCHAR( 10 ) NOT NULL ,
INDEX ( `faktura` )
) ENGINE = MYISAM ;
