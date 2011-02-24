<?php

/**
 * Class for downloading czech XKCD from Abclinuxu.cz
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 *
 * @author Tomas Kopecny <tomas@kopecny.info>
 * @package daily-comics-to-kindle
 * @license http://www.gnu.org/licenses/gpl-2.0.html GPL version 2
 * @origin https://github.com/Georgo/daily-comics-to-kindle
 */

class xkcdcz {
	public $idref = 'xkcdcz';
	public $title = null;
	public $manifest = array();

	public function generate($dir) {
		if(file_exists($dir.'/'.$this->idref.'.html')) {
			$this->title = file_get_contents($dir.'/'.$this->idref.'.title');
			$this->manifest = array(array(
				'id' => 1,
				'filename' => $this->idref.'.gif',
				'content-type' => 'image/gif'
			));
			return true;
		}

		/** Download RSS */
		$c = curl_init();
		curl_setopt($c, CURLOPT_URL, 'http://www.abclinuxu.cz/auto/abc.rss');
		curl_setopt($c, CURLOPT_HEADER, false);
		curl_setopt($c, CURLOPT_USERAGENT, 'KindleGenerator/1.0');
		curl_setopt($c, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($c, CURLOPT_TIMEOUT, 15);
		$html = curl_exec($c);
		curl_close($c);
		unset($c);
		
		if($html === false) {
			return false;
		}

		/** Parse <items> */
		if(!preg_match('/<items>(.*)<\/items>/is', $html, $items)) {
			return false;
		}	

		$items = $items[0];
		$items = explode('<rdf', $items);
		$xkcdurl = '';

		/** Get last XKCD article url */
		foreach ($items as $item) {
			if(preg_match('#"(http://www\.abclinuxu\.cz/clanky/komiks\-xkcd\-[0-9]+[^"]+)"#i', $item, $tmp)) {
				$xkcdurl = $tmp[1];
				break;
			}
		}
		
		if($xkcdurl == '' || (file_exists('last/'.$this->idref) && file_get_contents('last/'.$this->idref) == $xkcdurl)) {
			return false;
		}
		file_put_contents('last/'.$this->idref, $xkcdurl);

		/** Download article with XKCD comics */
		$c = curl_init();
		curl_setopt($c, CURLOPT_URL, $xkcdurl);
		curl_setopt($c, CURLOPT_HEADER, false);
		curl_setopt($c, CURLOPT_USERAGENT, 'KindleGenerator/1.0');
		curl_setopt($c, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($c, CURLOPT_TIMEOUT, 15);
		$html = curl_exec($c);
		curl_close($c);
		unset($c);

		/** Grab XKCD image */
		if(preg_match('/<img src="(.*\/xkcd\/xkcd\-[0-9]+_czech\.png)"[^>]*title="([^"]+)"/i', $html, $item)) {
			$this->title = 'xkcd (cs)';
			if(preg_match('/<h2[^>]*>([^<]+)<\/h2>/i', $html, $tmp)) {
				$this->title = 'xkcd: '.$tmp[1].' (cs)';
			}
			unset($html);	
			$imgurl = $item[1];

			if(!preg_match('/^http/i', $imgurl)) {
				$imgurl = 'http://www.abclinuxu.cz'.$imgurl;
			}

			$c = curl_init();
			curl_setopt($c, CURLOPT_URL, $imgurl);
			curl_setopt($c, CURLOPT_HEADER, false);
			curl_setopt($c, CURLOPT_USERAGENT, 'KindleGenerator/1.0');
			curl_setopt($c, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($c, CURLOPT_TIMEOUT, 15);
			$img = curl_exec($c);
			curl_close($c);
			unset($c);
		
			if($img !== false) {
				file_put_contents($dir.'/'.$this->idref.'.png', $img);

				/** Convert PNG image to GIF */
				exec('/usr/bin/convert -type grayscale '.$dir.'/'.$this->idref.'.png '.$dir.'/'.$this->idref.'.gif');

				$this->manifest = array(array(
					'id' => 1,
					'filename' => $this->idref.'.gif',
					'content-type' => 'image/gif'
				));

				$code = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title>'.$this->title.'</title>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" /> 
<link rel="stylesheet" href="comics.css" type="text/css" />
<body>
<div>
</head>
<h2>'.$this->title.'</h2>
<p class="centered"><img src="'.$this->idref.'.gif" /></p>
<p class="centered">'.clearUTF($item[2]).'</p>
</div>
<mbp:pagebreak/>
</body>
</html>
';
				file_put_contents($dir.'/'.$this->idref.'.html', $code);
				file_put_contents($dir.'/'.$this->idref.'.title', $this->title);
				return true;
			}
		}

		return false;
	}

}


?>
