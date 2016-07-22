<?php
//ini_set('display_errors', '0');
//error_reporting(0);
define('SM_PATH', dirname(__FILE__).'/');
define('HERE', dirname(__FILE__));

require_once HERE . '/include/sieve_buildrule.inc.php';
require_once HERE . '/include/support.inc.php';

//sieve script von STDIN
$sievescript = file_get_contents("php://stdin");

//RuleExtractor (avelsieve_extract_rules) aus includes/sieve_getrule.inc.php
 $regexp = "/START_SIEVE_RULE([^#]+|\s+\n(.+)#)END_SIEVE_RULE/smU";
 if (preg_match_all($regexp,$sievescript,$rulestrings)) {
	 for($i=0; $i<sizeof($rulestrings[1]); $i++) {
		 if (empty($rulestrings[2][$i])) { 
			 $rulearray[$i] = unserialize(base64_decode(urldecode($rulestrings[1][$i])));
		 }else{
			 $rulearray[$i] = array ( 'type' => 13, 'code' => substr($rulestrings[2][$i], 0, -1));
		 }
	 }
 } else {
	 /* No rules; return an empty array */
	 //return array();
 }

//"require"-Zeile Quick'n'Dirty. (In der hoffung das alle Datein das gleiche Format haben)
echo explode(PHP_EOL, $sievescript)[7].PHP_EOL;

//Alle geparsten Regeln Konvertiert mit leicht modifizierter avelsive Funktion
foreach( $rulearray as $rule ){
	echo "# rule:[";
	echo str_replace("DISABLED","",strip_tags(
		str_replace(
			'<br/>', ' ', str_replace(
				'</td><td align="right">', '-> ', strip_tags(
					makesinglerule($rule, 'terse'),'<td><br>'
				)
			)
		)
	))."]".PHP_EOL ;
	echo makesinglerule($rule, 'rule').PHP_EOL ;
}

