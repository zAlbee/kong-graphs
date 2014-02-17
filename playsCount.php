<?php

/**
 * Quick and dirty json attribute grabber. Not generic.
 * Fine for getting one or two attributes,
 * but not optimal if you want to parse the whole thing!
 */
function getAttr($json, $key) {
	$key = "\"$key\":";
	$start = strpos($json, $key);
	if ($start === false) {
		error_log("Failed: $key Not found!");
		return false;
	}
	$start += strlen($key);
	$end = strpos($json, ',', $start);
	if ($end === false) {
		error_log("Failed: Ending comma for $key not found!");
		return false;
	}
	
	return substr($json, $start, $end - $start);
}

function fetchGameInfo($title, $url) {
	$url2 = "$url/metrics.json";
	$json = file_get_contents($url2);
	
	if ($json === false) {
		$plays = -1;
		$rating = -1;
	} else {
		$plays = getAttr($json, 'gameplays_count');
		if ($plays === false) $plays = -2;
		$rating = getAttr($json, 'rating');
		if ($rating === false) $rating = -2;
	}

	//echo "For game $url, has $plays plays, $rating rating.\n";
	
	return array(
		'title' => addslashes($title),
		'url' => $url,
		'plays' => $plays,
		'rating' => $rating
	);

}

function scrapeData($badges) {
	global $debug;
	global $gameCount;
	global $t_scrape;
	
	$games = array();
	$prelen = strlen('http://www.kongregate.com/games/');
	$doCount = 0;
	$doMax = 5;
	
	foreach ($badges as $key => $badge) {
		$title = $badge['games'][0]['title'];
		$url = $badge['games'][0]['url'];
		$gamekey = substr($url, $prelen);
		
		if (!array_key_exists($gamekey, $games)) {
			if ($debug) echo "$title...";
			$games[$gamekey] = fetchGameInfo($title, $url);
			if ($debug) echo " done.<br>\n";
			//if (++$doCount >= $doMax) break;
		}
	}
	$t_scrape = microtime(true);
	$gameCount = count($games);
	echo "Fetched info for $gameCount games.<br>\n";
	//print_r($games);
	
	// jsonify output
	
	$str = '[';
	$first = true;
	foreach ($games as $key => $game) {
		if ($first) {
			$first = false;
		} else {
			$str .= ',';
		}
		$str .= '{"title":"' . $game['title'] . '",';
		$str .= '"url":"' . $game['url'] . '",';
		$str .= '"plays":' . $game['plays'] . ',';
		$str .= '"rating":' . $game['rating'] . '}';
		//echo "$key => $val<br>\n";
		//print_r($val);
	}
	$str .= ']';
	return $str;
}

// Start here

$debug = FALSE;


$t_start = microtime(true);

$bfile = 'badges.js';
$newbfile = 'badges.weekly.js';

if (!copy($bfile, $newbfile)) {
	echo "Failed to copy $bfile to $newbfile... Continuing\n";
}

$badgeJSON = file_get_contents($bfile);
$p = strpos($badgeJSON, '=');
if ($p !== FALSE) {
	$badgeJSON = substr($badgeJSON, $p+1);
}
$badges = json_decode($badgeJSON, true);
$t_decode = microtime(true);	


$outJSON = scrapeData($badges);
//$t_scrape = microtime(true); // This is done inside the scrapeData function for more accuracy


$outfile = 'badged_games.js';
$fp = fopen($outfile, 'w');
if (!$fp) die("Failed to open $outfile for writing");

$result = fwrite($fp, 'games=' . $outJSON . ";\nlastUpdate=\"" . date(DATE_RSS) . "\";");
if (!$result) {
	die("Failed to write to $outfile");
}
$t_write = microtime(true);


$dt_net = round($t_scrape - $t_decode, 6);
$dt_fs = round($t_write - $t_scrape + $t_decode - $t_start, 6);
$dt_total = round($t_write - $t_start, 6);

echo "Took $dt_total seconds ($dt_net network, $dt_fs filesys + decode).<br>\n";
echo "Rate: " . round($dt_total/$gameCount, 6) . " secs/game or " . round($gameCount/$dt_total, 5) . " games/second.<br>\n";
?>