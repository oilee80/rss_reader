<?php
/* Security measure */


if (!defined('IN_CMS')) { exit(); }


/**
 * The property class represents a property on a page.
 */
class RSSReader extends Record {
	private $cache_time = 3600;		// This should be 1 hour
	private $wolf = false;

	public function __construct($wolf = false) {
		$this->wolf = $wolf;
	}

	const TABLE_NAME = 'rss_reader';

	public function setFormData($data) {
		return parent::setFromData($data);
	}

/*	Will Retrieve a feed and store it in the database
*/	public static function get_feed($feed = false) {
		if(!$feed)
			return false;

		$feed_data = false;
		$dom = new DOMDocument();
		if($dom->load($feed)) {
			$feed_data = $dom->saveXML();

			$vars = array('feed' => $feed, 'feed_data' => $feed_data);
			$feed = New RSSReader();
			$feed->setFormData($vars);

			$stat = $feed->save();
		}
		return $feed_data;
	}

/*	Will get the feed from the database if it is within the cache_time limit else will send to get_feed for new retrieval
*/	public function prepare_feed($feed = false) {
		$feed = $feed ? $feed : $this->feed;

		$feedObj = RSSReader::findOneFrom('RSSReader','(UNIX_TIMESTAMP(created) > (UNIX_TIMESTAMP(NOW())-'.$this->cache_time.')) AND (`feed` = "'.$feed.'")');
		if(!is_object($feedObj)) {
			$feed_data = $this->get_feed($feed);
		} else {
			$feed_data = $feedObj->feed_data;
		}
		return $feed_data;
	}
    
/*	Will process feed and return an array of array values for each item in the rss feed
*/	public function process_feed($feed = '') {
		if(!is_object($this->wolf)) {
			return;
		}

		$feed_data = $this->prepare_feed($feed);
		if(!$feed_data)
			return false;
		$dom = new DOMDocument;
		$dom->loadXML($feed_data);
		if($dom) {
			$xPath = new DOMXPath($dom);
			$news_items = $xPath->query('//item');
			$feed_data = array();
			$items = $xPath->query('//title');
			$feed_data['meta']['title'] = ($items->length == 0) ? null : $items->item(0)->textContent;
			$items = $xPath->query('//link');
			$feed_data['meta']['link'] = ($items->length == 0) ? null : $items->item(0)->textContent;
			$items = $xPath->query('//description');
			$feed_data['meta']['description'] = ($items->length == 0) ? null : $items->item(0)->textContent;
			$items = $xPath->query('//language');
			$feed_data['meta']['language'] = ($items->length == 0) ? null : $items->item(0)->textContent;
			$items = $xPath->query('//lastBuildDate');
			$feed_data['meta']['lastBuildDate'] = ($items->length == 0) ? null : $items->item(0)->textContent;
			$items = $xPath->query('//copyright');
			$feed_data['meta']['copyright'] = ($items->length == 0) ? null : $items->item(0)->textContent;
			$items = $xPath->query('//link');
			$feed_data['meta']['atom:link'] = ($items->length == 0) ? null : $items->item(0)->attributes->getNamedItem('href')->textContent;

			$items = $xPath->query('//image/url');
			$feed_data['meta']['image']['url'] = ($items->length == 0) ? null : $items->item(0)->textContent;
			$items = $xPath->query('//image/title');
			$feed_data['meta']['image']['title'] = ($items->length == 0) ? null : $items->item(0)->textContent;
			$items = $xPath->query('//image/link');
			$feed_data['meta']['image']['link'] = ($items->length == 0) ? null : $items->item(0)->textContent;
			$items = $xPath->query('//image/width');
			$feed_data['meta']['image']['width'] = ($items->length == 0) ? null : $items->item(0)->textContent;
			$items = $xPath->query('//image/height');
			$feed_data['meta']['image']['height'] = ($items->length == 0) ? null : $items->item(0)->textContent;

//			$feed_data['meta']['title'] = $xPath->query('//title')->item(0)->textContent;
			foreach($news_items As $i => $item) {
				$feed_data['feeds'][$i]=array();
				$feed_data['feeds'][$i]['title'] = $xPath->query($item->getNodePath().'/title')->item(0)->textContent;
				$feed_data['feeds'][$i]['description'] = $xPath->query($item->getNodePath().'/description')->item(0)->textContent;
				$feed_data['feeds'][$i]['pubDate'] = $xPath->query($item->getNodePath().'/pubDate')->item(0)->textContent;
				$feed_data['feeds'][$i]['link'] = $xPath->query($item->getNodePath().'/link')->item(0)->textContent;
				$thumbnails = $xPath->query($item->getNodePath().'/media:thumbnail');
				foreach($thumbnails As $k => $thumbnail) {
					$feed_data['feeds'][$i]['media']['thumbnail_'.$k] = $thumbnail->attributes->getNamedItem('url')->textContent;
				}
			}
			return $feed_data;
		} else {
// Failed to load feed_data
			return false;
		}
	}
}