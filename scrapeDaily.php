<?php
/*
require('simple_html_dom.php');

// Create a DOM object
$html = new simple_html_dom();

// Load HTML from a URL
$html->load_file('http://www.google.com/');
*/
$badge_id = 0;

// Get the BOTD id from Kongregate page
if (true) {
	$url = 'http://www.kongregate.com/pages/about';
	$html = file_get_contents($url);
	
	// Look for BOTD badge id.
	
	$key = '"botd_badge_id":';
	
	$start = strpos($html, $key);
	if ($start === FALSE) {
		exit("Failed: $key Not found in $url!");
	}
	
	$start += strlen($key);
	$end = strpos($html, ',', $start);
	
	$badge_id = substr($html, $start, $end - $start);
	
	echo "Today's badge of the day is $badge_id<br>";
}

// Format the javascript botd[Y][m][d]=$id;
// Note n == month, j == day without leading zeros

$date = new DateTime(null, new DateTimeZone("America/Los_Angeles"));

$arrayName = 'botd';
$str = '';

if ($date->format('j') == 1) {
	if ($date->format('n') == 1) {
		// First entry in the year, initialize the array
		$str .= $arrayName . $date->format('[Y]') . "=[];\n";
	}
	// First entry in the month, initialize the array
	$str .= $arrayName . $date->format('[Y][n]') . "=[];\n";
}
$str .= $arrayName . $date->format('[Y][n][j]') . "=$badge_id;\n";

echo $str;

$fp = fopen('botd.js','a');
if (!$fp) die("Failed to open botd.js in append mode");

$result = fwrite($fp, $str);
if (!$result) {
	die("Failed to write");
}

echo "Success\n";

?>