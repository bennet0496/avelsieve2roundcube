<?php
/**
 * User-friendly interface to SIEVE server-side mail filtering.
 * Plugin for Squirrelmail 1.4+
 *
 * Functions for getting existing rules out from an avelsieve script.
 *
 * Licensed under the GNU GPL. For full terms see the file COPYING that came
 * with the Squirrelmail distribution.
 *
 * @version $Id: sieve_getrule.inc.php 1020 2009-05-13 14:10:13Z avel $
 * @author Alexandros Vellis <avel@users.sourceforge.net>
 * @copyright 2004-2007 The SquirrelMail Project Team, Alexandros Vellis
 * @package plugins
 * @subpackage avelsieve
 */

/**
 * Decode data from an existing SIEVE script. Data are stored as metadata (PHP
 * serialized variables). No actual parsing is performed.
 *
 * @param str $sievescript A SIEVE script to get information from
 * @param array $scriptinfo Store Script Information (creation date,
 * modification date, avelsieve version) here
 *
 * @return array Rules array
 */
function avelsieve_extract_rules($sievescript, &$scriptinfo) {
    /* Get avelsieve script version info, if it exists. */
    $regexp = '/AVELSIEVE_VERSION.+\n/sU';
    if (preg_match($regexp, $sievescript, $verstrings) == 1) {
        $tempstr = substr(trim($verstrings[0]), 17);
        $scriptinfo['version'] = unserialize(base64_decode($tempstr));
    } else {
        $scriptinfo['version'] = array('old' => true);
    }

    /* Creation date */
    $regexp = '/AVELSIEVE_CREATED.+\n/sU';
    if (preg_match($regexp, $sievescript, $verstrings) == 1) {
        $scriptinfo['created'] = substr(trim($verstrings[0]), 17);
    }
    
    /* Last modification date */
    $regexp = '/AVELSIEVE_MODIFIED.+\n/sU';
    if (preg_match($regexp, $sievescript, $verstrings) == 1) {
        $scriptinfo['modified'] = substr(trim($verstrings[0]), 18);
    }

    /* Only decode script if it was created from avelsieve 0.9.6 +.
     * Backward compatibility: If version==0.9.5 or 0.9.4 or not defined,
     * don't decode it! */

    if( (!isset($scriptinfo['version']) ) ||
        (
         isset($scriptinfo['version']['major']) &&
         $scriptinfo['version']['major'] == 0 &&
         $scriptinfo['version']['minor'] == 9 &&
         ($scriptinfo['version']['release'] == 4 || $scriptinfo['version']['release'] == 5) 
        ) ||
        (isset($scriptinfo['version']['old']) && ($scriptinfo['version']['old'] == true ))
    ) {
        if(AVELSIEVE_DEBUG == 1) {
                print "Notice: Backward compatibility mode - not decoding script.";
        }
    } else {
        $sievescript = DO_Sieve::decode_script($sievescript);
    }

    /* Get Rules */
    /*
    $regexp = "/START_SIEVE_RULE.+END_SIEVE_RULE/sU";
    if (preg_match_all($regexp,$sievescript,$rulestrings)) {
        for($i=0; $i<sizeof($rulestrings[0]); $i++) {
            // remove the last 14 characters from a string 
            $rulestrings[0][$i] = substr($rulestrings[0][$i], 0, -14); 
            // remove the first 16 characters from a string 
            $rulestrings[0][$i] = substr($rulestrings[0][$i], 16);

            $rulearray[$i] = unserialize(base64_decode(urldecode($rulestrings[0][$i])));
        }
     */

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
        return array();
    }
    
    /* Migrate for avelsieve <= 1.9.3 to 1.9.4+-style rules */
    if( (!isset($scriptinfo['version']) ) ||
        (
         isset($scriptinfo['version']['major']) &&
         $scriptinfo['version']['major'] == 0
        ) ||
        (
         isset($scriptinfo['version']['major']) &&
         $scriptinfo['version']['major'] == 1 &&
         $scriptinfo['version']['minor'] <= 9 &&
         $scriptinfo['version']['release'] <= 3 
        ) ||
        (isset($scriptinfo['version']['old']) && ($scriptinfo['version']['old'] == true ))
    ) {
        if(AVELSIEVE_DEBUG == 1) {
               print "Notice: Backward compatibility mode - transitioning from <=1.9.3 to 1.9.4+";
        }
        avelsieve_migrate_1_9_4($rulearray);
    }

    return $rulearray;
}

/**
 * Migration of avelsieve rules from avelsieve <= 1.9.3 to 1.9.4+.
 * Changes the condition (if part) to the new 'cond' schema for more complex
 * conditions and adds support for envelope, body etc.
 *
 * All the rules with type = 2, 3 or 4 will use type = 1 from now on.
 *
 * @param $rulearray The array of rules which will be modified.
 * @return void
 */
function avelsieve_migrate_1_9_4(&$rulearray) {
    foreach($rulearray as $no => $r) {
        if($r['type'] == '2') { // header
            $rulearray[$no]['type'] = 1;
            for($i=0;$i<sizeof($rulearray[$no]['header']);$i++) {
                $rulearray[$no]['cond'][$i]['type'] = 'header';
                $rulearray[$no]['cond'][$i]['header'] = $r['header'][$i];
                $rulearray[$no]['cond'][$i]['matchtype'] = $r['matchtype'][$i];
                $rulearray[$no]['cond'][$i]['headermatch'] = $r['headermatch'][$i];
            }
            unset($rulearray[$no]['header']);
            unset($rulearray[$no]['matchtype']);
            unset($rulearray[$no]['headermatch']);
        
        } elseif($r['type'] == '3') { // size
            $rulearray[$no]['type'] = 1;
            $rulearray[$no]['cond'][0]['type'] = 'size';
            $rulearray[$no]['cond'][0]['sizerel'] = $r['sizerel'];
            $rulearray[$no]['cond'][0]['sizeamount'] = $r['sizeamount'];
            $rulearray[$no]['cond'][0]['sizeunit'] = $r['sizeunit'];
            unset($rulearray[$no]['sizerel']);
            unset($rulearray[$no]['sizeamount']);
            unset($rulearray[$no]['sizeunit']);


        } elseif($r['type'] == '4') { // all
            $rulearray[$no]['type'] = 1;
            $rulearray[$no]['cond'][0]['type'] = 'all';
            
        } elseif($rulearray[$no]['type'] == '10') { // spam
            /* TODO */
        }
    }
}

/**
 * Consistency check between folders referred to in filtering rules and folders
 * that exist to the user.
 *
 * @param array $boxes
 * @param array $rules
 * @return array of inconsistent folders
 */
function avelsieve_folder_consistency_check(&$boxes, &$rules) {
    global $plugins;

    /* Gather some exceptions for the consistency check. */
    if(in_array('junkfolder', $plugins)) {
        global $junkfolder_user, $junkfolder_autocreate;
        if(!empty($junkfolder_user) && $junkfolder_autocreate) {
            $exceptions[] = $junkfolder_user;
        }
    }

    $inconsistent_folders = array();

    for($i=0; $i<sizeof($boxes); $i++) {
        $boxes_index[$i] = $boxes[$i]['unformatted'];
    }
    for($i=0; $i<sizeof($rules); $i++) {
        if( ($rules[$i]['type'] == 1 && $rules[$i]['action'] == 5 && isset($rules[$i]['folder'])) &&
            (!isset($rules[$i]['disabled']) || (isset($rules[$i]['disabled']) && !$rules[$i]['disabled']))
          ) {
            if(in_array($rules[$i]['folder'], $boxes_index)) {
                // Check passed
            } else {
                // Check failed
                $inconsistent_folders[] = $rules[$i]['folder'];
                /*
                // Would the rule number be of any use to someone?
                $inconsistent_folders[] = array(
                    'rule' => $i,
                    'folder' => $rules[$i]['folder']
                );
                */
            }
        }

    }
    return $inconsistent_folders;
}

/**
 * Go through rules and find a vacation action.
 *
 * @param array $rules
 * @return array of the indexes of rules, or an empty array if no vacation 
 *  actions were found.
 */
function avelsieve_vacation_check(&$rules) {
    $vacation_rules = array();
    for($i=0; $i<sizeof($rules); $i++) {
        if(isset($rules[$i]['action']) && $rules[$i]['action'] == '6' && !isset($rules[$i]['disabled'])) {
            $vacation_rules[] = $i;
        }
    }
    return $vacation_rules;
}

