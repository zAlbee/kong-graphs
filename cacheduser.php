<?php

require('cache_funcs.php');

$user = $_GET['u'];
$cbFunc = $_GET['callback'];
//$len = strlen($user);
// Remove characters that could be used to access arbitrary URLs
// For some reason, anything with % including %20 or a space will cause either 400 Bad Request
// Worse, %aa or above will cause 500 Internal Server Error
// This doesn't happen when you try to access the URL in Chrome, so may be specific to PHP.
// And catching '%' doesn't work since that refers to an ascii/unicode
//$user = str_replace(array('.', '/', '?', '&', '=', '%', '#', ' '), '', $user);
$count = 0;
// Remove invalid chars, limit 1. If any invalid found, reject.
$user = strtolower(preg_replace('/[^A-Za-z0-9_]/', '', $user, 1, $count));

if ($cbFunc) {
	$prefix = "$cbFunc(";
	$suffix = ")";
} else {
	$prefix = "";
	$suffix = "";
}
// Reject any string that contained disallowed characters
if ($count > 0 || !$user) {
	echo $prefix.'null'.$suffix;
}
else {
	/* Uncached version
	echo "userBadges = ";
	$url = 'http://www.kongregate.com/accounts/' . $user . '/badges.json';
	readfile($url);
	*/
	$localfile = "cache/$user.js";
	if (!cachedGet("http://www.kongregate.com/accounts/$user/badges.json", 
		$localfile, $prefix, $suffix, $debug)) {
//		echo "userBadges = null;";
	}
}
?>