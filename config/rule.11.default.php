<?php
/**
 * User-friendly interface to SIEVE server-side mail filtering.
 * Plugin for Squirrelmail 1.4+
 *
 * Licensed under the GNU GPL. For full terms see the file COPYING that came
 * with the Squirrelmail distribution.
 *
 * Configuration File for Rule #11: Junk Mail Options.
 *
 * @version $Id: rule.11.default.php 935 2008-07-04 10:25:39Z avel $
 * @author Alexandros Vellis <avel@users.sourceforge.net>
 * @copyright 2002-2004 Alexandros Vellis
 * @package plugins
 * @subpackage avelsieve
 */

$prev = bindtextdomain ('avelsieve_rule_11', SM_PATH . 'plugins/avelsieve/locale');
textdomain ('avelsieve_rule_11');

/**
 * @var array Rule #11 (New-style Spam Rule) Setttings.
 */
$avelsieve_rules_settings[11] = array(
    'spamrule_score_max' => 100,
    'spamrule_score_default' => 10,
    'spamrule_score_header' => 'X-Spam-Score',
    'spamrule_tests_ldap' => true, // Try to ask Sendmail's LDAP Configuration
    'spamrule_tests_header' => 'X-Spam-Tests',
    'spamrule_action_default' => 'junk',

    'junkprune_backend' => 'ldapuserdata',

    'icons' => array(
        'OK' => 'images/icons/accept.png',
        'SPAM' => 'images/icons/exclamation.png',
        'NO_MAILBOX' => 'images/icons/exclamation.png',

        'TEMP_FAIL' => 'images/icons/error.png',
        'FAIL' => 'images/icons/error.png',
        'FAILED' => 'images/icons/error.png',
        'TEMP_FAILED' => 'images/icons/error.png',
    ),
    
    'spamrule_tests' => array(
        'rbls' => array(
            'desc' => _("RBLs are lists of Internet addresses, that have been verified to send SPAM messages."), 
            'action' => _("Place Messages that are marked in these black lists in the Junk Folder"),
            'available' => array(
    	        'Spamhaus.Block.List' => "Spamhaus Block List",
                'SpamCop' => "SpamCop",
	            'Composite.Blocking.List' => "Composite Blocking List",
             ),
             'values' => array(
                'OK' => _("Not considered as SPAM"),
                'SPAM' => _("Considered as SPAM"),
                'TEMP_FAIL' => _("Temporary Failure during RBL Check")
             ),
             'fail_values' => array('SPAM')
        ),
        'sav' => array(
            'action' => _("Check for Validity of Sender's Email Address"),
             'available' => array(
                 'Sender.Address.Verification' => _("Sender Address Verification"),
             ),
             'values' => array(
                'OK' => _("Sender Address is valid"),
                'NO_MAILBOX' => _("Sender Address does not exist"),
                'FAILED' => _("Unable to verify Sender Address (Permanent Error)"),
                'TEMP_FAILED' => _("Unable to verify Sender Address (Temporary Error)"),
             ),
             'fail_values' => array('NO_MAILBOX', 'FAILED')
        ),
        'additional' => array(
             'action' => _("Perform Additional Verification Tests"),
             'available' => array(
        	    'FORGED' => _("Forged Header")
             ),
             'values' => array(
                'OK' => _("Message Header is Valid"),
                'SPAM' => _("Message Header is Forged"),
                'TEMP_FAIL' => _("Temporary Failure during Check of Message Header")
             ),
             'fail_values' => array('SPAM')
        ),
    ),
    'custom_text' => array(
        'Sender.Address.Verification' => array(
                'NO_MAILBOX' => _("File messages in Junk folder, if the sender address does not exist."),
                'FAILED' => _("Strict mode: Also file messages in Junk when the sender does not accept any answers."),
        )
    ),
    'spamrule_tests_info' => array(
        'Spamhaus.Block.List' => array(
            'SPAM' => array(
                'desc' => _("Spamhaus tracks the Internet's Spammers, Spam Gangs and Spam Services, provides dependable realtime anti-spam protection for Internet networks, and works with Law Enforcement to identify and pursue spammers worldwide."),
                'url' => 'http://www.spamhaus.org/',
            ),
        ),
        'SpamCop' => array(
            'SPAM' => array(
                'desc' => _("SpamCop is the premier service for reporting spam. SpamCop determines the origin of unwanted email and reports it to the relevant Internet service providers. By reporting spam, you have a positive impact on the problem. Reporting unsolicited email also helps feed spam filtering systems, including, but not limited to, SpamCop\'s own service."),
                'url' => 'http://www.spamcop.net/',
            ),
        ),
	    'Composite.Blocking.List' => array(
            'SPAM' => array(
                'desc' => _("The CBL takes its source data from very large spamtraps/mail infrastructures, and only lists IPs exhibiting characteristics which are specific to open proxies of various sorts (HTTP, socks, AnalogX, wingate etc) which have been abused to send spam, worms/viruses that do their own direct mail transmission, or some types of trojan-horse or \"stealth\" spamware, without doing open proxy tests of any kind. In other words, the CBL only lists IPs that have attempted to send email to one of our servers in such a way as to indicate that the sending IP is infected."),
                'url' => 'http://cbl.abuseat.org/',
            ),
        ),
        'Sender.Address.Verification' => array(
           'NO_MAILBOX' => array(
                'desc' => _("Sender Address Verification (SAV) is a mechanism whereby the mail system checks if it can reach the sender of a message via the address that appears in the message envelope. If the server of the organization that the sender address belongs to responds to the check by saying that the address does not exist then Sender Address Verification fails.<br/<br/>WARNING: some organisations transmit automatic messages with an inexistent sender address (so called &quot;noreply&quot; addresses). These message will trigger an address verification failure and will end up in your Junk folder."),
            ),
            'FAILED' => array(
                'desc' => _("Strict mode: In the normal mode of behaviour only a non existent sender results in Sender Address Verification failure. In strict mode, Sender Address Verification <strong>also</strong> fails for those cases where the remote server cannot accept a message for other permanent reasons besides the sender not existing."),
            ),
        ),
        'SORBS.Safe.Aggregate' => array(
            'SPAM' => array(
                'desc' => _("An aggretate listing of various &quot;safe&quot; checks from SORBS, Spam and Open Relay Blocking System (SORBS)"),
                'url' => 'http://www.de.sorbs.net/using.shtml',
            ),
        ),
        'Policy.Block.List' => array(
            'SPAM' => array(
                'desc' => _("The Spamhaus PBL is a DNSBL database of end-user IP address ranges which should not be delivering unauthenticated SMTP email to any Internet mail server except those provided for specifically by an ISP for that customer\'s use. The PBL helps networks enforce their Acceptable Use Policy for dynamic and non-MTA customer IP ranges."),
                'url' => 'http://www.spamhaus.org/pbl/',
            ),
        ),
        'Exploits.Block.List' => array(
            'SPAM' => array(
                'desc' => _("The Spamhaus Exploits Block List (XBL) is a realtime database of IP addresses of illegal 3rd party exploits, including open proxies (HTTP, socks, AnalogX, wingate, etc), worms/viruses with built-in spam engines, and other types of trojan-horse exploits."),
                'url' => 'http://www.spamhaus.org/xbl/',
            ),
        ),
        'FORGED' => array(
            'SPAM' => array(
                 'desc' => _("This test checks if a message header is forged. For instance, the IP addresses are fake."),
            ),
        ),
    ),
    'default_rule' => array(
        'type' => 11,
        'enable' => 1,
        'junkmail_prune' => 1,
        'junkmail_days' => 7,
        'enable_whitelist' => 1,
        'whitelist_abook' => 1,
        'junkmail_advanced' => 0,
        'tests' => 
            array(
             'Policy.Block.List' => array('SPAM'),
             'SORBS.Safe.Aggregate' => array('SPAM'),
             'Spamhaus.Block.List' => array('SPAM'),
             'SpamCop' => array('SPAM'),
             'Composite.Blocking.List' => array('SPAM'),
             'Exploits.Block.List' => array('SPAM'),
             'Sender.Address.Verification' => array('NO_MAILBOX'),
             'FORGED' => array('SPAM'),
            ),
        'action' => 7,
        'stop' => true
    )
);

bindtextdomain('squirrelmail', SM_PATH . 'locale');
textdomain ('squirrelmail');

?>
