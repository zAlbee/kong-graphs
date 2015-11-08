<?php

/**
 * Fetches all HTTP headers from the current request.
 * 
 * To be used when getallheaders / apache_request_headers() is not available.
 * acidfilez at gmail dot com 07-Jun-2011 09:16
 * http://us.php.net/manual/en/function.getallheaders.php
 * 	
 * @return headers
 */
function emu_getallheaders() {
	if (function_exists('getallheaders')) {
		return getallheaders();
	}
	foreach ($_SERVER as $name => $value)
	{
		if (substr($name, 0, 5) == 'HTTP_')
		{
			$name = str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($name, 5)))));
			$headers[$name] = $value;
		} else if ($name == 'CONTENT_TYPE') {
			$headers['Content-Type'] = $value;
		} else if ($name == 'CONTENT_LENGTH') {
			$headers['Content-Length'] = $value;
		}
	}
	return $headers;
}

/**
 * Reads the saved headers for the cached copy, finds the modified time or the etag.
 * Returns the header to use for revalidating the cache.
 * @param $headerfile
 */
function getCacheTag($headerfile, $isResponse) {
	if (!file_exists($headerfile)) return null;

	$tag = null;
	$fph = fopen($headerfile, "r");
	while (!feof($fph) && $line = trim(fgets($fph, 1024)))
	{
		if ($line == "\r\n") break;
		list($key, $val) = explode(': ', $line, 2);
		if (strtolower($key) == "last-modified") {
			if ($isResponse) $tag = $line; // 'Last-Modified'
			else $tag = 'If-Modified-Since: ' . $val;
			break;
		}
		if (strtolower($key) == "etag") {
			if ($isResponse) $tag = $line; // 'Etag'
			else $tag = 'If-None-Match: ' .  $val;
			break;
		}
	}
	fclose($fph);
	return $tag;
}

/**
 * updateCache
 * Downloads a file from URL and caches it to the given file name. If the file
 * already is in cache and the remote version has not been modified more
 * recently, then the file won't be re-downloaded.
 *
 * This function currently validates the cache using one of two methods:
 *  1. Last-Modified and If-Modified-Since headers, or
 *  2. ETag and If-None-Match
 * Does not support anything more complex than 200 and 304 codes.
 *
 * @param $url		URL to fetch
 * @param $localfile	file name to use as the cache file (will be saved here)
 * @param $debug	if set, will still update the cache, but also print extra debugs to the user agent
 */
function updateCache($url, $localfile, $cacheControl, $debug=FALSE) {
	if ($debug) {
		echo "cacheControl = $cacheControl<br>\n";
		echo "$localfile last modified on cache: ";
		ftime($localfile);
	}

	$headerfile = $localfile . '.hdr';
	$maxAge = 60 * 30; // 30 minutes
	if (!empty($cacheControl)) {
		$parts = explode('=', $cacheControl);
		if (count($parts) == 2 && $parts[0] == 'max-age') {
			$maxAge = $parts[1];
		}
	}

	// If the file is in cache, check the saved headers.
	// To validate cache using last-modified time, must use time given by last response;
	// Cannot use our local file system time even if it is later.
	// At least for Kongregate (Server: nginx/0.7.67), it will return 200 OK without any content, instead of 304 Not Modified
	if (file_exists($localfile)) {
		$diff = time() - filemtime($headerfile);
		if ($diff < $maxAge && !$_GET['force']) {
			if ($debug) echo "Not fetching, checked $diff secs ago < $maxAge max-age. <br>\n";
			return 0;
		}
		else {
			if ($debug) echo "Last checked $diff secs ago >= $maxAge max-age. Fetching... <br>\n";
		}
		$tag = getCacheTag($headerfile, false);
	}

	$ch = curl_init($url);
	// fopen($fp, 'w') truncates the file to 0 length, so need to use temp file
	$rand = mt_rand();
	$tmpfile = 'temp/tmp' . $rand;
	$tmpheaderfile = 'temp/tmp' . $rand . '.hdr';
	$fp = fopen($tmpfile, "w");
	$fph = fopen($tmpheaderfile, "w");

	curl_setopt($ch, CURLOPT_FILE, $fp);
	curl_setopt($ch, CURLOPT_HEADER, false); // Put Response Header in output file?
	curl_setopt($ch, CURLOPT_WRITEHEADER, $fph); // Put Response Header in file $fph
	curl_setopt($ch, CURLINFO_HEADER_OUT, true); // Set true to allow curl_getinfo to give the *Request* header
	curl_setopt($ch, CURLOPT_USERAGENT, "PHP/5.2");

	if ($tag) {
		curl_setopt($ch, CURLOPT_HTTPHEADER, array($tag));
	}
	curl_exec($ch);

	if ($debug) {
		echo "Sending request string: <pre>". curl_getinfo($ch, CURLINFO_HEADER_OUT) . "</pre>\n";
		echo "Remote file time: ". curl_getinfo($ch, CURLINFO_FILETIME) . "<br>\n";
		echo "Name lookup time: ". curl_getinfo($ch, CURLINFO_NAMELOOKUP_TIME) ."<br>\n";
		echo "Connect time: ". curl_getinfo($ch, CURLINFO_CONNECT_TIME) ."<br>\n";
		echo "Pre-transfer time: ". curl_getinfo($ch, CURLINFO_PRETRANSFER_TIME) ."<br>\n";
		echo "Start transfer time: ". curl_getinfo($ch, CURLINFO_STARTTRANSFER_TIME) ."<br>\n";
		echo "Total time: ". curl_getinfo($ch, CURLINFO_TOTAL_TIME) . "<br>\n";
		echo "Upload speed: ". curl_getinfo($ch, CURLINFO_SPEED_UPLOAD) ."<br>\n";
		echo "Download speed: ". curl_getinfo($ch, CURLINFO_SPEED_DOWNLOAD) ."<br>\n";
		echo "<br>\n";
	}

	$code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
	curl_close($ch);
	fclose($fp);
	fclose($fph);

	if ($code == 200) { //OK
		if ($debug) echo "Got 200 OK";
		rename($tmpfile, $localfile);
		rename($tmpheaderfile, $headerfile);
	} else if ($code == 304) { //NOT MODIFIED
		if ($debug) echo "Got 304 Not Modified";
		unlink($tmpfile);
		rename($tmpheaderfile, $headerfile);
	} else {
		if ($code == 400) { //Kong sends 400 Bad Request if user doesn't exist
			if ($debug) echo "Got $code - user doesn't exist?";
		} else {
			if ($debug) echo "Got $code - this response code is not expected!";
			error_log("Got $code response for $url");
		}
		unlink($tmpfile);
		unlink($tmpheaderfile);
		return $code;
	}

	if ($debug) echo "\n<br>Response Headers saved: <pre>" . file_get_contents($headerfile) . "</pre>\n";

	return $code;
}

/**
 * Gets a remote file and outputs it, cache-enabled.
 *
 * Each call will first validate our cached copy with the remote server,
 * updating it if stale. Then either the file will be returned to the user agent
 * with cache-enabled headers, or simply a 304 NOT MODIFIED header instructing
 * the user agent to use its own cache.
 *
 * This function is to be called when a HTTP request comes from the user agent,
 * as it will both analyze incoming and output outgoing headers.
 */
function cachedGet($url, $localfile, $prefix='', $suffix='', $debug=FALSE) {
	$userHeaders = array_change_key_case(emu_getallheaders(), CASE_LOWER);

	if ($debug) {
		echo "Checking incoming user agent headers<br>\n<pre>";
	}
	foreach ($userHeaders as $key => $value) {
		if ($debug) {
			echo "$key: $value\n";
		}
	}

	$cacheControl = $userHeaders['cache-control'];

	// TODO: Make the update optional.
	// Update our cache.
	$code = updateCache($url, $localfile, $cacheControl, $debug);
	// Get the last-modified, or etag of our copy.
	if (file_exists($localfile)) {
		$headerfile = $localfile . '.hdr';
		$tag = getCacheTag($headerfile, true);
		list($tagname, $tagvalue) = explode(': ', $tag, 2);
		$isEtag = strcasecmp($tagname, 'Etag') == 0;
		if ($debug) echo "isEtag $isEtag<br>\n";
	} else {
		// Error case. Could not get from server and not found in cache either.
		if ($debug) echo "Fail, $localfile not exists";
		else header("HTTP/1.1 404 Not Found");
		return false;
	}

	// Check user's last-modified time or etag

	if (!$isEtag) {
		$userValue = $userHeaders['if-modified-since'];
	}
	else {
		$userValue = $userHeaders['if-none-match'];
	}
	if (!empty($userValue)) {
		if ($userValue == $tagvalue || $userValue == substr($tagvalue,0,-1).'-gzip"') {
			if ($debug) echo "</pre>\n [$userValue] == [$tagvalue]; would return 304 not modified in real scenario";
			else header("HTTP/1.1 304 Not Modified");
			// No need to send content when not modified
			//header($tag);
			return true;
		} else {
			if ($debug) {
				echo " [$userValue] != [$tagvalue]; client had a cached copy, but it was out of date";
			}
		}
	}

	if ($debug) {
		echo "</pre>\n would return 200 OK in real scenario";
		echo " with header [$tag]\n";
	} else {
		header("HTTP/1.1 200 OK");
		// Send the updated modification time/Etag
		header($tag);
	}

	// Send the file
	if (file_exists($localfile)) {
		echo $prefix;
		readfile($localfile);
		echo $suffix;
	}

	return true;
}

function ftime($localfile) {
	if (file_exists($localfile)) {
		echo date(DATE_RFC822, filemtime($localfile));
	} else {
		echo "File not exists.";
	}
	echo "<br>\n";
}

?>