<?php

function generate_toc($dir, $toc) {
	$code = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" /> 
<title>Table of contents</title>
<link rel="stylesheet" href="comics.css" type="text/css" />
</head>
<body>
<div id="toc">
<h1>Table of contents</h1>
<ul>
';

	foreach($toc as $idref => $title) {
		$code .= '<li><a href="'.$idref.'.html">'.$title.'</a></li>'."\n";
	}


	$code .= '</ul></div>
</body>
</html>
';
	file_put_contents($dir.'/toc.html', $code);

	$code = '<?xml version="1.0" encoding="UTF-8"?>
<!DOCTYPE ncx PUBLIC "-//NISO//DTD ncx 2005-1//EN"
        "http://www.daisy.org/z3986/2005/ncx-2005-1.dtd">
<ncx xmlns="http://www.daisy.org/z3986/2005/ncx/" version="2005-1" xml:lang="en-US">
<head>
<meta name="dtb:uid" content="georgocomics"/>
<meta name="dtb:depth" content="4"/>
<meta name="dtb:totalPageCount" content="0"/>
<meta name="dtb:maxPageNumber" content="0"/>
</head>
<docTitle><text>Daily comics</text></docTitle>
<docAuthor><text>georgo.org</text></docAuthor>'."\n";

	$code .= '<navMap>'."\n";
	$x = 1;
	$code .= '<navPoint class="periodical">'."\n";
	$code .= "\t".'<navLabel><text>Daily comics</text></navLabel>'."\n";
	$code .= "\t".'<content src="toc.html" />'."\n";
	$code .= "\t".'<navPoint class="section" playOrder="'.$x.'">'."\n";
	$code .= "\t\t".'<navLabel><text>Daily comics '.date('Y/m/d').'</text></navLabel>'."\n";
	$code .= "\t\t".'<content src="toc.html" />'."\n";
	$x++;
	foreach($toc as $idref => $title) {
		$code .= "\t\t".'<navPoint class="article" id="'.$idref.'" playOrder="'.$x.'">'."\n";
		$code .= "\t\t\t".'<navLabel><text>'.htmlspecialchars($title).'</text></navLabel>'."\n";
		$code .= "\t\t\t".'<content src="'.$idref.'.html" />'."\n";
		$code .= "\t\t".'</navPoint>'."\n";
		$x++;
	}
	$code .= "\t\t".'</navPoint>'."\n";
	$code .= "\t".'</navPoint>'."\n";
	$code .= '</navMap>'."\n".'</ncx>'."\n";
	file_put_contents($dir.'/toc.ncx', $code);
	return true;
}

?>
