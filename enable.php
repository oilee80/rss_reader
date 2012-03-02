<?php
$sql = 'CREATE TABLE IF NOT EXISTS `'.TABLE_PREFIX.'rss_reader` (
	`id` int(11) unsigned NOT NULL AUTO_INCREMENT,
	`feed` varchar(255) NOT NULL COMMENT "feed url",
	`feed_data` text NOT NULL,
	`created` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
	PRIMARY KEY (`id`),
	KEY `timestamp` (`created`),
	KEY `feed` (`feed`)
)';

$PDO = Record::getConnection();

// Install snippet, named search-form
$PDO->exec($sql);

exit();
?> 
