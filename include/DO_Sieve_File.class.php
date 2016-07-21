<?php
/**
 * User-friendly interface to SIEVE server-side mail filtering.
 * Plugin for Squirrelmail 1.4+
 *
 * Licensed under the GNU GPL. For full terms see the file COPYING that came
 * with the Squirrelmail distribution.
 *
 * @version $Id: DO_Sieve_File.class.php 1020 2009-05-13 14:10:13Z avel $
 * @author Alexandros Vellis <avel@users.sourceforge.net>
 * @copyright 2004-2007 Alexandros Vellis
 * @package plugins
 * @subpackage avelsieve
 */

/**
 * Skeleton for a file-based backend of Sieve scripts storage.
 */
class DO_Sieve_File extends DO_Sieve {
    /**
     * Class Constructor
     * FIXME
     */
    function DO_Sieve_File() {
        sqgetGlobalVar('sieve_capabilities', $sieve_capabilities, SQ_SESSION);
        sqgetGlobalVar('rules', $rules, SQ_SESSION);
        $this->capabilities = $sieve_capabilities;
        $this->rules = $rules;
    }

    /**
     * This function initializes the avelsieve environment. Basically, it makes
     * sure that there is a valid sieve_capability array.
     *
     * @return void
     */
    function init() {
    }

    /**
     * Login to SIEVE server. Also saves the capabilities in Session.
     *
     * @return boolean
     */
    function login() {
        if(is_object($this->sieve)) {
            return true;
        }
        // fopen();
    }

    /**
     * Get scripts list from SIEVE server.
     */
    function list() {
        $scripts = array();
        /* dirlist() ... */
        return $scripts;
    }

    /**
     * Get rules from specified script of Sieve server
     *
     * @param string $scriptname
     * @param array $scriptinfo
     * @return array
     */
    function load($scriptname = 'phpscript', &$rules, &$scriptinfo) {
        /* fopen() ... */
        /* If error: */
        if(false) {
            $prev = bindtextdomain ('avelsieve', SM_PATH . 'plugins/avelsieve/locale');
            textdomain ('avelsieve');
            $errormsg = _("Could not get SIEVE script from your IMAP server");
            $errormsg .= " " . $imapServerAddress.".<br />";
            
            if(!empty($this->sieve->error)) {
                $errormsg .= _("Error Encountered:") . ' ' . $this->sieve->error . '</br>';
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
     * @param string $newscript The SIEVE script to be uploaded
     * @param string $scriptname Name of script
     * @return true on success, false upon failure
     */
    function save($newscript, $scriptname = 'phpscript') {
        /* Write file... */
        /* fwrite() */
        if(false) {
            /* Error */
            $errormsg = '<p>';
            $errormsg .= _("Unable to load script to server.");
            // $errormsg .= _("Server responded with:");
            $errormsg .= '</p>';
            $errormsg .= _("Please contact your administrator.");
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
     */
    function delete($script = 'phpscript') {
        /* Delete file */
        /* If Error: */
        if(false) {
            $errormsg = sprintf( _("Could not delete script from server %s."), $sieve->host.':'.$sieve->port) .
                '<br/>';
            $errormsg .= _("Please contact your administrator.");
            print_errormsg($errormsg);
            return false;
        }
    }
}


