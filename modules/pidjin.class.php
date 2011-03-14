<?php

/**
 * Class for downloading Fredo & Pid'Jin from http://www.pidjin.net/
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


class pidjin {
	public $idref = 'pidjin';
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
		curl_setopt($c, CURLOPT_URL, 'http://www.pidjin.net/');
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
                
                if(preg_match('/\<h1\>\<a[^\>]+\>([^\<]+)\<\/a\>\<\/h1\>/i', $html, $h1)){
                        $this->title = 'Fredo and Pid\'Jin: "'.$h1[1].'"';
                } else {
                        $this->title = 'Fredo and Pid\'Jin';
                }             

		/** Grab comics images */
		if(preg_match_all('/\<img.* src=\"(http:\/\/www\.pidjin\.net\/wp-content\/uploads\/20\d\d\/\d\d\/\d.+\d.jpg)\"[^\>]* \/\>/i', $html, $items)) {
                        unset($html);
                         
                        if(file_exists('last/'.$this->idref) && file_get_contents('last/'.$this->idref) == $items[1][0]) {
				echo $this->idref.' is old'."\n";
				return false;
                        } else {
                                file_put_contents('last/'.$this->idref, $items[1][0]);
                                $foo = 1;
                                foreach ($items[1] as $item) {
                                        $c = curl_init();
                                        curl_setopt($c, CURLOPT_URL, $item);
                                        curl_setopt($c, CURLOPT_HEADER, false);
                                        curl_setopt($c, CURLOPT_USERAGENT, 'KindleGenerator/1.0');
                                        curl_setopt($c, CURLOPT_RETURNTRANSFER, 1);
                                        curl_setopt($c, CURLOPT_TIMEOUT, 15);
                                        $img = curl_exec($c);
                                        curl_close($c);
                                        unset($c);
			
                                        if($img !== false) {
                                                file_put_contents($dir.'/'.$this->idref.$foo.'.jpg', $img);
                                                $this->manifest = array(array(
                                                        'id' => 1,
                                                        'filename' => $this->idref.'.jpg',
                                                        'content-type' => 'image/jpg'
                                                ));
                                        }
                                        
                                        $foo++;
                                }

					$code = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" /> 
<title>'.$this->title.'</title>
<link rel="stylesheet" href="comics.css" type="text/css" />
</head>
<body>
<div>
<h2>'.$this->title.'</h2>';

for ($i=1; $i < $foo; $i++) {
    $code = $code."\n".'<p class="centered"><img src="'.$this->idref.$i.'.jpg" /></p>';
}

$code = $code."\n".'</div>
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
