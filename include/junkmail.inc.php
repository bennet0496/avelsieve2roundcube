<?php
/**
 * User-friendly interface to SIEVE server-side mail filtering.
 * Plugin for Squirrelmail 1.4+
 *
 * Licensed under the GNU GPL. For full terms see the file COPYING that came
 * with the Squirrelmail distribution.
 *
 * Also view plugins/README.plugins for more information.
 *
 * This file contains special functions related to junk mail options.
 *
 * @version $Id: junkmail.inc.php 1020 2009-05-13 14:10:13Z avel $
 * @author Alexandros Vellis <avel@users.sourceforge.net>
 * @copyright 2004-2007 Alexandros Vellis
 * @package plugins
 * @subpackage avelsieve
 */

/** Includes */
include_once(SM_PATH . 'plugins/avelsieve/config/config.php');
include_once(SM_PATH . 'plugins/avelsieve/include/html_main.inc.php');

/**
 * Informational message and link to Junk Mail options, from Junk Folder 
 * Screen.
 *
 * @return void
 */
function junkmail_right_main_do() {
    global $PHP_SELF;
    
    bindtextdomain ('avelsieve', SM_PATH . 'plugins/avelsieve/locale');
    textdomain ('avelsieve');
    
    require_once(SM_PATH . 'plugins/avelsieve/include/constants.inc.php');

    sqgetGlobalVar('rules', $rules, SQ_SESSION);
    sqgetGlobalVar('haschanged', $avelsieve_changed, SQ_SESSION);

    /* Initialization of the Sieve storage backend class, for the following
     * two actions */
    if (!isset($rules) || isset($avelsieve_changed)) {
        global $avelsieve_backend;
        $backend_class_name = 'DO_Sieve_'.$avelsieve_backend;
        include_once(SM_PATH . 'plugins/avelsieve/include/sieve.inc.php');
        $s = new $backend_class_name;
        $s->init();
        $s->login();
    }

    /* If this page is called before table.php is ever shown, then we have to make
    * the current filtering rules available in the Session. This will happen when
    * a user clicks either:
    * i) creation of a rule from the message commands (while viewing a message)
    * ii) creation of a rule from some search criteria.
    */
    if (!isset($rules)) {
        /* Actually get the script 'phpscript' (hardcoded ATM). */
        if($s->load('phpscript', $rules, $scriptinfo)) {
            $_SESSION['rules'] = $rules;
            $_SESSION['scriptinfo'] = $scriptinfo;
        }
        $s->logout();
    }

    /**
     * Replicate the script-saving code from table.php.
     * This is for when we get back in this page. The rules have
     * to be saved to server then.
     */
    if(isset($avelsieve_changed) && $avelsieve_changed == true) {
        $newscript = makesieverule($rules);
        $s->save($newscript, 'phpscript');
        avelsieve_spam_highlight_update($rules);
        sqsession_unregister('haschanged');
        $s->logout();
    }

    $rule_exists = false;
    for($i=0; $i<sizeof($rules); $i++) {
        if(in_array($rules[$i]['type'], array('11'))) {
            $rule_exists = true;
            if(!isset($rules[$i]['disabled'])) {
                $rule_enabled = true;
            } elseif ( $rules[$i]['disabled'] == 1) {
                $rule_enabled = false;
            } 
            $junkmailDays = $rules[$i]['junkmail_days'];
            break;
        }
    }
    
    $ht = new avelsieve_html;
    $ht->useimages = true; // FIXME

    echo $ht->all_sections_start() . $ht->section_start(
            ($ht->useimages == true ? '<img src="'.$ht->iconuri.'information.png" alt="(i)" /> ' : '')
            .   _("Junk Folder Information"))          
            . '<p>'.
            ($ht->useimages == true ? '<img src="'.$ht->iconuri.'email_error.png" alt="(i)" /> ' : '').
            _("Messages in this folder have been identified as SPAM / Junk.");
    
    if(!$rule_exists || !$rule_enabled) {
        echo '<br/>' .
             ($ht->useimages == true? '<img src="'.$ht->iconuri.'exclamation.png" alt="(!)" /> ' : '') .
             '<strong>' . sprintf( _("Note: Junk Mail is currently not enabled. Select &quot;%s&quot; to enable it."),
                '<em>'._("Edit Junk Mail Options...").'</em>' ) . '</strong>';
    } else {
        echo ' ' . sprintf( _("Any messages older than %s days are automatically deleted."), $junkmailDays);
    }
    echo '</p>';

    echo '<p style="text-align:center">'.
        '<strong><a href="../plugins/avelsieve/edit.php?addnew=1&amp;type=11&amp;referrerUrl='.rawurlencode($PHP_SELF).'">'.
        _("Edit Junk Mail Options...") . '</a></strong></p>';

    
    $avelsieveMessages = $ht->retrieve_avelsieve_messages();
    if($avelsieveMessages) {
        echo '<p style="text-align:center;">' .$avelsieveMessages . '</p>';
        $ht->clear_avelsieve_messages();
    }

    echo $ht->section_end() . $ht->all_sections_end();
    
    bindtextdomain('squirrelmail', SM_PATH . 'locale');
    textdomain('squirrelmail');
}

/**
 * Informational message and link to Junk Mail options, from Folders Screen 
 * (folders.php).
 *
 * TODO - Implement
 *
 * @return void
 */
function junkmail_folders_do() {
}
   

/**
 * Ask LDAP Server's Sendmail configuration for configured RBLs.
 *
 * The global variable $ldap_server from Squirrelmail's ldap server
 * configuration is used to determine if this feature will be enabled.
 * 
 * *** Advanced Administrators Option *** (Needs custom configuration
 * in LDAP server and possible tweaking of this function).
 *
 * @return RBLs structure, or false if no such configuration present. Structure
 * looks like this:
 * Array(
 *   [0] => Array
 *       (
 *           [host] => relays.ordb.org
 *           [name] => Open Relay DataBase
 *           [serverweight] => 50
 *       )
 */
function avelsieve_askldapforrbls() {
        
    /* If they were retrieved before, do not ask LDAP again. */
    if(isset($_SESSION['avelsieve_spamrbls_ldap'])) {
        return($_SESSION['avelsieve_spamrbls_ldap']);
    }

    global $ldap_server;

    foreach($ldap_server as $ldapno=>$info) {
        if(isset($info['mtarblspamfilter'])) {
            $mtarblspamfilter = $info['mtarblspamfilter'];
            $ls = $ldapno;
            break;
        }
    }
        
    if(!isset($mtarblspamfilter)) {
        return false;
    }

    if(!($ldap = ldap_connect($ldap_server[$ls]['host']))) {
        print "Could not connect to LDAP!";
        return false;
    }

    ldap_set_option($ldap, LDAP_OPT_PROTOCOL_VERSION, 3); 

    if(isset($ldap_server[$ls]['binddn'])) {
        $bind_result = ldap_bind($ldap, $ldap_server[$ls]['binddn'], $ldap_server[$ls]['bindpw']);
        if (!$bind_result) {
            print "Error while binding to LDAP server";
            return false;
        }
    }
        
    if (!($search_result = ldap_search($ldap, $ldap_server[$ls]['mtarblspambase'],
        $ldap_server[$ls]['mtarblspamfilter']))) {
        print "Failed to search for SPAM RBLs.";
        return false;
    }

    $info = ldap_get_entries($ldap, $search_result);

    $spamrbls = array();
    $sp_index = 0;
    
    for($j=0; $j<$info['count']; $j++) {

    if(isset($info[$j]['sendmailmtaclassname'])) {
        $spamrule_temp = array();
        for($i=0; $i<$info[$j]['sendmailmtaclassvalue']['count']; $i++) {
            $spamrule_temp[] =  $info[$j]['sendmailmtaclassvalue'][$i];
        }
        $spamrule_temp = str_replace('<', '', $spamrule_temp);
        $spamrule_temp = str_replace('>', '', $spamrule_temp);
        
        $temp=array();
        for($i=0; $i<sizeof($spamrule_temp); $i++) {
            $temp[$i] = explode('@', $spamrule_temp[$i]);
        }
        $temp2 = array();
        for($i=0; $i<sizeof($temp); $i++) {
            $temp2[$i] = explode(':', $temp[$i][0]);
        }
        for($i=0; $i<sizeof($temp); $i++) {
            $spamrbls[$sp_index]['host'] = $temp[$i][1];
            $spamrbls[$sp_index]['name'] = $temp2[$i][0];
            $spamrbls[$sp_index]['test'] = str_replace(' ', '.', $temp2[$i][0]);
            $spamrbls[$sp_index]['marker'] = $temp2[$i][1];
            $sp_index++;
        }
        
        /* TODO: Replace explode() with one smart regexp */
    }
    }

    for($j=0; $j<$info['count']; $j++) {
        if(isset($info[$j]['sendmailmtaclassname']) &&
           $info[$j]['sendmailmtaclassname'][0] == 'SpamForged') {
    
            $no = sizeof($spamrbls);
            $spamrbls[$no]['name'] = _("Test for Forged Header");
            $spamrbls[$no]['test'] = 'FORGED';
            $spamrbls[$no]['marker'] = $info[$j]['sendmailmtaclassvalue'][0];
        }
    }

    /* Cache into session */
    $_SESSION['avelsieve_spamrbls_ldap'] = $spamrbls;
    
    return($spamrbls);
}

/**
 * Create or update an entry in the squirrelmail highlight list, when there is
 * a spam rule.
 *
 * Note: this function requires a patch in functions/imap_messages.php, which at
 * the moment is not releasable. Perhaps two new plugin hooks should be in
 * place in order to support that.
 *
 * @param array $rules
 * @return void
 */
function avelsieve_spam_highlight_update(&$rules) {
    global $data_dir, $username, $color, $avelsieve_spam_highlight_enable;
    if(!isset($avelsieve_spam_highlight_enable) ||
      (isset($avelsieve_spam_highlight_enable) && !$avelsieve_spam_highlight_enable)) {
        return;
    }

    /* TODO: Probably move these arguments to configuration file */
    $avelsieve_hili_name = 'SPAM';
    $avelsieve_hili_color = $color[3];
    $avelsieve_hili_value = ';';
    $avelsieve_hili_match_type = 'x-spam-tests';

    $hili=getPref($data_dir, $username, 'hililist', '');
    $hilight = unserialize($hili);

    $hilight_exists = false;
    foreach($hilight as $h) {
        if($h['name'] == 'SPAM') {
            $hilight_exists = true;
        }
    }
    
    $rule_exists = false;
    for($i=0; $i<sizeof($rules); $i++) {
        if(in_array($rules[$i]['type'], array('10', '11'))) {
            $rule_exists = true;
        }
    }
        
    if($rule_exists) {
        if(!$hilight_exists) {
            $hilight[] = array(
                'name' => $avelsieve_hili_name,
                'color' => $avelsieve_hili_color,
                'value' => $avelsieve_hili_value,
                'match_type' => $avelsieve_hili_match_type
            );
            setPref($data_dir, $username, 'hililist', serialize($hilight));
        }
    } else {
        if($hilight_exists) {
            /* Here we could remove the highlight rule, but I guess it won't
             * hurt leaving it in there. */
        }
    }
}
 
/**
 * JunkPrune Update: LDAPuserdata backend. (Saves the days number in LDAP attribute).
 *
 * @param string $username
 * @param int $junkFolderDays
 * @return boolean
 */
function avelsieve_junkprune_ldapuserdata_update($username, $junkFolderDays) {
    global $plugins, $data_dir;
    if(in_array('ldapuserdata', $plugins)) {
        if($_SESSION['ldap_prefs_cache']['junkprune'] != $junkFolderDays) {
            setPref($data_dir, $username, 'junkprune', $junkFolderDays);
            ldapuserdata_flush();
            return true;
        }
        return false;
    }
}

