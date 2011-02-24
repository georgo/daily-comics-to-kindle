#!/usr/bin/php
<?php

/**
 * Daily comics to kindle
 * 
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

/** Go to correct directory */
chdir(dirname(__FILE__));

/** Mandatory required classes */
require 'email.class.php';
require 'toc.php';

/** Locale */
setlocale(LC_ALL, 'en_US.UTF8');

/** Default variables */
$today = date('Y/m/d');
$today_nopath = date('Y-m-d');
$manifest = ''; $spine = ''; $firstpage = ''; $firstpageTitle = ''; $toc = array();

/** Directory to store today's files */
mkdir($today, 0755, true);

/** Comics modules */
/** @BEFORE-FIRST-START: Change you favorite comics modules */
$modules = array(
	'dilbertcom',
	'dilbertcz',
	'garfield',
	'geekandpoke',
	'wulffmorgenthaler',
	'calvinandhobbes',
	'xkcd',
	'xkcdcz',
	'kemel',
	'rencin'
);

/** Load and proccess each module */
foreach($modules as $module) {
	if(file_exists('modules/'.$module.'.class.php')) {
		printf("Processing %s ...\n", $module);
		require 'modules/'.$module.'.class.php';

		$x = new $module();
		$ok = $x->generate($today);
		if($ok) {
			$toc[$x->idref] = $x->title;
			$manifest .= "\t".'<item id="'.$x->idref.'" media-type="application/xhtml+xml" href="'.$x->idref.'.html"/>'."\n";
			if($firstpage == '') {
				$firstpage = $x->idref.'.html';
				$firstpageTitle = $x->title;
			}
			$spine .= "\t".'<itemref idref="'.$x->idref.'"/>'."\n";
			foreach($x->manifest as $_m) {
				$manifest .= "\t".'<item id="'.$x->idref.'-'.$_m['id'].'" media-type="'.$_m['content-type'].'" href="'.$_m['filename'].'"/>'."\n";
			}
		}
	} else {
		printf("Module '%s' not found.\n", $module);
	}
}

/** If no page loaded, quit */
if($firstpage == '') {
	echo "Nothing to do ...\n";
	exit;
}

/** Table of contents */
if(generate_toc($today, $toc)) {
	$manifest .= "\t".'<item id="Table_of_contents" media-type="application/x-dtbncx+xml" href="toc.ncx"/>'."\n";
	$manifest .= "\t".'<item id="htmltoc" media-type="application/xhtml+xml" href="toc.html"/>'."\n";
	$spine = "\t".'<itemref idref="htmltoc"/>'."\n".$spine;
}

$manifest .= "\t".'<item id="My_Cover" media-type="image/gif" href="comics.gif"/>';

/** Main OPF file */
$doc = '<?xml version="1.0" encoding="utf-8"?>
<package xmlns="http://www.idpf.org/2007/opf" version="2.0" unique-identifier="uid">
<metadata>
	<dc-metadata xmlns:dc="http://purl.org/dc/elements/1.1/" xmlns:opf="http://www.idpf.org/2007/opf">
		<dc:title>Daily comics</dc:title>
		<dc:language>en-us</dc:language>
		<dc:identifier id="uid">georgocomics</dc:identifier>
		<meta name="cover" content="My_Cover" />
		<dc:creator>georgo.org</dc:creator>
		<dc:publisher>georgo.org</dc:publisher>
		<dc:subject>News</dc:subject>
		<dc:subject>Comics</dc:subject>
		<dc:date>'.date('Y-m-d').'T'.date('H:i:s').'Z</dc:date>
		<dc:description>Comics pack for actual day.</dc:description>
	</dc-metadata>
	<x-metadata>
		<output encoding="UTF-8" content-type="application/x-mobipocket-subscription-magazine"></output>
	</x-metadata>
</metadata>
<manifest>
'.$manifest.'
</manifest>
<spine toc="Table_of_contents">
'.$spine.'
</spine>
<guide>
	<reference type="toc" title="Table of contents" href="toc.html"></reference>
	<reference type="text" title="'.$firstpageTitle.'" href="'.$firstpage.'"></reference>
</guide>
</package>
';

/** Filenames */
$opf = $today.'/comics-'.date('Y-m-d').'.opf';
$filename_tmp = 'comics-'.date('Y-m-d').'.mobi';
$filename = $today.'/comics-'.date('Y-m-d').'.mobi';

/** Common files */
link('comics.css', $today.'/comics.css');
link('comics.gif', $today.'/comics.gif');

/** Save OPF file */
file_put_contents($opf, $doc);

/** Execute kindle generator */
$ret = null; $log = '';
$lastline = exec('./kindlegen '.$opf.' -c2 -unicode -rebuild -o '.$filename_tmp, $log, $ret);
file_put_contents($today.'/kindlegen.log', implode("\n", $log));

/** Send email to Kindle */
$subject = 'Comics for '.date('Y/m/d');
/** @BEFORE-FIRST-START: Change you kindle email address, don't forget to update email.class.php */
$email = new Email($filename, $subject, 'your-email@free.kindle.com');

?>
