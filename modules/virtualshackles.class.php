<?php

/**
 * Class for downloading Virtual Shackles
 * http://www.virtualshackles.com/
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


class virtualshackles {
	public $idref = 'virtualshackles';
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
                
		/** Download today's comix page */
		$c = curl_init();
		curl_setopt($c, CURLOPT_URL, 'http://www.virtualshackles.com/');
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
                if(preg_match('/\<div id=\"comicbox\">[^\>]*<img src=\"(\/img\/[^.]+.jpg)\".+alt=\"([^\"]*)\"[^\/]*\/>/isu', $html, $item)) {
			unset($html);
			
			$imgurl = 'http://www.virtualshackles.com'.$item[1];
			
			if($imgurl == '' || (file_exists('last/'.$this->idref) && file_get_contents('last/'.$this->idref) == $imgurl)) {
				echo $this->idref.' is old'."\n";
				return false;
			}
                        
			$c = curl_init();
			curl_setopt($c, CURLOPT_URL, $imgurl);
			curl_setopt($c, CURLOPT_HEADER, false);
			curl_setopt($c, CURLOPT_USERAGENT, 'KindleGenerator/1.0');
			curl_setopt($c, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($c, CURLOPT_TIMEOUT, 15);
			curl_setopt($c,CURLOPT_FOLLOWLOCATION, true);
			$img = curl_exec($c);
			curl_close($c);
			unset($c);
		
			if($img !== false) {
                                file_put_contents('last/'.$this->idref, $imgurl);
                            
				$this->title = 'Virtual Shackles: "'.$item[2].'"';
				file_put_contents($dir.'/'.$this->idref.'.jpg', $img);
                                
				$this->manifest = array(array(
					'id' => 1,
					'filename' => $this->idref.'.jpg',
					'content-type' => 'image/jpg'
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
<p class="centered"><img src="'.$this->idref.'.jpg" /></p>
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
