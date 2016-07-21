<?php
/**
 * User-friendly interface to SIEVE server-side mail filtering.
 * Plugin for Squirrelmail 1.4+
 *
 * These are just my own wrapper functions around sieve-php.lib.php, with error
 * handling et al.
 *
 * Licensed under the GNU GPL. For full terms see the file COPYING that came
 * with the Squirrelmail distribution.
 *
 * @version $Id: managesieve_wrapper.inc.php 1020 2009-05-13 14:10:13Z avel $
 * @author Alexandros Vellis <avel@users.sourceforge.net>
 * @copyright 2004-2007 The SquirrelMail Project Team, Alexandros Vellis
 * @package plugins
 * @subpackage avelsieve
 * @obsolete
 */

/** Includes */
include_once(SM_PATH . 'plugins/avelsieve/include/managesieve.lib.php');
include_once(SM_PATH . 'plugins/avelsieve/include/support.inc.php');
include_once(SM_PATH . 'plugins/avelsieve/config/config.php');

/**
 * This function initializes the avelsieve environment. Basically, it makes
 * sure that there is a valid sieve_capability array.
 *
 * An instance of the $sieve handle is placed in the global scope.
 *
 * Important: If a valid rules array is needed, then avelsieve_getrules()
 * should be used.
 *
 * @param object $sieve Sieve class connection handler.
 * @return void
 * @obsolete
 */
function avelsieve_initialize(&$sieve) {
    sqgetGlobalVar('sieve_capabilities', $sieve_capabilities, SQ_SESSION);
    sqgetGlobalVar('rules', $rules, SQ_SESSION);

    if(!is_object($sieve)) {
        sqgetGlobalVar('key', $key, SQ_COOKIE);
        sqgetGlobalVar('onetimepad', $onetimepad, SQ_SESSION);
        sqgetGlobalVar('authz', $authz, SQ_SESSION);
        global $imapServerAddress, $username, $avelsieve_imapproxymode, $avelsieve_cyrusadmins_map, $sieveport,
            $avelsieve_imapproxyserv, $sieve_preferred_sasl_mech;

        /* Need the cleartext password to login to timsieved */
        $acctpass = OneTimePadDecrypt($key, $onetimepad);

        if(isset($authz)) {
            $imap_server =  sqimap_get_user_server ($imapServerAddress, $authz);
        } else {
            $imap_server =  sqimap_get_user_server ($imapServerAddress, $username);
    
            if ($avelsieve_imapproxymode == true) { /* Need to do mapping so as to connect directly to server */
                $imap_server = $avelsieve_imapproxyserv[$imap_server];
            }
        }
        if(isset($authz)) {
            if(isset($avelsieve_cyrusadmins_map[$username])) {
                $bind_username = $avelsieve_cyrusadmins_map[$username];
            } else {
                $bind_username = $username;
            }
             $sieve=new sieve($imap_server, $sieveport, $bind_username, $acctpass, $authz, $sieve_preferred_sasl_mech);
        } else {
            $sieve=new sieve($imap_server, $sieveport, $username, $acctpass, $username, $sieve_preferred_sasl_mech);
        }
        avelsieve_login($sieve);
    }
}

/**
 * Login to SIEVE server. Also saves the capabilities in Session.
 *
 * @param object $sieve Sieve class connection handler.
 * @return boolean
 * @obsolete
 */
function avelsieve_login(&$sieve) {
    global $sieve_capabilities, $imapServerAddress, $sieve_loggedin;
    if(is_object($sieve) && isset($sieve_loggedin)) {
        return true;
    }
    if ($sieve->sieve_login()){ /* User has logged on */
        if(!isset($sieve_capabilities)) {
            $sieve_capabilities = $sieve->sieve_get_capability();
             $_SESSION['sieve_capabilities'] = $sieve_capabilities;
        }
        $sieve_loggedin = true;
        return true;
    } else {
        $errormsg = _("Could not log on to timsieved daemon on your IMAP server") . 
                " " . $sieve->host.':'.$sieve->port.'.<br/>';
        if(!empty($sieve->error)) {
            $errormsg .= _("Error Encountered:") . ' ' . $sieve->error . '</br>';
        }
        $errormsg .= _("Please contact your administrator.");

        if(AVELSIEVE_DEBUG == 1) {
            print "<pre>(Debug Mode). Login failed. Capabilities:\n";
            print_r($sieve_capabilities);
            if(!empty($sieve->error)) {
                print "\nError Message returned:\n";
                print_r($sieve->error);
            }
            print '</pre>';
        }
        print_errormsg($errormsg);
        exit;
    }
}

/**
 * Get scripts list from SIEVE server.
 * @obsolete
 */
function avelsieve_listscripts($sieve) {
    $scripts = array();
    if($sieve->sieve_listscripts()) {
        if(is_array($sieve->response)){
            $i = 0;
            foreach($sieve->response as $line){
                $scripts[$i] = $line;
                $i++;
            }
        }
    }
    return $scripts;
}

/**
 * Get rules from specified script of Sieve server
 *
 * @param object $sieve Sieve class connection handler.
 * @param string $scriptname
 * @param array $scriptinfo
 * @return array
 * @obsolete
 */
function avelsieve_getrules(&$sieve, $scriptname = 'phpscript', &$rules, &$scriptinfo) {
    global $imapServerAddress;
    sqgetGlobalVar('sieve_capabilities', $sieve_capabilities, SQ_SESSION);
    
    $rules = array();
    $scriptinfo = array();

    if(!isset($sieve_capabilities)) {
        avelsieve_initialize($sieve);
    }
    if(!is_object($sieve)) {
        avelsieve_login($sieve); 
    }

    $scripts = avelsieve_listscripts($sieve);

    if(!in_array($scriptname, $scripts)) {
        /* No avelsieve script. */
        return false;
    }

    /* Get actual script from Sieve server. */
    unset($sieve->response);
    $sievescript = '';
    if($sieve->sieve_getscript($scriptname)){
        foreach($sieve->response as $line){
            $sievescript .= $line;
        }
    } else {
        $prev = bindtextdomain ('avelsieve', SM_PATH . 'plugins/avelsieve/locale');
        textdomain ('avelsieve');
        $errormsg = _("Could not get SIEVE script from your IMAP server");
        $errormsg .= " " . $imapServerAddress.".<br />";
        
        if(!empty($sieve->error)) {
            $errormsg .= _("Error Encountered:") . ' ' . $sieve->error . '</br>';
            $errormsg .= _("Please contact your administrator.");
            print_errormsg($errormsg);
            exit;
        }
    }

    /* Extract rules from $sievescript. */
    $rules = avelsieve_extract_rules($sievescript, $scriptinfo);
    return true;
}

/**
 * Upload script
 *
 * @param object $sieve Sieve class connection handler.
 * @param string $newscript The SIEVE script to be uploaded
 * @param string $scriptname Name of script
 * @return true on success, false upon failure
 * @obsolete
 */
function avelsieve_upload_script (&$sieve, $newscript, $scriptname = 'phpscript') {
    global $imapServerAddress;
    if(isset($sieve->error_raw)) {
        unset($sieve->error_raw);
    }

    if($sieve->sieve_sendscript($scriptname, stripslashes($newscript))) {
        if(!($sieve->sieve_setactivescript($scriptname))){
            /* Just to be safe. */
            $errormsg = _("Could not set active script on your IMAP server");
            $errormsg .= " " . $imapServerAddress.".<br />";
            $errormsg .= _("Please contact your administrator.");
            print_errormsg($errormsg);
            return false;
        }
        return true;

    } else {
        $errormsg = '<p>';
        $errormsg .= _("Unable to load script to server.");
        $errormsg .= '</p>';

        if(isset($sieve->error_raw)) {
            $errormsg .= '<p>';
            $errormsg .= _("Server responded with:");
            $errormsg .= '<br />';
            
            if (is_array($sieve->error_raw)) {
                foreach($sieve->error_raw as $error_raw) {
                    $errormsg .= $error_raw . "<br />";
                }
            } else {
                $errormsg .= $sieve->error_raw . "<br />";
            }
            $errormsg .= _("Please contact your administrator.");
        
            /* The following serves for viewing the script that
             * tried to be uploaded, for debugging purposes. */
            if(AVELSIEVE_DEBUG == 1) {
                $errormsg .= '<br />(Debug mode)
                <strong>avelsieve bug</strong> <br /> Script
                that probably is buggy follows.<br /> Please
                copy/paste it, together with the error message above, and email it to <a
                href=\"mailto:avel@users.sourceforge.net\">avel@users.sourceforge.net</a>.
                <br /><br />
                <div style="font-size:8px;"><pre>' . $newscript. "</pre></div>";
            }
        }
        print_errormsg($errormsg);
        return false;
    }
}

/**
 * Deletes a script on SIEVE server.
 *
 * @param object $sieve Sieve class connection handler.
 * @param string $script 
 * @return true on success, false upon failure
 * @obsolete
 */
function avelsieve_delete_script (&$sieve, $script = 'phpscript') {
    if(empty($script)) {
        return false;
    }
    if($sieve->sieve_deletescript($script)) {
        return true;
    } else {
        
        $errormsg = sprintf( _("Could not delete script from server %s."), $sieve->host.':'.$sieve->port) .
            '<br/>';
        if(!empty($sieve->error)) {
            $errormsg .= _("Error Encountered:") . ' ' . $sieve->error . '</br>';
        }
        $errormsg .= _("Please contact your administrator.");
        print_errormsg($errormsg);

        /*
        if(is_array($sieve->error_raw)) {
            foreach($sieve->error_raw as $error_raw)
                print $error_raw."<br>";
        } else {
            print $sieve->error_raw."<br>";
        }
        */
        return false;
    }
}

/**
 * Check if avelsieve capability exists.
 *
 * avelsieve capability is defined as SIEVE capability, NOT'd with
 * $disable_avelsieve_capabilities from configuration file.
 *
 * $disable_avelsieve_capabilities specifies capabilities to disable. If you
 * would like to force avelsieve not to display certain features, even though
 * there _is_ a capability for them by Cyrus/timsieved, you should specify
 * these here. For instance, if you would like to disable the notify extension,
 * even though timsieved advertises it, you should add 'notify' in this array:
 * $force_disable_avelsieve_capability = array("notify");. This will still
 * leave the defined feature on, and if the user can upload her own scripts
 * then she can use that feature; this option just disables the GUI of it.
 *
 * @param $cap capability to check for
 * @return boolean true if capability exists, false if it does not exist
 * @obsolete
 */
function avelsieve_capability_exists ($cap) {

    global $disable_avelsieve_capabilities, $sieve_capabilities;
    
    if(array_key_exists($cap, $sieve_capabilities)) {
        if(!in_array($cap, $disable_avelsieve_capabilities)) {
            return true;
        }
    }
    return false;
}

/**
 * Encode script from user's charset to UTF-8.
 *
 * @param string $script
 * @return string
 */
function avelsieve_encode_script($script) {

    global $languages, $squirrelmail_language, $default_charset;

    /* change $default_charset to user's charset (THANKS Tomas) */
    set_my_charset();

    if(strtolower($default_charset) == 'utf-8') {
        // No need to convert.
        return $script;
    
    } elseif(function_exists('mb_convert_encoding') && function_exists('sqimap_mb_convert_encoding')) {
        // sqimap_mb_convert_encoding() returns '' if mb_convert_encoding() doesn't exist!
        $utf8_s = sqimap_mb_convert_encoding($script, 'UTF-8', $default_charset, $default_charset);
        if(empty($utf8_s)) {
            return $script;
        } else {
            return $utf8_s;
        }

    } elseif(function_exists('mb_convert_encoding')) {
        // Squirrelmail 1.4.0 ?

        if ( stristr($default_charset, 'iso-8859-') ||
          stristr($default_charset, 'utf-8') || 
          stristr($default_charset, 'iso-2022-jp') ) {
            return mb_convert_encoding($script, "UTF-8", $default_charset);
        }

    } elseif(function_exists('recode_string')) {
        return recode_string("$default_charset..UTF-8", $script);

    } elseif(function_exists('iconv')) {
        return iconv($default_charset, 'UTF-8', $script);
    }

    return $script;
}


/**
 * Decode script from UTF8 to user's charset.
 *
 * @param string $script
 * @return string
 */
function avelsieve_decode_script($script) {

    global $languages, $squirrelmail_language, $default_charset;

    /* change $default_charset to user's charset (THANKS Tomas) */
    set_my_charset();

    if(strtolower($default_charset) == 'utf-8') {
        // No need to convert.
        return $script;
    
    } elseif(function_exists('mb_convert_encoding') && function_exists('sqimap_mb_convert_encoding')) {
        // sqimap_mb_convert_encoding() returns '' if mb_convert_encoding() doesn't exist!
        $un_utf8_s = sqimap_mb_convert_encoding($script, $default_charset, "UTF-8", $default_charset);
        if(empty($un_utf8_s)) {
            return $script;
        } else {
            return $un_utf8_s;
        }

    } elseif(function_exists('mb_convert_encoding')) {
        /* Squirrelmail 1.4.0 ? */

        if ( stristr($default_charset, 'iso-8859-') ||
          stristr($default_charset, 'utf-8') || 
          stristr($default_charset, 'iso-2022-jp') ) {
            return mb_convert_encoding($script, $default_charset, "UTF-8");
        }

    } elseif(function_exists('recode_string')) {
        return recode_string("UTF-8..$default_charset", $script);

    } elseif(function_exists('iconv')) {
        return iconv('UTF-8', $default_charset, $script);
    }
    return $script;
}

