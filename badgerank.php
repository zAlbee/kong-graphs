<!DOCTYPE html>
<html>
<?php
	require('includes.php');
	$user = preg_replace('/[^A-Za-z0-9_]/', '', strFromGPC($_GET['u']));
?>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=ISO-8859-1">
<title>Difficulty Analysis</title>
<!-- <div>Honey pot trap for malware</div> --><style>
dt {font-style: italic;}

#content {
	margin-top: 0;
	vertical-align: top;
}
/*
 * Fix silly list item overlapping with floated sidbar
 * http://stackoverflow.com/questions/710158/why-do-my-list-item-bullets-overlap-floating-elements
 */
#content ol {
	overflow: hidden;
	zoom: 1; /* IE6 */
}

#sidebar {
	width: 180px;
	background: #dddddd;
	z-index: 9000;
	font-family: "Helvetica", "Arial", sans-serif;
	font-size: 10pt;
	padding: 5px;
	vertical-align: top;
	float: left;
	margin-right: 1ex;
}

#sidebar h3 {
	text-align: left;
}

#sidebar ul {
	list-style: disc;
	margin-left: 1.5em;
	padding-left: 0em;
	/*text-indent: -1em;*/
}

#namesForm {
	text-align: center;
	margin: 1em 0;
}

label, .formlabel {
	font-family: 'Arial', 'Helvetica', sans-serif;
	font-size: 9pt;
	color: #990000;
}
.emph {
	color: #000099;
}
.user1 {
	color: #006600;
}
.user2 {
	color: #000099;
}
.emph_pct {
	color: #007777;
}
.info {
	font-family: 'Arial', 'Helvetica', sans-serif;
	font-size: 75%;
	color: #666666;
}
#buttonDiv {
	margin-bottom: 1em;
}
.small {
	font-size: 80%;
}
</style>
<script type="text/javascript" src="dom.js"></script>
<script type="text/javascript">
var badges, userBadges, userBadges2;
var userName = "<?php echo strToJS(strToHTML($user));?>";
var kong = "Kongregate";
var hideOwn = false;
// Callback for Kong JSON
cbUser = function (list) {
	userBadges = list;
};
//Offline Testing
if (userName.lastIndexOf("<" + "?php", 0) === 0) {
	println('<scr'+'ipt type="text/javascript" src="zalbee.json"></scr'+'ipt>');
	userName = "zAlbee";
}
</script>
<?php
if (isset($_GET['hideOwn'])) {
	echo '<script type="text/javascript">';
	echo 'hideOwn = true;';
	echo '</script>';
}
?>
<?php if ($user) {
	echo '<script type="text/javascript" src="http://www.kongregate.com/accounts/' . strToHTML(strtolower($user)) . '/badges.json?callback=cbUser"></script>' . "\n";
}
?>
<script type="text/javascript">
// Callback for Kong JSON
cbUser = function (list) {
	userBadges2 = list;
};
</script>
<?php if ($user2) {
	echo '<script type="text/javascript" src="http://www.kongregate.com/accounts/' . strToHTML(strtolower($user2)) . '/badges.json?callback=cbUser"></script>' . "\n";
}
?>
<script type="text/javascript" src="badges.weekly.js"></script>
<script type="text/javascript" src="badged_games.js"></script>
<!-- <script type="text/javascript" src="getElementsByClassName-1.0.1.js"></script> -->
<script type="text/javascript">
// badges indexed by id
var badgesTable = [];
for (var i in badges) {
	var id = badges[i].id;
	badgesTable[id] = badges[i];
}

/**
 * The following global variable is used by the sorting functions.
 */
var table = {'rank': 1, 'column': 'pe', 'reverse': false, 'hideOwned': false};

function resetRank() {
	table.rank = 1;
}

function badgeToHTMLPic(badge) {
	var s = '<div class="badge">%DATE% <img src="%IMGSRC%" align="left" hspace="5"> <b>%NAME% Badge</b> <i>(%DIFF%)</i><br>'
		+ '<a href="%GAMEURL%" target="_blank">%GAME%</a> - %DESC%</div>\n';
	return (s.replace('%DATE%', '')
		 .replace('%IMGSRC%', badge.icon_url)
		 .replace('%NAME%', badge.name)
		 .replace('%DIFF%', badge.difficulty)
		 .replace('%GAMEURL%', badge.games[0].url)
		 .replace('%GAME%', badge.games[0].title)
		 .replace('%DESC%', badge.description)
		 );
}

function badgeToHTMLRow(badge, column, desc) {
	table.column = column;
	table.reverse = desc;
	var s = '<tr valign="top" id="row%ID%" style="%STYLE%"><td>%RANK%</td><td>%COUNT%</td><td>%PLAYS%</td><td>%RATING%</td><td>%DIFF%</td>'
		+ '<td>%PE%</td><td>%PER%</td><td>%PPP%</td><td>%PPPR%</td><td>%USERHAS%</td><td><a href="%GAMEURL%" title="%DESC%" target="_blank">%GAME%</a> - <b>%BADGE%</b></td>'
		+ '</tr>';
	var marker = desc ? 
			'<sup style="font-family:sans-serif; color:#00cc00; font-weight: bold">v</sup>':
			'<sup style="font-family:sans-serif; color:red; font-weight: bold">^</sup>';
	
	if (badge) {
		var game = badge.game;
		return (s.replace(/%RANK%/g, table.rank++)
			 .replace('%ID%', badge.id)
			 .replace('%DATE%', '')
			 .replace('%IMGSRC%', badge.icon_url)
			 .replace('%BADGE%', badge.name + ' Badge')
			 .replace('%DIFF%', badge.difficulty)
			 .replace('%GAMEURL%', game.url)
			 .replace('%GAME%', game.title)
			 .replace('%DESC%', strToHTML(badge.description))
			 .replace('%PE%', roundTo(badge.score * 100, 2) + "%")
			 .replace('%PPP%', roundTo(badge.score * badge.points, 2) + " ppp")
			 .replace('%PER%', roundTo(badge.scoreR * 100, 2) + "")
			 .replace('%PPPR%', roundTo(badge.scoreR * badge.points, 2) + "")
			 .replace('%COUNT%', badge.users_count)
			 .replace('%PLAYS%', game.plays)
			 .replace('%RATING%', game.rating)
			 .replace('%USERHAS%', userHasBadge[badge.id] ? 'Y':'')
			 .replace('%STYLE%', table.hideOwned && userHasBadge[badge.id] ? 'display: none;':'')
			 );
	}
	else {
		return (s.replace(/%RANK%/g, 'Rank')
			 .replace('%ID%', 'Header')
			 .replace('%DATE%', 'Date')
			 .replace('%IMGSRC%', '')
			 .replace('%BADGE%', 'Badge')
			 .replace('%DIFF%', '<a href="javascript:sortD(' + (column=='d' ? !desc:desc) 
					 + ');">Difficulty</a>'          + (column=='d'?marker:''))
			 .replace('%GAMEURL%', 'javascript:sortG(' + (column=='g' ? !desc:desc) 
					 + ');')
			 .replace('%GAME%', 'Game' + (column=='g'?marker:''))
			 .replace('%DESC%', 'Badge Description')
			 .replace('%PE%', '<a href="javascript:sortPE(' + (column=='pe' ? !desc:desc) 
					 + ');">Percent earned</a>'     + (column=='pe'?marker:'') + '')
			 .replace('%PPP%', '<a href="javascript:sortPPP(' + (column=='ppp' ? !desc:desc) 
					 + ');">Points Per Play</a>'      + (column=='ppp'?marker:''))
			 .replace('%PER%', '<a href="javascript:sortPER(' + (column=='per' ? !desc:desc)
					 + ');">PE*Rating</a>'            + (column=='per'?marker:''))
			 .replace('%PPPR%', '<a href="javascript:sortPPPR(' + (column=='pppr' ? !desc:desc)
					 + ');">PPP*Rating</a>'             + (column=='pppr'?marker:''))
			 .replace('%COUNT%', '<a href="javascript:sortE(' + (column=='e' ? !desc:desc) 
					 + ');">Users Earned</a>'         + (column=='e'?marker:'') + '')
			 .replace('%PLAYS%', '<a href="javascript:sortP(' + (column=='p' ? !desc:desc) 
					 + ');">Plays</a>'                + (column=='p'?marker:'') + '')
			 .replace('%RATING%', '<a href="javascript:sortR(' + (column=='r' ? !desc:desc) 
					 + ');">Rating</a>'                + (column=='r'?marker:'') + '')
			 .replace('%USERHAS%', 'Own')
			 );
	}
}


/**
 * Sort by Percentage Earned
 */
function sortPE(reverse) {
	resetRank();
	badges.sort(function(a,b) {
		var k1 = a.score;
		var k2 = b.score;
		return (k1 > k2) ? 1 : ( (k2 > k1) ? -1 : 0 );
	});
	
	if (reverse) badges.reverse();

	var s = badgeToHTMLRow(null, "pe", reverse);
	for (var i in badges) {
		s += badgeToHTMLRow(badges[i]);
	}
	getEl("badgeTable").innerHTML = "<table>"+s+"</table>";
}

/**
 * Sort by Points Per Play
 */
function sortPPP(reverse) {
	resetRank();
	badges.sort(function(a,b) {
		var k1 = a.score * a.points;
		var k2 = b.score * b.points;
		return (k1 > k2) ? 1 : ( (k2 > k1) ? -1 : 0 );
	});
	
	if (reverse) badges.reverse();

	var s = badgeToHTMLRow(null, "ppp", reverse);
	for (var i in badges) {
		s += badgeToHTMLRow(badges[i]);
	}
	getEl("badgeTable").innerHTML = "<table>"+s+"</table>";
}

/**
 * Sort by Percentage Earned * Rating
 */
function sortPER(reverse) {
	resetRank();
	badges.sort(function(a,b) {
		var k1 = a.scoreR;
		var k2 = b.scoreR;
		return (k1 > k2) ? 1 : ( (k2 > k1) ? -1 : 0 );
	});
	
	if (reverse) badges.reverse();

	var s = badgeToHTMLRow(null, "per", reverse);
	for (var i in badges) {
		s += badgeToHTMLRow(badges[i]);
	}
	getEl("badgeTable").innerHTML = "<table>"+s+"</table>";
}

/**
 * Sort by Points Per Play * Rating
 */
function sortPPPR(reverse) {
	resetRank();
	badges.sort(function(a,b) {
		var k1 = a.scoreR * a.points;
		var k2 = b.scoreR * b.points;
		return (k1 > k2) ? 1 : ( (k2 > k1) ? -1 : 0 );
	});
	
	if (reverse) badges.reverse();

	var s = badgeToHTMLRow(null, "pppr", reverse);
	for (var i in badges) {
		s += badgeToHTMLRow(badges[i]);
	}
	getEl("badgeTable").innerHTML = "<table>"+s+"</table>";
}

/**
 * Sort by Rating
 */
function sortR(reverse) {
	resetRank();
	badges.sort(function(a,b) {
		var k1 = a.game.rating;
		var k2 = b.game.rating;
		return (k1 > k2) ? 1 : ( (k2 > k1) ? -1 : 0 );
	});
	
	if (reverse) badges.reverse();

	var s = badgeToHTMLRow(null, "r", reverse);
	for (var i in badges) {
		s += badgeToHTMLRow(badges[i]);
	}
	getEl("badgeTable").innerHTML = "<table>"+s+"</table>";
}


/**
 * Sort by Earned Count
 */
function sortE(reverse) {
	resetRank();
	badges.sort(function(a,b) {
		var k1 = a.users_count;
		var k2 = b.users_count;
		return (k1 > k2) ? 1 : ( (k2 > k1) ? -1 : 0 );
	});
	
	if (reverse) badges.reverse();

	var s = badgeToHTMLRow(null, "e", reverse);
	for (var i in badges) {
		s += badgeToHTMLRow(badges[i]);
	}
	getEl("badgeTable").innerHTML = "<table>"+s+"</table>";
}


/**
 * Sort by Plays
 */
function sortP(reverse) {
	resetRank();
	badges.sort(function(a,b) {
		var k1 = a.game.plays;
		var k2 = b.game.plays;
		return (k1 > k2) ? 1 : ( (k2 > k1) ? -1 : 0 );
	});
	
	if (reverse) badges.reverse();

	var s = badgeToHTMLRow(null, "p", reverse);
	for (var i in badges) {
		s += badgeToHTMLRow(badges[i]);
	}
	getEl("badgeTable").innerHTML = "<table>"+s+"</table>";
}

/**
 * Sort by Difficulty
 */
function sortD(reverse) {
	resetRank();
	badges.sort(function(a,b) {
		var k1 = -a.points;
		var k2 = -b.points;
		return (k1 > k2) ? 1 : ( (k2 > k1) ? -1 : 0 );
	});
	
	if (reverse) badges.reverse();

	var s = badgeToHTMLRow(null, "d", reverse);
	for (var i in badges) {
		s += badgeToHTMLRow(badges[i]);
	}
	getEl("badgeTable").innerHTML = "<table>"+s+"</table>";
}

/**
 * Sort by Game Name
 */
function sortG(reverse) {
	resetRank();
	badges.sort(function(a,b) {
		var k1 = a.game.title.toLowerCase();
		var k2 = b.game.title.toLowerCase();
		return (k1 > k2) ? 1 : ( (k2 > k1) ? -1 : 0 );
	});
	
	if (reverse) badges.reverse();

	var s = badgeToHTMLRow(null, "g", reverse);
	for (var i in badges) {
		s += badgeToHTMLRow(badges[i]);
	}
	getEl("badgeTable").innerHTML = "<table>"+s+"</table>";
}

/**
 * Sort by Badge Name
 */
function sortB(reverse) {
	resetRank();
	badges.sort(function(a,b) {
		var k1 = a.name;
		var k2 = b.name;
		return (k1 > k2) ? 1 : ( (k2 > k1) ? -1 : 0 );
	});
	
	if (reverse) badges.reverse();

	var s = badgeToHTMLRow(null, "b", reverse);
	for (var i in badges) {
		s += badgeToHTMLRow(badges[i]);
	}
	getEl("badgeTable").innerHTML = "<table>"+s+"</table>";
}

// Not used
/*
function redraw(showOwned) {
	var s = badgeToHTMLRow(null, "d", reverse);
	for (var i in badges) {
		s += badgeToHTMLRow(badges[i]);
	}
	getEl("badgeTable").innerHTML = s;
}*/

function toggleOwned(hide, doAlert) {
	//hide = !hide;
	var display = hide ? "none":"";
	table.hideOwned = hide;
	for (var i in badges) {
		var b = badges[i];
		if (!b || !b.id) continue;
		if (userHasBadge[b.id]) {
			getEl("row"+(b.id)).style.display = display;
		}
	}
	if (doAlert && userName) {
		if (hide) alert(userName + "'s owned badges are now hidden.");
		else alert("All badges are now shown.");
	}
}


</script>

</head>




<!-----------  BODY START  ----------->

<body>
<div id="sidebar">
	<h3>Kongregate Charts and Graphs</h3>
	<i>by zAlbee</i>
	
	<form id="namesForm" name="names" method="GET" action="">
	<label for="u" class="formlabel">Enter Kong username:</label><br>
	<input type="text" name="u" value="<?php echo strToHTML($user);?>" title="Enter a Kongregate username to personalize"><br>
	<!-- <span class="formlabel"><a href="javascript:flipUserNames();void(0);">vs.</a></span><br>
	<input type="text" name="u2" value="<?php echo strToHTML($user2);?>" title="Enter another username to compare against (optional)"><br>-->
	<input type="checkbox" name="hideOwn" id="hideBox" onchange="toggleOwned(this.checked, true);"><label for="hideBox">Hide Owned Badges</label>
	<input type="submit" value="Personalize">
	</form>

	<ul>
		<li><a href="charts.php?u=<?php echo strToHTML($user);?>&u2=<?php echo strToHTML($user2);?>">Summary Charts</a></li>
		<li><a href="graph.php?u=<?php echo strToHTML($user);?>&u2=<?php echo strToHTML($user2);?>">Historical Graphs</a></li>
		<li><b>Badge Difficulty Ranking</b></li>
		<li><a href="botd.html">BOTD Archives</a></li>
		</ul>
	
	External Tools:
	<ul class="small">
	<?php if ($user) { ?>
		<li><a href="http://www.kongregate.com/accounts/<?php echo strToHTML($user);?>/points"><?php echo strToHTML($user);?>'s Point History</a></li>
	<?php } ?>
	<?php if ($user2) { ?>
		<li><a href="http://www.kongregate.com/accounts/<?php echo strToHTML($user2);?>/points"><?php echo strToHTML($user2);?>'s Point History</a></li>
	<?php } ?>
		<li><a href="http://badge.savagewolf.org/">Badge Browser</a> [SavageWolf]</li>
		<li><a href="http://www.kongregate.com/games/Senekis93/badge-master">Badge Master</a> [Senekis93]</li>
	</ul>
	
	<div id="debug_info">
	</div>
</div>

<div id="content">
	<h1>Badge Difficulty Analysis</h1>
	<p>
	This table measures difficulty of Kongregate badges in a few ways.
	</p>
	<ol>
		<li><dl><dt>Earned Count</dt>
			<dd>This is the absolute number of people who earned the badge. Fewer people = harder badge (generally). Simple.</dd>
		</dl></li>
		<li><dl><dt>Percent earned = earn_count / plays</dt>
			<dd>This normalizes earn count by the number of times the game was played. A 50% earned percentage means 1 badge was awarded for every 2 plays.
			A lower perecentage typically means a harder badge, assuming that harder badges take more plays to complete (usually they take more time and hence, more plays). 
			The inverse of this statistic says how many times you can expect to play this game before earning the badge.</dd>
		</dl></li>
		<li><dl><dt>Points per play = earn_count * badge_points / plays</dt>
			<dd>Instead of ranking the <em>badges</em> in difficulty, PPP ranks the easiest (or hardest) <em>points</em> to earn. 
			This says on average how many points you could expect to earn (from this badge) each time you play this game. 
			Note this doesn't take into account game plays that may be incurred trying to earn different badges (if the game has more than one badge), or game plays simply because a user likes replaying the game.</dd>
		</dl></li>
		<li><dl><dt>PE * rating</dt>
			<dd>This is the same as (2), but multiplied by rating. The idea is that higher rating means a more enjoyable game and hence "easier" to play. 
			It also helps offset a high gameplay count for the more popular games, where people may continue to play even when not trying to earn a badge.</dd>
		</dl></li>
		<li><dl><dt>PPP * rating</dt>
			<dd>This is the same as (3), but multiplied by rating.</dd>
		</dl></li> 
	</ol>
	
	<p>This project was partly inspired by <a href="http://www.kongregate.com/accounts/daelyte">daelyte</a>, who originally computed a list of easiest badges based on a sampling of users.
	</p>
</div>

<div id="content2">
<p>
<strong>How to use this table:</strong>
Click on a header to sort by that column. Click again to reverse the order. For most columns, higher number means easier and lower means a harder badge.
If you want to see which badges you already own in the table, enter your username in the box above.
</p>
<!-- 
	<form id="namesForm2" name="names" method="GET" action="">
	<label for="u" class="formlabel">Username (optional):</label>
	<input type="text" name="u" title="Enter a Kongregate username to filter by owned badges">
	<input type="submit" value="Submit">
	<input type="checkbox" name="hideOwn" id="hideBox" onchange="toggleOwned(this.checked);"><label for="hideBox">Hide Owned Badges</label>
	</form>
 -->
	<div id="badgeTable"><span style="color: #ff0000">Sorting data... If you still see this after a few seconds, there may be an error with the script.</span></div>

<script type="text/javascript">
// user badges presence indexed by id
var userHasBadge = [];
if (userName) {
	for (var i in userBadges) {
		if (!userBadges[i].badge_id) continue;
		var b = userBadges[i].badge_id;
		userHasBadge[b] = 1;
	}
	document.forms.namesForm.u.value = userName;
}

// games => [{title, url, rating, plays}, ...]
// badges => [{}]

if (!games) {
	println("games is null or empty");
}
else {
	//println(games.length);
}

// Games indexed by title
var games2 = {};

for (var i in games) {
	var g = games[i];
	games2[g.title] = g;
}

for (var i in badges) {
	var badge = badges[i];
	if (!badge || !badge.games[0]) {
		delete badges[i];
		continue;
	}
	var game = games2[badge.games[0].title];
	if (!game) {
		delete badges[i];
		continue;
	}
	// "Easy" score = number earned / times played
	if (game.plays <= 0) {
		badge.score = 0;
		badge.scoreR = 0;
	} else {
		badge.score = badge.users_count / game.plays;
		// "Easy" x "Good"
		badge.scoreR = badge.users_count / game.plays * game.rating;
	}
	badge.game = game;
}

sortPE(true);

if (hideOwn) {
	document.forms.namesForm.hideBox.checked = true;
	toggleOwned(true, false);
}

</script>

<p>
<b>History:</b><br>
2012-09-16: First public release<br>
2012-11-??: Add ability to hide your owned badges from the list.<br>
2012-11-11: Fix table not showing in IE9. The badges file is now consistent with the plays count file (both updated weekly), so new badges shouldn't get hugely inaccurate percent earned stats anymore. Add sidebar.<br>
2013-02-01: Open game links in new tab.<br>
2014-02-16: Kongregate server is having intermittent problems, occasionally throwing 502 and 503 errors. This is affecting my ability to fetch the data for gameplays and ratings. You may see "-1" for these values until the problem is resolved.<br> 
</p>

<p>
<b>Credits:</b><br>
Made by: <a href="http://zalbee.intricus.net/">zAlbee</a><br>
Badge Data: Kongregate badges.json<br>
Play counts data: game/metrics.json<br>
Questions? <a href="mailto:zalbee@gmail.com?subject=Kongregate Badge Difficulty Analysis">Email me</a>, post in <a href="http://www.kongregate.com/forums/1-kongregate/topics/297971-new-tool-to-find-easiest-hardest-badges">this thread</a>.
</p>
</div>

</body>
</html>