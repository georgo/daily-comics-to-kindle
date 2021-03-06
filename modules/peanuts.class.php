<?php

/**
 * Class for downloading Peanuts comics
 * http://www.gocomics.com/peanuts/
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 *
 * @author Frantisek Tuma <tumaf@seznam.cz>, @Feainne
 * @package daily-comics-to-kindle
 * @license http://www.gnu.org/licenses/gpl-2.0.html GPL version 2
 * @origin https://github.com/Georgo/daily-comics-to-kindle
 */


class peanuts {
	public $idref = 'peanuts';
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
                
		/** Download today's strip page */
		$c = curl_init();
		curl_setopt($c, CURLOPT_URL, 'http://www.gocomics.com/peanuts/'.date('Y/m/d'));
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
		if(preg_match('/\<p class=\"feature_item\"\>\<a href = \'(http:\/\/imgsrv.gocomics.com\/dim\/\?fh=[^\']+)\'\>/i', $html, $item)) {
			unset($html);
                        
			$c = curl_init();
			curl_setopt($c, CURLOPT_URL, $item[1]);
			curl_setopt($c, CURLOPT_HEADER, false);
			curl_setopt($c, CURLOPT_USERAGENT, 'KindleGenerator/1.0');
			curl_setopt($c, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($c, CURLOPT_TIMEOUT, 115);
			$img = curl_exec($c);
			curl_close($c);
			unset($c);
		
			if($img !== false) {
				$this->title = 'Peanuts';
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
