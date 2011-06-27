<?php

/**
 * Class for downloading Ctyrlistky comics
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


class ctyrlistky {
	public $idref = 'ctyrlistky';
	public $title = null;
	public $manifest = array();

	private $months = array(
		 1 => 'ledna',
		 2 => 'unora',
		 3 => 'brezna',
		 4 => 'dubna',
		 5 => 'kvetna',
		 6 => 'cervna',
		 7 => 'cervence',
		 8 => 'srpna',
		 9 => 'zari',
		10 => 'rijna',
		11 => 'listopadu',
		12 => 'prosince'
	);

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


		/** Download today's strip page */
		$c = curl_init();
		curl_setopt($c, CURLOPT_URL, 'http://ctyrlistky.cz/'.date('j-').$this->months[intval(date('n'))].date('-Y/'));
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

		/** Grap comics image */
		if(preg_match('/<img alt="([^"]*)" src="(http:\/\/ctyrlistky.cz\/4xmale\/[a-z0-9_\-\.]+\.jpg)"/i', $html, $item)) {
			unset($html);

			$c = curl_init();
			curl_setopt($c, CURLOPT_URL, $item[2]);
			curl_setopt($c, CURLOPT_HEADER, false);
			curl_setopt($c, CURLOPT_USERAGENT, 'KindleGenerator/1.0');
			curl_setopt($c, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($c, CURLOPT_TIMEOUT, 15);
			$img = curl_exec($c);
			curl_close($c);
			unset($c);
		
			if($img !== false) {
				$this->title = $item[1];
				file_put_contents($dir.'/'.$this->idref.'.gif', $img);
				$this->manifest = array(array(
					'id' => 1,
					'filename' => $this->idref.'.gif',
					'content-type' => 'image/gif'
				));

				$code = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" /> 
<title>'.$this->title.'</title>
<link rel="stylesheet" href="comics.css" type="text/css" />
</head>
<body>
<div>
<h2>'.$this->title.'</h2>
<p class="centered"><img src="'.$this->idref.'.gif" /></p>
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
