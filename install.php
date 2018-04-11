<?php

$prefix = OW_DB_PREFIX."somusicapi_";

$sql = '
CREATE TABLE IF NOT EXISTS `'.$prefix.'push_notification` (
    `registrationId` VARCHAR(256) NOT NULL,
	`userId` INT NOT NULL, 
	PRIMARY KEY (`registrationId`)
) ENGINE = MyISAM  DEFAULT CHARSET=utf8;

';

OW::getDbo()->query($sql);


$path = OW::getPluginManager()->getPlugin('somusicapi')->getRootDir().'langs.zip';
OW::getLanguage()->importPluginLangs($path, 'somusicapi');


