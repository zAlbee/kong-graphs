<!DOCTYPE html>
<html>
<?php
	require('includes.php');
	$user = preg_replace('/[^A-Za-z0-9_]/', '', strFromGPC($_GET['u']));
	$user2 = preg_replace('/[^A-Za-z0-9_]/', '', strFromGPC($_GET['u2']));
	$debug = isset($_GET['debug']);
	$numInvalid = 0;
?>
<head>
<title><?php
if ($user && $user2) {
	echo strToHTML($user) . " vs " . strToHTML($user2) . " Badge Graph";
} else if ($user) {
	echo strToHTML($user) . "'s Badge Graph";
} else if ($user2) {
	echo strToHTML($user2) . "'s Badge Graph";
} else {
	echo 'Graphing Kongregate Badges';
}
?></title>
<!-- <div>Honey pot trap for malware</div> -->
<style>
div#buttons {
	position:fixed;
	top: 0;
	left: 200px;
	padding: 0.2em 1.0em;
	background-color: #eeeeee;
	z-index: 9000;
}
#content {
	margin-top: 1em;
	display: inline-block;
	vertical-align: top;
	/* IE7 inline-block hack, trigger hasLayout */
	zoom: 1;
	*display: inline;
}
#sidebar {
	width: 180px;
	background: #dddddd;
	z-index: 9000;
	font-family: "Helvetica", "Arial", sans-serif;
	font-size: 10pt;
	padding: 5px;

	display: inline-block;
	vertical-align: top;
	/* IE7 inline-block hack, trigger hasLayout */
	zoom: 1;
	*display: inline;
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

#debug_info {
	background: white;
	font-size: 9pt;
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
<meta http-equiv="X-UA-Compatible" content="IE=EmulateIE7; IE=EmulateIE9"> 
    <!--[if IE]><script type="text/javascript" src="excanvas.compiled.js"></script><![endif]-->
<script type="text/javascript">
var badges, userBadges, userBadges2;
// Callback for Kong JSON
cbUser = function (list) {
	userBadges = list;
};
</script>
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
<script type="text/javascript" src="badges.js"></script>
<script type="text/javascript" src="dygraph-combined.js"></script>
<script type="text/javascript" src="dom.js"></script>
<script type="text/javascript">

var userName = "<?php echo strToJS(strToHTML($user));?>";
var userName2 = "<?php echo strToJS(strToHTML($user2));?>";
var outdated = false;
var debugPHP = "<?php echo $debug;?>";
var debugFlag = !!debugPHP;

//Offline Testing
if (userName.lastIndexOf("<" + "?php", 0) === 0) {
	println('<scr'+'ipt type="text/javascript" src="zalbee.json"></scr'+'ipt>');
	userName = "";
	userName2 = "zAlbee";
}
if (debugPHP.lastIndexOf("<" + "?php", 0) === 0) {
	debugFlag = 0;
}

var hasGraph2 = false, hasGraph3 = false;

function showLess() {
	disable("showLessButton");
	enable("showUser2Button");
	enable("showBadgesButton");
	enable("showPointsButton");

	showEl("groupUser1");	
	hideEl("groupUser2");
	hideEl("groupCompareBadges");
	hideEl("groupComparePoints");
	graphs[0].resize();
	graphs[5].resize();
	graphs[6].resize();
}

function showUser2() {
	enable("showLessButton");
	disable("showUser2Button");
	enable("showBadgesButton");
	enable("showPointsButton");

	hideEl("groupUser1");
	showEl("groupUser2");
	hideEl("groupCompareBadges");
	hideEl("groupComparePoints");
	graphs[7].resize();
	graphs[8].resize();
	graphs[9].resize();
}

function showBadgesG() {
	enable("showLessButton");
	enable("showUser2Button");
	disable("showBadgesButton");
	enable("showPointsButton");

	hideEl("groupUser1");
	hideEl("groupUser2");
	showEl("groupCompareBadges");
	hideEl("groupComparePoints");

	if (!hasGraph2) {
		// DyGraphs will create a size 0x0 canvas if the containing div is hidden.
		// Call this AFTER we show the div!
		if (userName2) {
			makeCompareGraph(userBadges, userBadges2, userName, userName2, false, graphs);
		} else {
			makeCompareGraph(userBadges, badges, userName, null, false, graphs);
		}
	}
	graphs[1].resize();
	graphs[2].resize();
}

function showPointsG() {
	enable("showLessButton");
	enable("showUser2Button");
	enable("showBadgesButton");
	disable("showPointsButton");

	hideEl("groupUser1");
	hideEl("groupUser2");
	hideEl("groupCompareBadges");
	showEl("groupComparePoints");

	if (!hasGraph3) {
		// DyGraphs will create a size 0x0 canvas if the containing div is hidden.
		// Call this AFTER we show the div!
		if (userName2) {
			makeCompareGraph(userBadges, userBadges2, userName, userName2, true, graphs);
		} else {
			makeCompareGraph(userBadges, badges, userName, null, true, graphs);
		}
}
	graphs[3].resize();
	graphs[4].resize();
}

function setStacked(g, el) {
	//alert(graphs[g].yAxisRange());
	var options = {
		stackedGraph: el.checked, 
		fillGraph: el.checked//,
		//valueRange: graphs[g].yAxisRange()
	}
	graphs[g].updateOptions(options);
}

// badges indexed by id
var badgesTable = [];
var graphs = [];
var options = [];
	
function makeSimpleGraph(badges, userBadges, userName, graphs, userNum) {
	idx = (userNum == 2) ? 7 : 0; // graphs[] index

	// badges indexed by id
	for (var i in badges) {
		var id = badges[i].id;
		badgesTable[id] = badges[i];
	}

	/**
	 * Start processing for the user badges
	 */
	// CSV data
	var data = "Date,Badges,Points\n";
	var count = 0;
	var points = 0;

	if (userName) {
		//userBadges.reverse(); // get in chrono order
		for (var i in userBadges) {
			var x = userBadges[i];
			// Exclude mobile badges (they have mobile_badge_id only)
			if (!x.badge_id) continue;
			count++;
			if (!badgesTable[x.badge_id]) {
				println("Badge id " + x.badge_id + " not found. i = " + i 
					+ ", dbg: " + x.created_at.substring(0,19) + "," + count + "," + points);
				outdated = true;
			} else {
				points += badgesTable[x.badge_id].points;
			}
			// Must truncate off the timezone offset as DyGraph doesn't understand it
			data += x.created_at.substring(0,19) + "," + count + "," + points + "\n";
		}
		if (outdated) {
			println('<br><span style="font-size: 12pt">Global badge data is out of date! ' +
				'Badges are updated every 24 hours at 20:00 PST/PDT. If the badge was NOT added recently, ' +
				'please <a href="http://www.refreshyourcache.com/en/cache/">refresh your browser</a> via <b>Ctrl+F5</b> or <b>Shift+Reload</b>.</span>');
		}
	}
	else {
		/**
		 * No user specified - Graph all Kong badges.
		 */
		for (var i in badges) {
			var x = badges[i];
			// Exclude mobile badges (they have mobile_badge_id only)
			if (!x.id) continue;
			count++;
			points += x.points;
			// Must truncate off the timezone offset as DyGraph doesn't understand it
			data += x.created_at.substring(0,19) + "," + count + "," + points + "\n";
		}
	}
	graphs[idx] = new Dygraph(
	
	    // containing div
	    document.getElementById("graphdiv" + userNum),
		data,
		{
	    	title: userName ? "Earned Badges over Time (" + userName + ")" :
		    	"All Kongregate badges over time",
			xlabel: "Date",
			ylabel: "Badges",
	//		labels: [ 'Date', 'Badges', 'Points' ],
			'Points': {
				axis: {
					ylabel: "Points"
				}
			}
	        
		}
	);

	getEl("firstText").innerHTML = 'Use mouse to zoom. Double-click to zoom out.';
}

/**
 * Compares two sets of badges, generating a percent graph and a comparison graph.
 * Can be two users, or one user and Kongregate.
 * If user2 is NULL, then this function treats badges2 as Kongregate badges.
 * Note: Assumes badges1 and badges2 are in chronological order.
 * 
 */
function makeCompareGraph(badges1, badges2, user1, user2, isPoints, graphs) {
	var isKong = (user2 == null); // if badges2 contains global Kongregate badges.
	if (isKong) user2 = "All";
	var noun = isPoints ? "points":"badges";
	var nounT = isPoints ? "Point":"Badge";
	
	var u1count = 0;
	var u2count = 0;
	var tieCount = 0;
	var lastTieDate = "";
	var bestPct = 0;
	var bestPctDate = "";
	var u1more = 0;
	var u2more = 0;
	var maxDiff = 0;
	var maxDiffUser = 0;
	var maxDiffDate = "never";
	var minDiff = 2147483647;
	var minDiffDate = "never";
	var M = badges1.length;
	var N = badges2.length;
	var date1;
	var date2;

	// The CSV strings (init the headers)
	var percentData = "Date," + nounT + " %\n";
	var compareData = "Date," + user1 + "," + user2 + "\n";
	
	// Merge the two lists
	for (var i=0, j=0; i < M || j < N; ) {
		var isUser1 = false;
		var isUser2 = false;
		var x = null;

		// Exclude mobile badges (they have mobile_badge_id only)
		if (i < M && !badges1[i].badge_id) {
			i++; continue;
		}
		if (j < N && isKong && !badges2[j].id) {
			j++; continue;
		}
		if (j < N && !isKong && !badges2[j].badge_id) {
			j++; continue;
		}

		// select the next earliest one
		if (i >= M) isUser2 = true;
		else if (j >= N) isUser1 = true;
		else {
			date1 = new Date(badges1[i].created_at);
			date2 = new Date(badges2[j].created_at);
			if (date1 < date2) {
				isUser1 = true;
			} else if (date1 > date2) { 
				isUser2 = true;
			} else { 
				isUser1 = isUser2 = true;
			}
		}
		if (isUser1) {
			x = badges1[i++];
			if (isPoints) {
				u1count += badgesTable[x.badge_id].points;
			}
			else {
				u1count++;
			}
		}
		if (isUser2) {
			x = badges2[j++];
			if (isPoints) {
				if (!isKong)
					u2count += badgesTable[x.badge_id].points;
				else 
					u2count += x.points;
			}
			else {
				u2count++;
			}
		}
		var pct = u2count == 0 ? -1 : (100*u1count/u2count); // Math.floor(ucount/bcount*10000)/100;
		if (pct > bestPct) {
			bestPct = pct;
			bestPctDate = x.created_at;
		}
		// Count ties
		if (u1count == u2count) {
			tieCount++;
			lastTieDate = x.created_at;
			minDiff = 0;
		} else if (u1count > u2count) {
			u1more++;
			diff = u1count - u2count; 
			if (diff > maxDiff) {
				maxDiff = diff;
				maxDiffUser = 1;
				maxDiffDate = x.created_at;
			}
			if (diff < minDiff && u2count > 0) {
				minDiff = diff;
				minDiffDate = x.created_at;
			}
		} else {
			u2more++;
			diff = u2count - u1count;
			if (diff > maxDiff) {
				maxDiff = diff;
				maxDiffUser = 2;
				maxDiffDate = x.created_at;
			}
			if (diff < minDiff && u1count > 0) {
				minDiff = diff;
				minDiffDate = x.created_at;
			}
		}
		percentData += x.created_at.substring(0,19) + "," + pct + "\n";
		compareData += x.created_at.substring(0,19) + "," + u1count + "," + u2count + "\n";
	}

	// Write some interesting stats.
	var u1str = "<span class='user1'>" + user1 + "</span>";
	var u2str = "<span class='user2'>" + user2 + "</span>";
	var s = "<p>Highest "+ (isKong?"earned ":"relative ") + noun +" percentage: <span class='emph_pct'>" + (Math.round(bestPct*100)/100) + "%</span> <span class='info'>achieved on " + bestPctDate + ".</span><br>\n";
	if (tieCount > 0) {
		s += (isKong ? "Fully badged: " : "Tied in "+noun) + " <span class='emph'>" + tieCount + "</span> times. <span class='info'>Most recent on " + lastTieDate + "</span>.<br>\n";
	} else {
		
		s += "Closest to " +(isKong?"full":"tying")+ " was <span class='emph'>" + minDiff + "</span> "+noun + " <span class='info'>on " + minDiffDate + "</span>.<br>\n";
	}
	if (!isKong) {
		if (u1count == u2count) {
			s += "Currently, " + u1str + " and " + u2str + 
				" are tied with the same number of " + noun + ".<br>\n";
		} else {
			s += "Currently, " + (u1count > u2count ? u1str : u2str) + " has <span class='emph'>" + 
				Math.abs(u1count-u2count) + "</span> more " + noun + " than " + (u1count > u2count ? u2str : u1str) + 
				".<br>\n";
		}			

		s += "Biggest lead was <span class='emph'>" + maxDiff + "</span> " +noun+ " in favour of " + 
			(maxDiffUser == 1 ? u1str : u2str) + " <span class='info'>on " + maxDiffDate + ".</span><br>\n";

		if (u1more == u2more) {
			var a = u1more, b = u2more, c = tieCount;
			s += "Overall, " + u1str + " and " + u2str + 
				" beat each other in " + noun + " the exact same number of times. An incredible coincidence! <span class='info'>["
				+a+" win/"+b+" loss/"+c+" tie]</span><br>\n";
		} else {
			var a,b,c = tieCount;
			if (u1more > u2more) {
				a = u1more; b = u2more;
			} else {
				a = u2more; b = u1more;
			}
			morePct = Math.round((a/(a+b+c))*100);
			s += "Overall, " + (u1more > u2more ? u1str : u2str) + " had more " + noun +  
				" <span class='emph'>" + 
				morePct + "%</span> of the time <span class='info'>["+a+" win/"+b+" loss/"+c+" tie]</span><br>\n";
		}
	}
	s += "</p>";

	if (debugFlag) {
		s += "<code>compareData CSV = " + compareData + "</code><br><br>";
		s += "<code>percentData CSV = " + percentData + "</code><br><br>"; 
	}
		
	getEl("percentText" + (isPoints?"Points":"")).innerHTML = s;
	
	var offset = (isPoints ? 3 : 1);
	
	graphs[offset+0] = new Dygraph(
	
	    // containing div
	    document.getElementById("percentGraph" + (isPoints?"Points":"")),
		percentData,
		{
	    	title: "Earned "+nounT+" Percentage (" + user1 + (isKong ? ")" : " vs " + user2 + ")"),
			xlabel: "Date",
			ylabel: nounT + " %"
	//		labels: [ 'Date', 'Badges', 'Points' ],
		}
	);

	graphs[offset+1] = new Dygraph(
	
	    // containing div
	    document.getElementById("compareGraph" + (isPoints?"Points":"")),
		compareData,
		{
	    	title: "" + user1 + " vs " + user2 + " " + nounT + "s",
			xlabel: "Date",
			ylabel: nounT + "s"
		}
	);

	if (isPoints) hasGraph3 = true;
	else hasGraph2 = true;
}

function makeStackedGraph(badges, userName, isPoints, graphs, userNum) {
	var noun = isPoints ? "points":"badges";
	var nounT = isPoints ? "Point":"Badge";
	var nounTs = nounT + "s";
	// CSV data
	var data = "Date,Easy,Med,Hard,Imp\n";
	var easy = 0;
	var med = 0;
	var hard = 0;
	var imp = 0;
	var outdated = false;
	var uOffset = (userNum == 2) ? 3 : 0;

	for (var i=0; i<badges.length; i++) {
		var x = badges[i];
		// Exclude mobile badges (they have mobile_badge_id only)
		if (x.mobile_badge_id) continue;
		
		if (userName) {
			if (!badgesTable[x.badge_id]) {
				println("Badge id " + x.badge_id + " not found. i = " + i 
					+ ", dbg: " + x.created_at.substring(0,19) + "," + count + "," + points);
				outdated = true;
				continue;
			} else {
				var points = badgesTable[x.badge_id].points;
			}
		} else {
			var points = x.points;
		}
		
		if (points == 5) {
			easy += isPoints ? points : 1;
		} else if (points == 15) {
			med += isPoints ? points : 1;
		} else if (points == 30) {
			hard += isPoints ? points : 1;
		} else if (points == 60) {
			imp += isPoints ? points : 1;
		}
		
		// For stacked graphs, if two entries in a row have the same date, then there is an anomaly. Avoid that.
		var y = badges[i+1];
		if (y && y.created_at == x.created_at) {
			//alert('skipping ' + i);
		} else {
			// Must truncate off the timezone offset as DyGraph doesn't understand it
			data += x.created_at.substring(0,19) + "," + easy + "," + med + "," + hard + "," + imp + "\n";
		}
	}
	
	var index = (uOffset) + (isPoints ? 6 : 5);

	options[index] = {
	    	title: userName ? nounTs + " Breakdown (" + userName + ")" :
		    	"All " + nounTs + " Breakdown",
			xlabel: "Date",
			ylabel: nounTs,
   			stackedGraph: true,
   			fillGraph: true
		};

	graphs[index] = new Dygraph(
	
	    // containing div
	    isPoints ? document.getElementById("stackedPointsDiv" + userNum) 
	    	     : document.getElementById("stackedBadgesDiv" + userNum),
		data,
		options[index]
	);
	
}

function flipUserNames() {
	var f = document.forms["names"];
	var u = f.u.value;
	f.u.value = f.u2.value;
	f.u2.value = u;
}
</script>
</head>

<body>
<div id="buttons">
<!--[if lt IE 9]>
<span style="color: #cc0000">Warning: You are using an old version of Internet Explorer. The graphs may work, but will be <em>very slow</em>. Please upgrade to 
Internet Explorer 9 (or later) or use another web browser like Google Chrome or Mozilla Firefox for best results.</span><br />
<![endif]-->
<form><label>Show Graph Type:</label>
	<input id="showLessButton" type="button" value="User 1" onclick="showLess();">
	<input id="showUser2Button" type="button" value="User 2" onclick="showUser2();">
	<input id="showBadgesButton" type="button" value="Badge Compare" onclick="showBadgesG();">
	<input id="showPointsButton" type="button" value="Points Compare" onclick="showPointsG();">
</form>
</div>

<div id="topFiller">&nbsp;</div>

<div id="sidebar">
	<h3>Kongregate Charts and Graphs</h3>
	<i>by zAlbee</i>
	
	<form id="namesForm" name="names" method="GET" action="">
	<label for="u" class="formlabel">Enter Kong username:</label><br>
	<input type="text" name="u" value="<?php echo strToHTML($user);?>" title="Enter a Kongregate username to graph"><br>
	<span class="formlabel"><a href="javascript:flipUserNames();void(0);">vs.</a></span><br>
	<input type="text" name="u2" value="<?php echo strToHTML($user2);?>" title="Enter another username to compare against (optional)"><br>
	<input type="submit" value="Graph it!">
	</form>

	<ul>
		<li><a href="charts.php?u=<?php echo strToHTML($user);?>&u2=<?php echo strToHTML($user2);?>">Summary Charts</a></li>
		<li><b>Historical Graphs</b></li>
		<li><a href="badgerank.php?u=<?php echo strToHTML($user);?>">Badge Difficulty Ranking</a></li>
		<li><a href="botd.html">BOTD Archives</a></li>
	</ul>
	
	Other Tools:
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
<div id="groupUser1">
	<div id="graphdiv1"></div>
	<div id="stackedBadgesDiv1"></div>
	<input id="stackedBadge1" type="checkbox" onclick="setStacked(5,this)" checked><label for="stackedBadge1">Stacked Graph</label>
	<div id="stackedPointsDiv1"></div>
	<input id="stackedPoint1" type="checkbox" onclick="setStacked(6,this)" checked><label for="stackedPoint1">Stacked Graph</label>
</div>
<div id="groupUser2">
	<div id="graphdiv2"></div>
	<div id="stackedBadgesDiv2"></div>
	<input id="stackedBadge2" type="checkbox" onclick="setStacked(8,this)" checked><label for="stackedBadge2">Stacked Graph</label>
	<div id="stackedPointsDiv2"></div>
	<input id="stackedPoint2" type="checkbox" onclick="setStacked(9,this)" checked><label for="stackedPoint2">Stacked Graph</label>
</div>
<div id="groupCompareBadges">
	<div id="compareGraph"></div>
	<div id="percentText"></div>
	<div id="percentGraph"></div>
</div>
<div id="groupComparePoints">
	<div id="compareGraphPoints"></div>
	<div id="percentTextPoints"></div>
	<div id="percentGraphPoints"></div>
</div>
<div id="firstText"></div>
<p style="font-size: 0.8em">
<script type="text/javascript">

////////////////////////////////////////////
//Code Execution Starts Here

//Sanity checking
//Set both user and Kong badges in ascending chronological order
var valid = true;
function debug(str) {
	println(str);
	//getEl("debug_info").innerHTML += str + "<br>\n";
}

if (typeof badges === "undefined" || !badges || !badges.length) {
	debug("Could not get Kongregate badge data. File may be corrupt.");
	valid = false;
}

if (userName) {
	if ((typeof userBadges === "undefined") || !userBadges) {
		debug("User " + userName + " is invalid");
		valid = false;
	} else if (!userBadges.length) {
		debug("User " + userName + " has no badges");
		valid = false;
	} else {
		userBadges.reverse(); // get in chrono order
	}
}

if (userName2) {
	if (typeof userBadges2 === "undefined" || !userBadges2) {
		debug("User " + userName2 + " is invalid");
		valid = false;
	} else if (userName2 && !userBadges2.length) {
		debug("User " + userName2 + " has no badges");
		valid = false;
	} else {
		userBadges2.reverse(); // get in chrono order
	}
}

if (valid) {
	// Create graph with first username that exists.
	// First, normalize it to the first one.
	if (!userName && userName2) {
		userName = userName2;
		userName2 = "";
		userBadges = userBadges2;
		userBadges2 = null;
	}
	if (userName) {
		date = new Date(userBadges[userBadges.length-1].created_at);
		debug("Last known User data (" + userName + "): " + date);
	}
	if (userName2) {
		date = new Date(userBadges2[userBadges2.length-1].created_at);
		debug("Last known User data (" + userName2 + "): " + date);
	}
	date = new Date(badges[badges.length-1].created_at);
	debug("Last known Badge data: " + date);
	
	makeSimpleGraph(badges, userBadges, userName, graphs, 1);
	if (userName) {
		getEl("showLessButton").value = userName;
		makeStackedGraph(userBadges, userName, false, graphs, 1);
		makeStackedGraph(userBadges, userName, true, graphs, 1);
	} else {
		hideEl("showBadgesButton");
		hideEl("showPointsButton");
		getEl("showLessButton").value = "Kongregate";
		makeStackedGraph(badges, null, false, graphs, 1);
		makeStackedGraph(badges, null, true, graphs, 1);
	}
	if (userName2) {
		makeSimpleGraph(badges, userBadges2, userName2, graphs, 2);
		makeStackedGraph(userBadges2, userName2, false, graphs, 2);
		makeStackedGraph(userBadges2, userName2, true, graphs, 2);
		getEl("showUser2Button").value = userName2;
	} else {
		hideEl("showUser2Button");
	}
	if (userName && userName2) {
		// Show comparison graph by default.
		showBadgesG();
	} else {
		showLess();
	}
}

//Code Execution Ends Here
////////////////////////////////////////////

</script>
</p>
</div>

<p>Note: The points here are only from regular badges, and do not include points from referrals, ratings, challenges, quests, mobile, or badges of the day.</p>

<p>
<b>History:</b><br>
2011-09-04: First public release<br>
2012-05-29: Updated DyGraphs to fix display on Android browsers<br>
2012-06-10: Use new caching system to speed up loading by 2x and reduce server load. Update DyGraphs to latest.<br>
2012-08-13: Fixed graphing errors caused by mobile badge data.<br>
2012-08-14: New comparison feature! Compare any two users, by badges or by points. Remove caching layer (requests go straight to Kongregate and are cached locally by your browser).<br>
2012-08-26: Added difficulty breakdown to basic graphs tab.<br>
2012-11-22: Added closest stat in Compare tab. Clearer error message when badge data out of date. Added tab for second user if present.<br>
</p>

<p>
<b>Credits:</b><br>
Made by: <a href="http://zalbee.intricus.net/">zAlbee</a><br>
Badge Data: Kongregate badges.json<br>
Graph Library: <a href="http://dygraphs.com/">DyGraphs</a> / Dan Vanderkam<br>
<br>
Questions? <a href="mailto:zalbee@gmail.com?subject=Kongregate Badge Graphs">Email me</a>, post in <a href="http://www.kongregate.com/forums/1-kongregate/topics/201886-user-badge-graphs">this thread</a>, or <a href="http://zalbee.intricus.net/2012/01/kongregate-stats-and-graphs/">comment here</a> (no account required).
</p>
</body>
</html>