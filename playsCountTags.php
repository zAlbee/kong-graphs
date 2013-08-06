<?php

$debug = FALSE;

$time_total = 0;
$time_net = 0;
$time_file = 0;

function timed_net_get($url) {
	global $time_net;
	$t1 = microtime(true);
	$ret = file_get_contents($url);
	$time_net += microtime(true) - $t1;
	return $ret;
}
function timed_file_get($file) {
	global $time_file;
	$t1 = microtime(true);
	$ret = file_get_contents($file);
	$time_file += microtime(true) - $t1;
	return $ret;
}

/**
 * Quick and dirty json attribute grabber. Not generic.
 * Fine for getting one or two attributes,
 * but not optimal if you want to parse the whole thing!
 */
function getAttr($json, $key) {
	$key = "\"$key\":";
	$start = strpos($json, $key);
	if ($start === FALSE) {
		error_log("Failed: $key Not found!");
		return '';
	}
	$start += strlen($key);
	$end = strpos($json, ',', $start);
	
	return substr($json, $start, $end - $start);
}

function fetchGameInfo($title, $url) {
	$url2 = "$url/metrics.json";
	$json = timed_net_get($url2);

	$obj = json_decode($json);
	
	//$plays = getAttr($json, 'gameplays_count');
	$plays = $obj->gameplays_count;
	//$rating = getAttr($json, 'rating');
	$rating = $obj->rating;

	//echo "For game $url, has $plays plays, $rating rating.\n";
	
	return array(
		'title' => $title, //addslashes($title),
		'url' => $url,
		'plays' => $plays,
		'rating' => $rating,
		'tags' => fetchGameTags($url),
	);

}

$allTags = array();

function fetchGameTags($url) {
	global $allTags;
	$tags = array();
	$totalScore = 0;
	$maxScore = 0;

	// Tag-Parsing code provided by Jade (j64e on Kongregate)
	// Thanks Jade!
	$currenturl = $url . '/tags'; //?show_hidden=true';

	$tagpage = timed_net_get($currenturl);

	//parsing tag page
	$tagoff = 0;
	while (true) {
		$pos = strpos($tagpage, '<em id="score', $tagoff);
		if ($pos == 0) { break; }
		$pos2 = strpos($tagpage, '>', $pos);
		$pos3 = strpos($tagpage, '<', $pos2);
		$tagscore = substr($tagpage, $pos2 + 2, $pos3 - 3 - $pos2);
		$pos4 = strpos($tagpage, '<td class="tag plm">', $pos3);
		$pos5 = strpos($tagpage, '<', $pos4 + 1);
		$tagname = trim(substr($tagpage, $pos4 + 20, $pos5 - $pos4 - 21));
		$pos6 = strpos($tagpage, '"', $pos5);
		$pos7 = strpos($tagpage, '"', $pos6 + 1);
		$tagurl = 'http://kongregate.com' . substr($tagpage, $pos6 + 1, $pos7 - 1 - $pos6);
		$tagoff = $pos7;
		//echo $tagscore, ' ', $tagname, ' ', $tagurl, '<br/>';

		// End parsing

		// Add tag to game list
		if (isset($tags[$tagname])) {
		    // There's a bug where the same tag can appear more than once...
		    // See Zombie tag in http://www.kongregate.com/games/ConArtists/the-last-stand/tags
		    $tags[$tagname]['score'] += $tagscore;
		} else {
		    $tags[$tagname] = array(
			'score' => $tagscore,
			//'name' => $tagname,
		    );
		}

		// Add tag to global list
		if (isset($allTags[$tagname])) {
		    $allTags[$tagname]['score'] += $tagscore;
		} else {
		    $allTags[$tagname] = array(
			'score' => $tagscore,
			'url' => $tagurl
		    );
		}
		$totalScore += $tagscore;
		$maxScore = max($tagscore, $maxScore);
	}

	$tags['total_score'] = $totalScore;
	$tags['max_score'] = $maxScore;
	return $tags;
}

function scrapeData($badges) {
	global $debug;
	global $gameCount;
	//global $t_scrape;
	
	$games = array();
	$prelen = strlen('http://www.kongregate.com/games/');
	$doCount = 0;
	$doMax = 20;
	
	foreach ($badges as $key => $badge) {
		$title = $badge['games'][0]['title'];
		$url = $badge['games'][0]['url'];
		$gamekey = substr($url, $prelen);
		
		if (!array_key_exists($gamekey, $games)) {
			if ($debug) echo "$title...";
			$games[$gamekey] = fetchGameInfo($title, $url);
			if ($debug) echo " done.<br>\n";
			if ($debug) {
				if (++$doCount >= $doMax) break;
			}
		}
	}
	//$t_scrape = microtime(true);
	$gameCount = count($games);
	echo "Fetched info for $gameCount games.<br>\n";
	//print_r($games);
	
	// jsonify output
	// Use array_values to remove array keys
	return json_encode(array_values($games));

	// TODO: why here? remove this
	/*
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
	*/
}

// Start here

  $t_start = microtime(true);

$bfile = 'badges.js';
$newbfile = 'badges.weekly.js';

if (!$debug) {
	if (!copy($bfile, $newbfile)) {
		echo "Failed to copy $bfile to $newbfile... Continuing\n";
	}
}
  $time_file += microtime(true) - $t_start;

$badgeJSON = timed_file_get($bfile);
$p = strpos($badgeJSON, '=');
if ($p !== FALSE) {
	$badgeJSON = substr($badgeJSON, $p+1);
}
$badges = json_decode($badgeJSON, true);

  $t_start_games = microtime(true);
$outJSON = scrapeData($badges);
  $t_end_games = microtime(true);

ksort($allTags);
$tagsJSON = json_encode($allTags);


  $t_start_write = microtime(true);

$outfile = 'badged_gamesTAGS.js';
$fp = fopen($outfile, 'w');
if (!$fp) die("Failed to open $outfile for writing");

$result = fwrite($fp, 'games=' . $outJSON . ";\nlastUpdate=\"" . date(DATE_RSS) . "\";\nallTags=" . $tagsJSON);
if (!$result) {
	die("Failed to write to $outfile");
}
  $t_end = microtime(true);
  $time_file += $t_end - $t_start_write;


//$dt_net = round($t_scrape - $t_decode, 6);
//$dt_fs = round($t_write - $t_scrape + $t_decode - $t_start, 6);
$dt_total = round($t_end - $t_start, 6);

$time_games = $t_end_games - $t_start_games;
$time_games_php = $time_games - $time_net;
$time_fixed = $dt_total - $time_games;
$time_fixed_php = $time_fixed - $time_file;

echo "Took $dt_total seconds ($time_games for $gameCount games = $time_net network + $time_games_php php) + ($time_file file i/o, $time_fixed_php php fixed).<br>\n";
echo "Rate: " . round($time_games/$gameCount, 6) . " secs/game or " . round($gameCount/$time_games, 5) . " games/second.<br>\n";

//echo $outJSON;
//echo "<br>\n<br>\n";
//echo $tagsJSON;
?>
