<!DOCTYPE html>
<html>
<head>
<?php
	require('includes.php');
	$user = preg_replace('/[^A-Za-z0-9_]/', '', strFromGPC($_GET['u']));
	$user2 = preg_replace('/[^A-Za-z0-9_]/', '', strFromGPC($_GET['u2']));
	$debug = isset($_GET['debug']);
?>
<title><?php
if ($user && $user2) {
	echo strToHTML($user) . " vs " . strToHTML($user2) . " Badge Charts";
} else if ($user) {
	echo strToHTML($user) . "'s Badge Charts";
} else if ($user2) {
	echo strToHTML($user2) . "'s Badge Charts";
} else {
	echo 'Kongregate Badge Charts';
}
?></title>
<!-- <div>Honey pot trap for malware</div> -->
<style>
body {
	width: 1320px;
}
div#buttons {
	position:fixed;
	top: 0;
	left: 200px;
	padding: 0.2em 1.0em;
	background-color: #eeeeee;
	z-index: 9000;
}
div#groupUser1, div#groupUser2 {
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
.small {
	font-size: 80%;
}
</style>
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
<script type="text/javascript" src="dom.js"></script>
<meta http-equiv="X-UA-Compatible" content="IE=EmulateIE7; IE=EmulateIE9"> 
    <!--[if IE]><script type="text/javascript" src="excanvas.compiled.js"></script><![endif]-->
<script type="text/javascript" src="jscharts.js"></script>
<script type="text/javascript">
var userName = "<?php echo strToJS(strToHTML($user));?>";
var userName2 = "<?php echo strToJS(strToHTML($user2));?>";
var kong = "Kongregate";

// Offline Testing
if (userName.lastIndexOf("<" + "?php", 0) === 0) {
	println('<scr'+'ipt type="text/javascript" src="zalbee.json"></scr'+'ipt>');
	userName = "";
	userName2 = "zAlbee";
}

// badges indexed by id
var badgesTable = [];
for (var i in badges) {
	var id = badges[i].id;
	badgesTable[id] = badges[i];
}

Date.dayNames = ["Sun", "Mon", "Tue", "Wed", "Thu", "Fri", "Sat"];
Date.monthNames = ["Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"];

// 0-based month, full (4-digit) year
function daysInMonth(m, y) {
	var days = [31, 28, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31];
	if (m == 1) return (y%4 == 0 && (y%100 != 0 || y%400 == 0)) ? 29 : 28;
	return days[m];
}

function makeRecentDays(badges, n, isPoints, debug, div, textDiv, name, pMax) {
	var dailyCounts = new Array(n);
	for (var i=0; i<n; i++) {
		dailyCounts[i] = 0;
	}
	var dailyList = new Array(n);
	for (var i=0; i<n; i++) {
		dailyList[i] = "";
	}
	var totalCount = 0;
	var curCount = 0;
	var curDay = 0;
	var bestCount = 0;
	var bestDay = 0;

	var now = new Date();
	var today = new Date(now.getFullYear(), now.getMonth(), now.getDate(), 23, 59, 59, 999);
	
	for (var i in badges) {
		var x = badges[i];
		if (x.mobile_badge_id || !x.created_at) continue;
		var points = 0;
		if (x.badge_id) points = badgesTable[x.badge_id].points;
		else if (x.id) points = x.points;
		
		var date = new Date(x.created_at);
		var dd = parseInt((today - date) / 1000 / 60 / 60 / 24);
		
		if (dd != curDay) {
			curCount = 0;
			curDay = dd;
		}
		curCount += (isPoints ? points : 1);
		totalCount += (isPoints ? points : 1);
		if (curCount >= bestCount) {
			bestCount = curCount;
			bestDay = curDay;
		}
		
		// For bar chart
		if (dd < n) {
			dailyCounts[dd] = curCount;
			if (debug) dailyList[dd] += x.name + " " + x.created_at + ", ";
			if (curCount > pMax[0]) pMax[0] = curCount;
		}
		// Do not break
	}
	
	var s = "Avg: " + roundTo(totalCount/curDay, 2) + (isPoints?" pts":" badges") +"/day" + "<br>";
	s += ("Most: " + bestCount + (isPoints?" pts":" badges") + " in one day - " + bestDay + " days ago") + "<br>";
	getEl(textDiv).innerHTML = s;
	var chartData1 = [];
	for (var i=0; i<n; i++) {
		if (debug) println("" + i + " days ago: " + dailyCounts[i] + " " + dailyList[i]);
		chartData1.push([''+i, dailyCounts[i]]);
	}

	var myChart = new JSChart(div, 'bar');
	myChart.setDataArray(chartData1);
	myChart.setAxisNameX('Days Ago');
	myChart.setAxisNameY(isPoints ? 'Points' : 'Badges');
	myChart.setSize(550, 300);
	myChart.setAxisReversed(false);
	myChart.setTitle(name + ' ' + (isPoints ? 'Points' : 'Badges') + ' - Last ' + n + ' Days');
	myChart.setTitleColor('#000000');
	myChart.setTitleFontSize(12);
	myChart.setBarColor('#bb0000');
	myChart.setBarOpacity(0.9);
	myChart.setBarValuesColor('#bb0000');
	//myChart.draw(); // delay drawing until after y-interval normalized
	return myChart;
}

function makeRecentMonths(badges, n, isPoints, debug, div, textDiv, name, pMax) {
	var monthlyCounts = new Array(n);
	for (var i=0; i<n; i++) {
		monthlyCounts[i] = 0;
	}
	var monthlyList = new Array(n);
	for (var i=0; i<n; i++) {
		monthlyList[i] = "";
	}
	var totalCount = 0;
	var curCount = 0;
	var curMonth = 0;
	var bestCount = 0;
	var bestMonth = 0;

	var now = new Date();
	var thisMonth = new Date(now.getFullYear(), now.getMonth(), 
			daysInMonth(now.getMonth(), now.getFullYear()), 23, 59, 59, 999);
	
	for (var i in badges) {
		var x = badges[i];
		if (x.mobile_badge_id || !x.created_at) continue;
		var points = 0;
		if (x.badge_id) points = badgesTable[x.badge_id].points;
		else if (x.id) points = x.points;
		
		var date = new Date(x.created_at);
		// TODO: This divides the months into chunks of 30 days, not the real months.
		var mm = parseInt((thisMonth - date) / 1000 / 60 / 60 / 24 / 30);
		
		if (mm != curMonth) {
			curCount = 0;
			curMonth = mm;
		}
		curCount += (isPoints ? points : 1);
		totalCount += (isPoints ? points : 1);
		if (curCount >= bestCount) {
			bestCount = curCount;
			bestMonth = curMonth;
		}
		
		// For bar chart
		if (mm < n) {
			monthlyCounts[mm] = curCount;
			if (debug) {
				monthlyList[mm] += (x.name ? x.name : '-') + " " + x.created_at + ", ";
			}
			if (curCount > pMax[0]) pMax[0] = curCount;
		}
		// Do not break
	}
	
	var s = "Avg: " + roundTo(totalCount/curMonth, 2) + (isPoints?" pts":" badges") +"/month" + "<br>";
	s += ("Most: " + bestCount + (isPoints?" pts":" badges") + " in one month - " + bestMonth + " months ago") + "<br>";
	getEl(textDiv).innerHTML = s;
	var chartData1 = [];
	for (var i=0; i<n; i++) {
		if (debug) println("" + i + " months ago: " + monthlyCounts[i] + " " + monthlyList[i]);
		chartData1.push([''+i, monthlyCounts[i]]);
	}

	var myChart = new JSChart(div, 'bar');
	myChart.setDataArray(chartData1);
	myChart.setAxisNameX('Months Ago');
	myChart.setAxisNameY(isPoints ? 'Points' : 'Badges');
	myChart.setSize(500, 300);
	myChart.setAxisReversed(false);
	myChart.setTitle(name + ' ' + (isPoints ? 'Points' : 'Badges') + ' - Last ' + n + ' Months');
	myChart.setTitleColor('#000000');
	myChart.setTitleFontSize(12);
	myChart.setBarColor('#333399');
	myChart.setBarOpacity(0.9);
	myChart.setBarValuesColor('#333399');
	//myChart.draw(); // delay drawing until after y-interval normalized
	return myChart;
}

function bucketize(badges, dayDiv, hourDiv, monthDiv, name) {
	var dayCounts = new Array(7);
	var hourCounts = new Array(24);
	var monthCounts = new Array(13);
	
	for (var d=0; d<7; d++) {
		dayCounts[d] = 0;
	}
	for (var h=0; h<24; h++) {
		hourCounts[h] = 0;
	}
	for (var m=0; m<12; m++) {
		monthCounts[m] = 0;
	}
	
	for (var i in badges) {
		var x = badges[i];
		var date = new Date(x.created_at);
		var h = date.getHours();
		var d = date.getDay();
		var m = date.getMonth();
		dayCounts[d]++;
		hourCounts[h]++;
		monthCounts[m]++;
	}

	// Days
	var sum = 0;
	var chartData1 = [];
	for (var d=0; d<7; d++) {
		//println(d + ": " + dayCounts[d]);
		sum += dayCounts[d];
		chartData1.push([Date.dayNames[d], dayCounts[d]]);
	}

	var myChart = new JSChart(dayDiv, 'bar');
	myChart.setDataArray(chartData1);
	myChart.setAxisNameX('Weekday');
	myChart.setAxisNameY('Badges');
	myChart.setSize(500, 250);
	myChart.setTitle(name + ' Badges by Weekday');
	myChart.setTitleColor('#000000');
	myChart.setTitleFontSize(12);
	myChart.setBarColor('#993399');
	myChart.setBarOpacity(0.9);
	myChart.setBarValuesColor('#993399');
	myChart.draw();
	
	// Hours
	var sum = 0;
	var chartData2 = [];
	for (var h=0; h<24; h++) {
		//println(h + ": " + hourCounts[h]);
		sum += hourCounts[h];
		chartData2.push([''+h, hourCounts[h]]);
	}

	var myChart = new JSChart(hourDiv, 'bar');
	myChart.setDataArray(chartData2);
	myChart.setAxisNameX('Hour');
	myChart.setAxisNameY('Badges');
	myChart.setSize(500, 350);
	myChart.setTitle(name + ' Badges by Hour of Day');
	myChart.setTitleColor('#000000');
	myChart.setTitleFontSize(12);
	myChart.setBarColor('#cc6600');
	myChart.setBarOpacity(0.9);
	myChart.setBarValuesColor('#bb6600');
	myChart.draw();
	
	// Months
	var sum = 0;
	var chartData3 = [];
	for (var m=0; m<12; m++) {
		//println(h + ": " + hourCounts[h]);
		sum += monthCounts[m];
		chartData3.push([Date.monthNames[m], monthCounts[m]]);
	}

	var myChart = new JSChart(monthDiv, 'bar');
	myChart.setDataArray(chartData3);
	myChart.setAxisNameX('Month');
	myChart.setAxisNameY('Badges');
	myChart.setSize(500, 350);
	myChart.setTitle(name + ' Badges by Month');
	myChart.setTitleColor('#000000');
	myChart.setTitleFontSize(12);
	myChart.setBarColor('#33aa33');
	myChart.setBarOpacity(0.9);
	myChart.setBarValuesColor('#33aa33');
	myChart.draw();
}

function showUser1() {
	disable("user1Button");
	enable("user2Button");
	enable("bothButton");

	showEl("groupUser1", "inline-block");	
	hideEl("groupUser2");
}

function showUser2() {
	enable("user1Button");
	disable("user2Button");
	enable("bothButton");

	hideEl("groupUser1");	
	showEl("groupUser2", "inline-block");
}

function showBoth() {
	enable("user1Button");
	enable("user2Button");
	disable("bothButton");

	showEl("groupUser1", "inline-block");	
	showEl("groupUser2", "inline-block");
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
<form><label>Show:</label>
	<input id="user1Button" type="button" value="User1" onclick="showUser1();">
	<input id="user2Button" type="button" value="User2" onclick="showUser2();">
	<input id="bothButton" type="button" value="Side-by-Side" onclick="showBoth();">
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
		<li><b>Summary Charts</b></li>
		<li><a href="graph.php?u=<?php echo strToHTML($user);?>&u2=<?php echo strToHTML($user2);?>">Historical Graphs</a></li>
		<li><a href="badgerank.php?u=<?php echo strToHTML($user);?>">Badge Difficulty Ranking</a></li>
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
</div>

<div id="groupUser1">
	<!-- Recent History -->
    
	<div id="chart7"></div>
	<div id="text7"></div>
	<div id="chart8"></div>
	<div id="text8"></div>
	<div id="chart11"></div>
	<div id="text11"></div>
	<div id="chart12"></div>
	<div id="text12"></div>
	
	<!-- Aggregate Stats-->
	
	<div id="chart1"></div>
	<div id="chart2"></div>
	<div id="chart3"></div>
</div>

<div id="groupUser2">
	<!-- Recent History -->

	<div id="chart9"></div>
	<div id="text9"></div>
	<div id="chart10"></div>
	<div id="text10"></div>
	<div id="chart13"></div>
	<div id="text13"></div>
	<div id="chart14"></div>
	<div id="text14"></div>
	
	<!-- Aggregate Stats-->
		
	<div id="chart4"></div>
	<div id="chart5"></div>
	<div id="chart6"></div>
</div>

<script type="text/javascript">
// Normalize
badges.reverse();

// If only one name, put it in the first
if (!userName && userName2) {
	userName = userName2;
	userName2 = "";
	userBadges = userBadges2;
	userBadges2 = null;
}

// Fill the first missing user with Kongregate badges
if (!userName) {
	userBadges = badges;
} else if (!userName2) {
	userBadges2 = badges;
}

bucketize(userBadges, "chart1", "chart2", "chart3", userName ? userName + "'s" : kong);
if (userName) {
	bucketize(userBadges2, "chart4", "chart5", "chart6", userName2 ? userName2 + "'s" : kong);
}

var maxDb = [0];
var maxDp = [0];
var maxMb = [0];
var maxMp = [0];

var gDb1 = makeRecentDays(userBadges, 28, false, 0, 'chart7', 'text7', userName ? userName + "'s" : kong, maxDb);
var gDp1 = makeRecentDays(userBadges, 28, true, 0, 'chart8', 'text8', userName ? userName + "'s" : kong, maxDp);
var gMb1 = makeRecentMonths(userBadges, 12, false, 0, 'chart11', 'text11', userName ? userName + "'s" : kong, maxMb);
var gMp1 = makeRecentMonths(userBadges, 12, true, 0, 'chart12', 'text12', userName ? userName + "'s" : kong, maxMp);
if (userName) {
	var gDb2 = makeRecentDays(userBadges2, 28, false, 0, 'chart9', 'text9', userName2 ? userName2 + "'s" : kong, maxDb);
	var gDp2 = makeRecentDays(userBadges2, 28, true, 0, 'chart10', 'text10', userName2 ? userName2 + "'s" : kong, maxDp);
	var gMb2 = makeRecentMonths(userBadges2, 12, false, 0, 'chart13', 'text13', userName2 ? userName2 + "'s" : kong, maxMb);
	var gMp2 = makeRecentMonths(userBadges2, 12, true, 0, 'chart14', 'text14', userName2 ? userName2 + "'s" : kong, maxMp);
	gDb1.setIntervalEndY(maxDb[0]);
	gDb2.setIntervalEndY(maxDb[0]);
	gDp1.setIntervalEndY(maxDp[0]);
	gDp2.setIntervalEndY(maxDp[0]);
	gMb1.setIntervalEndY(maxMb[0]);
	gMb2.setIntervalEndY(maxMb[0]);
	gMp1.setIntervalEndY(maxMp[0]);
	gMp2.setIntervalEndY(maxMp[0]);
	gDb2.draw();
	gDp2.draw();
	gMb2.draw();
	gMp2.draw();
}
gDb1.draw();
gDp1.draw();
gMb1.draw();
gMp1.draw();

getEl("user1Button").value = userName ? userName : kong;
if (userName) {
	getEl("user2Button").value = userName2 ? userName2 : kong;
} else {
	hideEl("user2Button");
	hideEl("bothButton");
}
document.forms["names"].u.value = userName;
document.forms["names"].u2.value = userName2;

showUser1();
</script>

<p>Note: The points here are only from regular badges, and do not include points from referrals, ratings, challenges, quests, mobile, or badges of the day.</p>
<p>Note: All times are in your local time.</p>

<p>
<b>History:</b><br>
2012-08-26: Created new page for bar charts<br>
2012-11-22: Scaled the y-axes equally in Side-by-Side view for daily and monthly charts for better comparison.<br>
</p>

<p>
<b>Credits:</b><br>
Made by: <a href="http://zalbee.intricus.net/">zAlbee</a><br>
Badge Data: Kongregate badges.json<br>
Charts Library: <a href="http://dygraphs.com/">JS Charts</a><br>
<br>
Questions? <a href="mailto:zalbee@gmail.com?subject=Kongregate Badge Graphs">Email me</a>, post in <a href="http://www.kongregate.com/forums/1-kongregate/topics/201886-user-badge-graphs">this thread</a>, or <a href="http://zalbee.intricus.net/2012/01/kongregate-stats-and-graphs/">comment here</a> (no account required).
</p>

</body>
</html>