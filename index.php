<?php
/*	Package:	RSS Reader for Wolf CMS
 *	Author:		Lee J. Bradley (http://www.image-plus.co.uk)
 *	Date:		28/02/2012
*/

if (!defined('IN_CMS')) { exit(); } 

Plugin::setInfos(array(
	'id'          => 'rss_reader',
	'title'       => __('RSS Reader'),
	'description' => __('Read an RSS feed and output using a template snippet'),
	'version'     => '1.0.0',
	'license'     => 'GPL',
	'author'      => 'Image+',
	'website'     => 'http://www.image-plus.co.uk/',
	'require_wolf_version' => '0.7.5'
));

AutoLoader::addFile('RSSReader', CORE_ROOT . '/plugins/rss_reader/RSSReader.php');
