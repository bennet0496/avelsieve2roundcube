<?php
//ini_set('display_errors', '0');
//error_reporting(0);
define('SM_PATH', './');

require_once __DIR__ . '/include/sieve_buildrule.inc.php';
require_once __DIR__ . '/include/support.inc.php';
require_once __DIR__ . '/include/avelsieve_action_imapflags.class.php';


$sievescript = file_get_contents("php://stdin");

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

echo explode(PHP_EOL, $sievescript)[7].PHP_EOL;
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

//print_r($rulearray);
