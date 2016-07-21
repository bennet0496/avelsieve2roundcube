<?php
/**
 * User-friendly interface to SIEVE server-side mail filtering.
 * Plugin for Squirrelmail 1.4+
 *
 * Licensed under the GNU GPL. For full terms see the file COPYING that came
 * with the Squirrelmail distribution.
 *
 * @version $Id: DO_Sieve_Skeleton.class.php 1020 2009-05-13 14:10:13Z avel $
 * @author Alexandros Vellis <avel@users.sourceforge.net>
 * @copyright 2004-2007 Alexandros Vellis
 * @package plugins
 * @subpackage avelsieve
 */

/**
 * This is a skeleton (template) class to help you write a new storage backend
 * for AvelSieve Scripts.
 * If you need any help, please contact me by email and I will be glad to give
 * directions. - Alexandros
 */
class DO_Sieve_Skeleton extends DO_Sieve {
    /**
     * Class Constructor
     */
    function DO_Sieve_Skeleton() {
        /* Get Cached Capabilities if they exist. */
        sqgetGlobalVar('sieve_capabilities', $sieve_capabilities, SQ_SESSION);
        if(isset($sieve_capabilities)) {
            $this->capabilities = $sieve_capabilities;
        }
        sqgetGlobalVar('rules', $rules, SQ_SESSION);
        if(isset($rules)) {
            $this->rules = $rules;
        }
        
        /* Add other important variable initialization here. */
    }

    /**
     * This function initializes the avelsieve environment. Basically, it makes
     * sure that there is a valid sieve_capability array.
     *
     * @return void
     */
    function init() {
        /* Initialize here the environment. E.g. create new connection handle. */
    }

    /**
     * Login to SIEVE server. Also saves the capabilities in Session.
     *
     * @return boolean
     */
    function login() {
    }

    /**
     * Get scripts list from SIEVE server.
     */
    function list() {
        $scripts = array();
        /* ... */
        return $scripts;
        /* Return an array like this: $scripts = array(0=>'foobar', 1=>'meow') */
    }

    /**
     * Get rules from specified script of Sieve server
     *
     * @param string $scriptname
     * @param array $scriptinfo
     * @return array
     */
    function load($scriptname = 'phpscript', &$rules, &$scriptinfo) {
        /* ... */
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

    /**
     * Set Active Script
     *
     * @param string $script 
     * @return true on success, false upon failure
     */
    function setactive($script) {
        /* ... */
    }

    /**
     * Log Out
     */
    function logout() {
    }

}
 
