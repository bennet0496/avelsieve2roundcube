<?php
/*
 * User-friendly interface to SIEVE server-side mail filtering.
 * Plugin for Squirrelmail 1.4+
 *
 * Licensed under the GNU GPL. For full terms see the file COPYING that came
 * with the Squirrelmail distribution.
 *
 * @version DO_Sieve_LdapSieve.class.php,v 1.0 2007-01-22 10:10:10 
 * @authors Boris Maroutaeff <boris.maroutaeff@uclouvain.be>,
 *          Laurent Buset <laurent.buset@uclouvain.be>
 *          Pascal Maes <pascal.maes@uclouvain.be>
 * @copyright 2006-2008
 * @package plugins
 * @subpackage avelsieve
 */

/**
 * Backend for Sieve script management interface for Sun JES Messaging Server
 * The rules are stored in the attribute mailsieverulesource of the LDAP server
 */
class DO_Sieve_LdapSieve extends DO_Sieve {
    var $loggedin = false;
    var $sieveServerAddress;
    var $sieveUsername;
    
    function DO_Sieve_LdapSieve() {
        global $username, $ldap_server,$avelsieve_hard_capabilities;

        $this->DO_Sieve();

        /* Get Cached Capabilities if they exist. */

        sqgetGlobalVar('sieve_capabilities', $sieve_capabilities, SQ_SESSION);
        if(isset($sieve_capabilities)) {
            $this->capabilities = $sieve_capabilities;
        }

        sqgetGlobalVar('rules', $rules, SQ_SESSION);
        if(isset($rules)) {
            $this->rules = $rules;
        }
        
        $this->sieveUsername = $username;
        $this->sieveLdapServer = $ldap_server;
    $this->sieveHardcodedCapabilities = $avelsieve_hard_capabilities;
    }

    /**
    * This function does nothing
    *
    */
    function init() {

    }

    /**
     * Login to LDAP server. Also saves the capabilities in Session.
     *
     * @return boolean
     */
    function login() {
        
        if($this->loggedin) {
        return true;
        }
            
        $this->sieveLdapHost = $this->sieveLdapServer[0]['host'];
    $this->sieveLdapBase = $this->sieveLdapServer[0]['base'];

    /*  Anonymous connexion to retrieve dn */

    $this->sieveLdapConn = ldap_connect($this->sieveLdapHost);
    $this->ldapbind = ldap_bind($this->sieveLdapConn)
        or die("Unable to connect to LDAP server, contact your administrator");
    $this->sr = ldap_search($this->sieveLdapConn, $this->sieveLdapBase, "uid=$this->sieveUsername"); 
    $this->info = ldap_get_entries($this->sieveLdapConn, $this->sr);
    $this->dn = $this->info[0]["dn"];
    ldap_close($this->sieveLdapConn);

    /* Authenticated connexion to LDAP server */

    sqgetGlobalVar('key', $key, SQ_COOKIE);
    sqgetGlobalVar('onetimepad', $onetimepad, SQ_SESSION);

    /* Need the cleartext password to connect to the LDAP server */
    $acctpass = OneTimePadDecrypt($key, $onetimepad);

    if(!$this->dn || !$acctpass)
        die("Error: unable to find DN or invalid password.");
    $this->sieveLdapConn = ldap_connect($this->sieveLdapHost);
    $this->ldapbind = ldap_bind($this->sieveLdapConn, $this->dn, $acctpass)
        or die("Unable to bind to LDAP server, contact your administrator");

    if(!isset($this->sieve_capabilities)) {
        $this->capabilities = $sieve_capabilities = $this->sieveHardcodedCapabilities;
        $_SESSION['sieve_capabilities'] = $sieve_capabilities;
    }

        $this->loggedin = true;
        return true;
    }

    /**
     * Get rules from attribute mailsieverulesource of LDAP server
     *
     * @param string $scriptname (NULL in this case : not used)
     * @param array $rules
     * @param array $scriptinfo
     * @return boolean
     */
    function load($scriptname = NULL, &$rules, &$scriptinfo) {
        $rules = array();
        $scriptinfo = array();
    
        if(!$this->loggedin) {
            $this->login();
        }

        /* Get rules from LDAP server. */
        
        $this->sr = ldap_search($this->sieveLdapConn, $this->sieveLdapBase, "uid=$this->sieveUsername", array("mailsieverulesource"))
            or die("Unable to receive results from LDAP server, contact your administrator");
        $this->infos = ldap_get_entries($this->sieveLdapConn, $this->sr)
            or die("Unable to get results from LDAP server, contact your administrator");

    /* All the rules are store in only one value of the attribute mailsieverulesource */

        $sievescript = $this->infos[0]["mailsieverulesource"][0];
        //ldap_close($this->sieveLdapConn);
        //$this->loggedin = false;
        
        /* Extract rules from $sievescript. */
        $rules = avelsieve_extract_rules($sievescript, $scriptinfo);
        return true;
    }
    
    /**
    * Upload rules
    *
    * @param string $newscript The SIEVE script to be uploaded
    * @param string $scriptname (NULL in this case : not used)
    * @return true on success, false upon failure
    */
    function save($newscript, $scriptname = NULL) {
        
        if(!$this->loggedin) {
            $this->login();
        }
        $attrs["mailsieverulesource"] = stripslashes($newscript);
    if (!ldap_mod_replace($this->sieveLdapConn, $this->dn, $attrs)) {
        /* Just to be safe. */
            $errormsg = _("Could not set active script on your IMAP server ");
            $errormsg .= $this->sieveLdapServer . ".<br />";
            $errormsg .= _("Please contact your administrator.");
            print_errormsg($errormsg);
            return false;
    }
    return true;
        //ldap_close($this->sieveLdapConn);
        //$this->loggedin = false;
    }
    
    /**
     * Delete rules stored in LDAP server.
     *
     * @param string $script (NULL in this case : not used)
     * @return true on success, false upon failure
     */
    function delete($script = NULL) {
        if(!$this->loggedin) {
            $this->login();
        }
        $attrs["mailsieverulesource"] = array();
    if (!ldap_mod_del($this->sieveLdapConn, $this->dn, $attrs)) {
            $errormsg = _("Could not delete script from server ");
            $errormsg .= $this->sieveLdapServer . ".<br />";
            $errormsg .= _("Please contact your administrator.");
            print_errormsg($errormsg);
            return false;
    }
    return true;
        //ldap_close($this->sieveLdapConn);
        //$this->loggedin = false;
    }

    /**
     * Log Out from ManageSieve Server.
     */
    function logout() {
        $ldap_close($this->sieveLdapConn);
    }
}

