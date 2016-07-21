<?php
/**
 * User-friendly interface to SIEVE server-side mail filtering.
 * Plugin for Squirrelmail 1.4+
 *
 * Licensed under the GNU GPL. For full terms see the file COPYING that came
 * with the Squirrelmail distribution.
 *
 * Configuration File for Rule #10: SPAM Rule
 *
 * @version $Id: rule.10.default.php 935 2008-07-04 10:25:39Z avel $
 * @author Alexandros Vellis <avel@users.sourceforge.net>
 * @copyright 2002-2007 Alexandros Vellis
 * @package plugins
 * @subpackage avelsieve
 */

/**
 * @var array Rule #10 (Spam Rule) Setttings.
 *
 * Beta - easy anti-spam rule Configuration. Options should be
 * self-explanatory. For $spamrule_tests, the key is the spam block list as
 * displayed in the message header inserted by your anti-spam solution, while
 * the value is the user-friendly name displayed to the user in the advanced
 * configuration. 'spamrule_action_default' can be one of 'junk', 'trash' or
 * 'discard'. You can set it to 'junk' if you have the Junkfolder plugin
 * installed.
 *
 * If you would like to get the Spam tests from Sendmail's configuration (which
 * resides in LDAP), try something like this in your config/config_local.php:
 *
 * $ldap_server[0]['mtarblspamfilter'] =
 *       '(|(sendmailmtaclassname=SpamRBLs)(sendmailmtaclassname=SpamForged))';
 * $ldap_server[0]['mtarblspambase'] = 'ou=services,dc=example,dc=org';
 *
 */
$avelsieve_rules_settings[10] = array(
    'spamrule_score_max' => 100,
    'spamrule_score_default' => 10,
    'spamrule_score_header' => 'X-Spam-Score',
    'spamrule_tests_ldap' => false, // Try to ask Sendmail's LDAP Configuration 
    'spamrule_tests' => array(
    	'Open.Relay.DataBase' => "Open Relay Database",
	    'Spamhaus.Block.List' => "Spamhaus Block List",
    	'SpamCop' => "SpamCop",
	    'Composite.Blocking.List' => "Composite Blocking List",
    	'FORGED' => "Forged Header"
    ),
    'spamrule_tests_header' => 'X-Spam-Tests',
    'spamrule_action_default' => 'junk'
);

?>
