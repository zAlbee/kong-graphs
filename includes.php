<?php
/*
 * strFromGPC (string)
 */

/**
 * Returns the literal version of a string that came via a GET/POST/COOKIE operation
 */
function strFromGPC($str) {
	if (get_magic_quotes_gpc()) {
		return stripslashes($str);
	}
	else return $str;
}

/**
 * Converts string to be printable in HTML, without any HTML meaning.
 * converts ", &, <, >
 */
function strToHTML($str) {
	return htmlspecialchars($str);
}

/**
 * Converts string to be usable in a URL as a query variable or value.
 * e.g. http://www.url.com?var1=value1&var2=value2
 */
function strToQueryVar($str) {
	return urlencode($str);
}

/**
 * Converts string to be usable in a JavaScript string literal 
 * (i.e. something enclosed in quotes)
 */
function strToJS($str) {
	return addslashes($str);
}
?>