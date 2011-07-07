<?php

/**
 * Class for downloading Calvin and Hobber comics from Gocomics.com
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

class calvinandhobbes {
	public $idref = 'calvinandhobbes';
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

		/** Download comics frontpage */
		$c = curl_init();
		curl_setopt($c, CURLOPT_URL, 'http://www.gocomics.com/calvinandhobbes');
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

		/** Grab comics image */
		if(preg_match('/<link rel="image_src" href="([^"]+)"/i', $html, $item)) {
			unset($html);

			if(file_exists('last/'.$this->idref) && file_get_contents('last/'.$this->idref) == $item[1]) {
					echo $this->idref.' is old'."\n";
					return false;
			} else {
				file_put_contents('last/'.$this->idref, $item[1]);
				$c = curl_init();
				curl_setopt($c, CURLOPT_URL, $item[1]);
				curl_setopt($c, CURLOPT_HEADER, false);
				curl_setopt($c, CURLOPT_USERAGENT, 'KindleGenerator/1.0');
				curl_setopt($c, CURLOPT_RETURNTRANSFER, 1);
				curl_setopt($c, CURLOPT_TIMEOUT, 15);
				$img = curl_exec($c);
				curl_close($c);
				unset($c);
			
				if($img !== false) {
					$this->title = 'Calvin and Hobbes';
					file_put_contents($dir.'/'.$this->idref.'.gif', $img);
					$this->manifest = array(array(
						'id' => 1,
						'filename' => $this->idref.'.gif',
						'content-type' => 'image/gif'
					));

					$code = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
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
		}

		return false;
	}

}


?>
