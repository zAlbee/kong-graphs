/* Honeypot trap for malware */
/* write(); document.write(); */
function DUMMY() {
	document.write("harmless");
	document.write("test2");
}

/**
 * Pretty Print
 */
function pp(x) {
  var s = "";
  for (var k in x) {
    s += k + ": " + x[k] + ", " ;
  }
  return s;
}

function println(s) {
	document.write(s + "<br>\n");
}

function getEl(id) {
	if (document.getElementById) return document.getElementById(id);
	else if (document.all) return document.all[id];
}

function showEl(s, type) {
	if (type) getEl(s).style.display = type;
	else getEl(s).style.display = "block";
}
function hideEl(s) {
	getEl(s).style.display = "none";
}
function disable(s) {
	getEl(s).disabled = true;
}
function enable(s) {
	getEl(s).disabled = false;
}

/**
 * Math
 */
function roundTo(num, dec) {
	var result = Math.round(num*Math.pow(10,dec))/Math.pow(10,dec);
	return result;
}

/**
 * String
 */

if (typeof String.prototype.startsWith != 'function') {
	String.prototype.startsWith = function (str){
		return this.lastIndexOf(str, 0) === 0;
	};
}

function addslashes(str) {
    // Escapes single quote, double quotes and backslash characters in a string with backslashes  
    // 
    // version: 1109.2015
    // discuss at: http://phpjs.org/functions/addslashes
    // +   original by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
    // +   improved by: Ates Goral (http://magnetiq.com)
    // +   improved by: marrtins
    // +   improved by: Nate
    // +   improved by: Onno Marsman
    // +   input by: Denny Wardhana
    // +   improved by: Brett Zamir (http://brett-zamir.me)
    // +   improved by: Oskar Larsson Högfeldt (http://oskar-lh.name/)
    // *     example 1: addslashes("kevin's birthday");
    // *     returns 1: 'kevin\'s birthday'
    return (str + '').replace(/[\\"']/g, '\\$&').replace(/\u0000/g, '\\0');
}

/*
Escape the following characters with HTML entity encoding to prevent switching into any execution context, such as script, style, or event handlers. Using hex entities is recommended in the spec. In addition to the 5 characters significant in XML (&, <, >, ", '), the forward slash is included as it helps to end an HTML entity.
 & --> &amp;
 < --> &lt;
 > --> &gt;
 " --> &quot;
 ' --> &#x27;     &apos; is not recommended
 / --> &#x2F;     forward slash is included as it helps end an HTML entity
 */
function strToHTML(str) {
	return (str + '').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;').replace(/'/g,'&#x27;').replace(/\//g,'&#x2F;');
}