<?php
/**
 * User-friendly interface to SIEVE server-side mail filtering.
 * Plugin for Squirrelmail 1.4+
 *
 * Licensed under the GNU GPL. For full terms see the file COPYING that came
 * with the Squirrelmail distribution.
 *
 * This file contains configuration parameters for SIEVE mail filters plugin
 * (aka avelsieve)
 *
 * @version $Id: config_sample.php 1025 2009-05-21 08:35:24Z avel $
 * @author Alexandros Vellis <avel@users.sourceforge.net>
 * @copyright 2002-2004 Alexandros Vellis
 * @package plugins
 * @subpackage avelsieve
 */

/**
 * Debug Mode. Enable this (change to 1) if you need to send a bug report,
 * or to 2 or 3 if you are a developer!
 *
 * Valid values:
 * 0 = No debugging output
 * 1 = Normal debugging output
 * 2 = Firebug-enhanced debugging output
 * 3 = Enhanced debugging output and no Sieve capabilities checks - enables
 *     all UI features!
 */
if(!defined('AVELSIEVE_DEBUG')) {
    define('AVELSIEVE_DEBUG', 0);
}

/* ======================================================================== */
/* =================== IMAP Server / SIEVE Setup  ========================= */
/* ======================================================================== */

/** @var string Backend to use. Available backends are:
 * 'ManageSieve': Uses the ManageSieve protocol. e.g. Cyrus
 * 'File': Writes files straight to disk. e.g. Exim4, Dovecot LDA.
 */
global $avelsieve_backend;
$avelsieve_backend = 'ManageSieve';



/* ======================================================================== */
/* =================== ManageSieve Backend Options ======================== */
/* ======================================================================== */

/* Port where timsieved listens on the Cyrus IMAP server. Default is 2000. */

global $sieveport;
$sieveport = 2000;

/**
 * @var string Space separated list of preferred SASL mechanisms for the
 * authentication to timsieved. e.g. "PLAIN DIGEST-MD5";*/

global $sieve_preferred_sasl_mech;
$sieve_preferred_sasl_mech = 'PLAIN';

/**
 * @var boolean Disable STARTTLS for ManageSieve. You can set this to true,
 * if you do not wish to use encryption via TLS mechanisms (i.e. the server
 * is not configured properly, or this is a local connection and TLS is not
 * needed.
 * Note that STARTTLS is supported only in PHP5+. In PHP4 this option will
 * have no effect and STARTTLS will be disabled anyway.
 */
global $avelsieve_disabletls;
$avelsieve_disabletls = false;

/* ======================================================================== */
/* ======================= File Backend Options =========================== */
/* ======================================================================== */

global $avelsieve_file_backend_options, $data_dir, $username;
$avelsieve_file_backend_options = array(
    'avelsieve_default_file' => "$data_dir/$username.sievesource"
);

/* ======================================================================== */
/* ====== Implementation- and Server-Specific  Options ==================== */
/* ======================================================================== */


/* In Cyrus 2.3+, the notification action is a bit more complex than the
 * others. The oldcyrus variable is for supporting the partially implemented
 * notify extension implementation of Cyrus < 2.3. If you have Cyrus < 2.3,
 * just set this to true.
 *
 * This only changes the informational / help text displayed in avelsieve.
 *
 * Cyrus < 2.3 : $from$, $env-from$, $subject$
 * Cyrus 2.3+  : $from$, $env-from$, $subject$, $text$, $text[n]$
 */
global $avelsieve_oldcyrus;
$avelsieve_oldcyrus = true;

/* If you have Cyrus with an lmtpd that can understand the "auth" argument to
 * the :envelope test as the SMTP/LMTP auth, or any other Sieve implementation,
 * then you can enable this to provide this functionality to the user.
 *
 * This was not clarified in the base spec of RFC 3028. It will be done
 * correctly in a new version of Cyrus, based on a new draft / spec.
 */
global $avelsieve_enable_envelope_auth; 
$avelsieve_enable_envelope_auth = true;

/* Some Implementations of Sieve need certain things in order to operate
 * correctly. If you use any of the following server implementations, you MUST
 * set this variable to the corresponding value for the filtering to work
 * correctly.
 * Valid values are:
 * - Any RFC3028-mostly-compatible implementation: '' (empty).
 * - Exim MTA: 'exim'
 * - MFL (as supported by mvmf): 'mfl'
 */
global $avelsieve_custom_sieve_implementation;
$avelsieve_custom_sieve_implementation = '';


/* For delivery agents that don't know how to handle some mailbox prefixes, you 
 * can enable this option. Example for Dovecot LDA: 'INBOX.';
 */
global $avelsieve_striproot;
$avelsieve_striproot = '';

/* If the backend does not support capabilities reporting, such as the File
 * Backend, then you should define which capabilities are used by the server
 * implementation.
 *
 * The following are the capabilities supported by Exim4 as of Exim version
 * 4.60, according to README.SIEVE. You can change them if a new version of
 * Exim provides more functionality:
 *  'envelope', 'fileinto', 'copy', 'vacation', 'comparator-i;ascii-numeric'
 * 
 * The following are the capabilities that are suported by MFM, according to:
 * http://www.mvmf.org/mfl/language.shtml#sieve
 *  'envelope', 'fileinto', 'reject', 'relational', 'subaddress', 'regex',
 *  'editheader', 'copy', 'vacation', 'comparator-i;ascii-casemap',
 *  'comparator-i;octet'
 *
 * The following capabilities have been reported to work with Dovecot LDA:
 *  'envelope', 'fileinto', 'copy', 'vacation', 'comparator-i;ascii-numeric',
 *  'imapflags', 'subaddress','relational','regex'
 *
 */
global $avelsieve_hardcoded_capabilities;
$avelsieve_hardcoded_capabilities = array(
    'envelope', 'fileinto', 'copy', 'vacation', 'comparator-i;ascii-numeric'
);


/** @var boolean Enable ImapProxy mode.
 * If you use imapproxy, because imapproxy cannot understand and proxy the
 * SIEVE protocol, you must connect to the SIEVE daemon (usually on the IMAP
 * server) itself. So you need to set $imapproxymode to true, and define a
 * mapping, from the imapproxy host (usually localhost) to your real IMAP
 * server (usually the same that is defined on Imapproxy's configuration).
 * 
 * This will not work if you use a perdition-style proxy, where different users
 * go to different IMAP servers; it applies mostly to people running imapproxy
 * for speed and want a quick hack. */

global $avelsieve_imapproxymode, $avelsieve_imapproxyserv;
$avelsieve_imapproxymode = false;
$avelsieve_imapproxyserv = array(
	'localhost' => 'imap.example.org'
);

/** @var boolean Ldapuserdata mode: Gets user's email addresses (including
 * mailAlternate & mailAuthorized) from LDAP Prefs Backend plugin's cache */

global $avelsieve_ldapuserdatamode;
$avelsieve_ldapuserdatamode = false;

/** @var array Map of cyrus administrator users, for proxy authentication */

global $avelsieve_cyrusadmins_map;
$avelsieve_cyrusadmins_map = array(
	'cyrusimap' => 'cyrussieve'
);



/* ======================================================================== */
/* =============== Avelsieve Interface / Behavior Setup  ================== */
/* ======================================================================== */

/* Be conservative to our updates on the SIEVE server? If true, a button
 * entitled "Save Changes" will appear, which will give the user the
 * functionality to register her changes. 'false' is recommended. */
$conservative = false;

/* Use images for the move up / down, delete rule buttons and STOP? */

$useimages = true;

/* Translate the messages returned by the "Reject" and "Vacation" actions? The
 * default behaviour since 0.9 is not to translate them. Change to true if in
 * an intranet environment or in a same-language environment. */

global $translate_return_msgs;
$translate_return_msgs = false;

/* Theme to use for the images. A directory with the same name must exist under
 * plugins/avelsieve/$imagetheme, that contains the files: up.png, down.png,
 * del.png, dup.png, edit.png, top.png, bottom.png. */

$imagetheme = 'famfamfam';
//$imagetheme = 'bluecurve_24x24';
//$imagetheme = 'bluecurve_16x16';

/* Number of items to display _initially_, when displaying the header match
 * rule */
global $startitems;
$startitems = 3;

/* Maximum number of items to allow in one header match rule. */
global $maxitems;
$maxitems = 10;

/* Headers to display in listbox widget, when adding a new header rule. */

global $headers;
$headers = array(
 'From', 'To', 'Cc', 'Bcc', 'Subject', 'Reply-To', 'Sender', 'List-Id',
 'MailingList', 'Mailing-List', 'X-ML-Name', 'X-List', 'X-List-Name', 'X-Mailing-List',
 'Resent-From',  'Resent-To', 'X-Mailer', 'X-Mailing-List',
 'X-Spam-Flag', 'X-Spam-Status',
 'X-Priority', 'Importance', 'X-MSMail-Priority', 'Precedence',
 'Return-Path', 'Received', 'Auto-Submitted',
 'X-Spam-Flag', 'X-Spam-Status','X-Spam-Tests'
 );

/* Available :method's for the :notify extension (if applicable) */
global $notifymethods;
$notifymethods = array(
'mailto', 'sms'
);
/* use the value "false" if you want to provide a simple input box so that
 * users can edit the method themselves : */
//$notifymethods = false;


/* Capabilities to disable. If you would like to force avelsieve not to display
 * certain features, even though there _is_ a capability for them by
 * Cyrus/timsieved, you should specify these here. For instance, if you would
 * like to disable the notify extension, even though timsieved advertises it,
 * you should add 'notify' in this array: $force_disable_avelsieve_capability =
 * array("notify");. This will still leave the defined feature on, and if the
 * user can upload her own scripts then she can use that feature; this option
 * just disables the GUI of it. Leave as-is (empty array) if you do not need
 * that.
 * 
 * Look in $implemented_capabilities array in include/constants.inc.php for
 * valid values */

// $disable_avelsieve_capabilities = array("notify");
global $disable_avelsieve_capabilities;
$disable_avelsieve_capabilities = array();

/* Display Filters link in the top Squirrelmail header? */

global $avelsieveheaderlink;
$avelsieveheaderlink = true;

/* Default rules table display mode, one of 'verbose' or 'terse' */
global $avelsieve_default_mode; 
$avelsieve_default_mode = 'terse';



/* ======================================================================== */
/* ========================= Custom rules Configuration =================== */
/* ======================================================================== */

/**
 * @var array
 * List of additional rules to enable. This must be the numeric id of the
 * rule.
 *
 * 10: Spam Rule (as existed in Avelsieve)
 * 11: Junk-Mail Rule (new-style SPAM Rule, only one exists per Sieve script)
 * 12: Global Whitelist (only one exists per Sieve script)
 * 13: Custom Sieve Code (allows users to enter whatever Sieve code they like - 
 *     Alpha/Experimental)
 * 
 * Example:
 * array(11, 12);
 */
global $avelsieve_enable_rules;
$avelsieve_enable_rules = array();

global $avelsieve_rules_settings;
$avelsieve_rules_settings = array();
foreach($avelsieve_enable_rules as $r) {
    if(file_exists(SM_PATH . 'plugins/avelsieve/config/rule.'.$r.'.php')) {
        require(SM_PATH . 'plugins/avelsieve/config/rule.'.$r.'.php');
    } else {
        require(SM_PATH . 'plugins/avelsieve/config/rule.'.$r.'.default.php');
    }
}

/* Please keep the following setting false; it is alpha + needs Squirrelmail
 * to be patched in three or four places. */

$avelsieve_spam_highlight_enable = false;


