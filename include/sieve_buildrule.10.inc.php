<?php
/**
 * User-friendly interface to SIEVE server-side mail filtering.
 * Plugin for Squirrelmail 1.4+
 *
 * Licensed under the GNU GPL. For full terms see the file COPYING that came
 * with the Squirrelmail distribution.
 *
 * @version $Id: sieve_buildrule.10.inc.php 1034 2009-05-25 12:50:07Z avel $
 * @author Alexandros Vellis <avel@users.sourceforge.net>
 * @copyright 2004-2007 Alexandros Vellis
 * @package plugins
 * @subpackage avelsieve
 */

/**
 * Rule type: #10; Description: generic SPAM-rule, with SPAM-score,
 * whitelist and RBLs defined in the configuration file.
 *
 * The rule that will be produced (in $out) will be something like:
 *
 * <pre>
 *    if allof( anyof(header :contains "X-Spam-Rule" "Open.Relay.Database" ,
 *                header :contains "X-Spam-Rule" "Spamhaus.Block.List" 
 *            ),
 *         header :value "gt" :comparator "i;ascii-numeric" "80" ) {
 *       
 *       fileinto "INBOX.Junk";
 *       discard;
 *   }
 * </pre>   
 *       
 * Or, if a Whitelist is specified:
 *
 * <pre>
 *   if allof( anyof(header :contains "X-Spam-Rule" "Open.Relay.Database" ,
 *               header :contains "X-Spam-Rule" "Spamhaus.Block.List" 
 *           ),
 *         header :value "gt" :comparator "i;ascii-numeric" "80" ,
 *         not anyof(header :contains "From" "Important Person",
 *               header :contains "From" "Foo Person"
 *         )
 *       ) {
 *       
 *       fileinto "INBOX.Junk";
 *       discard;
 *   }
 * </pre>   
 */
function avelsieve_buildrule_10($rule) {
    global $avelsieve_rules_settings;
    
    $spamrule_score_default = $avelsieve_rules_settings[10]['spamrule_score_default'];
    $spamrule_score_header = $avelsieve_rules_settings[10]['spamrule_score_header'];
    $spamrule_tests = $avelsieve_rules_settings[10]['spamrule_tests'];
    $spamrule_tests_header = $avelsieve_rules_settings[10]['spamrule_tests_header'];
    $spamrule_action_default = $avelsieve_rules_settings[10]['spamrule_action_default'];

    $out = '';
    $text = '';
    $terse = '';
    
    $spamrule_advanced = false;
    
    if(isset($rule['advanced'])) {
        $spamrule_advanced = true;
    }
    
    if(isset($rule['score'])) {
        $sc = $rule['score'];
    } else {
        $sc = $spamrule_score_default;
    }
    
    if(isset($rule['tests'])) {
        $te = $rule['tests'];
    } else {
        $te = array_keys($spamrule_tests);
    }
    
    if(isset($rule['action'])) {
        $ac = $rule['action'];
    } else {
        $ac = $spamrule_action_default;
    }
    
    $out .= 'if allof( ';
    $text .= _("All messages considered as <strong>SPAM</strong> (unsolicited commercial messages)");
    $terse .= _("SPAM");
    
    if(sizeof($te) > 1) {
        $out .= ' anyof( ';
        for($i=0; $i<sizeof($te); $i++ ) {
            $out .= 'header :contains "'.$spamrule_tests_header.'" "'.$te[$i].'"';
            if($i < (sizeof($te) -1 ) ) {
                $out .= ",";
            }
        }
        $out .= " ),\n";
    } else {
        $out .= 'header :contains "'.$spamrule_tests_header.'" "'.$te[0].'", ';
    }
    
    $out .= "\n";
    $out .= ' header :value "ge" :comparator "i;ascii-numeric" "'.$spamrule_score_header.'" "'.$sc.'" ';
    
    if(isset($rule['whitelist']) && sizeof($rule['whitelist']) > 0) {
        /* Insert here header-match like rules, ORed of course. */
        $text .= ' (' . _("unless") . ' ';
        $terse .= '<br/>' . _("Whitelist:") . '<ul style="margin-top: 1px; margin-bottom: 1px;">';
    
        $out .= " ,\n";
        $out .= ' not anyof( ';
        for($i=0; $i<sizeof($rule['whitelist']); $i++ ) {
            $aTmp = build_rule_snippet('header', $rule['whitelist'][$i]['header'], $rule['whitelist'][$i]['matchtype'],
                $rule['whitelist'][$i]['headermatch']);
            $out .= $aTmp[0];
            $text .= $aTmp[1];
            $terse .= $aTmp[2];

            if($i<sizeof($rule['whitelist'])-1) {
                $out .= ', ';
                $text .= ' ' . _("or") . ' ';
            }
        }
        $text .= '), '; 
        $terse .= '</ul>'; 
        $out .= " )";
    }
    $out .= " )\n{\n";

    if($spamrule_advanced == true) {
        $text .= _("matching the Spam List(s):");
        $terse .= '<br/>' . _("Spam List(s):") . '<ul style="margin-top: 1px; margin-bottom: 1px;">';
        for($i=0; $i<sizeof($te); $i++) {
            $text .= $spamrule_tests[$te[$i]].', ';
            $terse .= '<li>' . $spamrule_tests[$te[$i]].'</li>';
        }
        $text .= sprintf( _("and with score greater than %s") , $sc );
        $terse .= '</ul>' . sprintf( _("Score > %s") , $sc);
    }
    
    $text .= ', ' . _("will be") . ' ';
    $terse .= '</td><td align="right">';
    
    if($ac == 'junk') {
        $out .= 'fileinto "INBOX.Junk";';
        $text .= _("stored in the Junk Folder.");
        $terse .= _("Junk");
    
    } elseif($ac == 'trash') {
        $text .= _("stored in the Trash Folder.");
    
        global $data_dir, $username;
        $trash_folder = getPref($data_dir, $username, 'trash_folder');
        /* Fallback in case it does not exist. Thanks to Eduardo
         * Mayoral. If not even Trash does not exist, it will end up in
         * INBOX... */
        if($trash_folder == '' || $trash_folder == 'none') {
            $trash_folder = "Trash";
        }
        $out .= 'fileinto "'.$trash_folder.'";';

        $terse .= _("Trash");
    
    } elseif($ac == 'discard') {
        $out .= 'discard;';
        $text .= _("discarded.");
        $terse .= _("Discard");
    }

    return(array($out,$text,$terse));
}

