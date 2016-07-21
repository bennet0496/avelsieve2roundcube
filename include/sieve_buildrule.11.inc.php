<?php
/**
 * User-friendly interface to SIEVE server-side mail filtering.
 * Plugin for Squirrelmail 1.4+
 *
 * Licensed under the GNU GPL. For full terms see the file COPYING that came
 * with the Squirrelmail distribution.
 *
 * @version $Id: sieve_buildrule.11.inc.php 1034 2009-05-25 12:50:07Z avel $
 * @author Alexandros Vellis <avel@users.sourceforge.net>
 * @copyright 2007 Alexandros Vellis
 * @package plugins
 * @subpackage avelsieve
 */

/**
 * Rule type: #11; Description: New-style SPAM-rule with
 * various features.
 *
 * This was written for the needs of the University of Athens
 * (http://www.uoa.gr, http://email.uoa.gr)
 * It might not suit your needs without proper adjustments
 * and hacking.
 *
 * @param array $rule
 * @param boolean $force_advanced_mode This flag is used when i want to get
 *   an analytical textual representation of the spam rule. This is used for
 *   being shown in the UI ("What does the predefined rule contain?").
 * @return array
 */
function avelsieve_buildrule_11($rule, $force_advanced_mode = false) {
    global $avelsieve_rules_settings, $rules;
    extract($avelsieve_rules_settings[11]);

    $out = '';
    $text = '';
    $terse = '';
    
    $advanced = false;
    if(isset($rule['advanced']) && $rule['advanced'] || isset($rule['junkmail_advanced']) && $rule['junkmail_advanced']) {
        $advanced = true;
    }
    
    if(isset($rule['tests']) && !empty($rule['tests'])) {
        $tests = $rule['tests'];
    }
    if(isset($rule['enable']) && $rule['enable']) {
        $enable = 1;
    } else {
        $enable = 0;
        $text = _("Junk Mail Rule is Disabled");
        $terse = _("<s></s>");
    }
    if(isset($rule['action'])) {
        $ac = $rule['action'];
    }
    
    $out .= 'if allof( ';
    $terse .= _("Junk Mail");
    
    $out_part = array();
    foreach($tests as $test=>$vals) {
        if(is_array($vals)) {
            foreach($vals as $val) {
                $out_part[] = 'header :contains "'.$spamrule_tests_header.'" "'.$test.':'.$val.'"';
            }
        } elseif(is_string($vals)) {
            // We prefer and build only arrays, but for backward compatibility 
            // with a previous alpha version...
            $out_part[] = 'header :contains "'.$spamrule_tests_header.'" "'.$test.':'.$vals.'"';
        }
    }
    if(sizeof($out_part) > 1) {
        $out .= ' anyof( '. implode( ",\n", $out_part ) . "),\n";
    } elseif(sizeof($out_part) == 1) {
        $out .= $out_part[0] . ',';
    }
    
    /** Placeholder: if there's a score in the future, it should be placed here. */
    //$out .= "\n";
    //$out .= ' header :value "ge" :comparator "i;ascii-numeric" "'.$spamrule_score_header.'" "'.$sc.'" ';
    
    /* Search the global variable $rules, to retrieve the whitelist rule data, if any. */
    for($i=0; $i<sizeof($rules); $i++) {
        if($rules[$i]['type'] == 12 && !empty($rules[$i]['whitelist'])) {
            $whitelistRef = &$rules[$i]['whitelist'];
            break;
        }
    }

    /* And now, use that data to build the actual whitelist in Sieve. */
    if(isset($whitelistRef)) {
        $out .= "\nnot anyof(\n";

        $outParts = array();
        foreach($whitelistRef as $w) {
            $aTmp1 = build_rule_snippet('header', 'From', 'contains', $w);
            $aTmp2 = build_rule_snippet('header', 'Sender', 'contains', $w);
            $outParts[] = $aTmp1[0];
            $outParts[] = $aTmp2[0];
        }
        $out .= implode(",\n", $outParts); 
        $out .= ')';

    } else {
        $out .= "true ";
    }
    $out .= ")\n{\n";  // closes 'allof'


    /* The textual descriptions follow */
    if($advanced || $force_advanced_mode) {
        $text .= _("All Messages") . ' '.
                 '<ul>' . // 1st level ul
                 '<li>' . _("matching any one of the Spam tests as follows:"). '</li><ul style="margin-top: 1px; margin-bottom: 1px;">';
        $terse .= '<br/>' . _("Spam Tests:") . '<ul style="margin-top: 1px; margin-bottom: 1px;">';
        foreach($tests as $test=>$val) {
            foreach($spamrule_tests as $group=>$data) {
                if(array_key_exists($test, $data['available'])) {
                    $text .= '<li><strong>' . $data['available'][$test]. '</strong> = '. 
                             ( is_array($val) ? implode(' | ', $val) : $val ). '</li>';
                    $terse .= '<li>' . $data['available'][$test].'</li>';
                    break;
                }
            }
        }
        $text .= '</ul><br/>';
        $terse .= '</ul>';
        
        if(isset($whitelistRef)) {
             $text .= '<li>' . _("and where the sender does <em>not</em> match any of the addresses / expressions in your <strong>Whitelist</strong>") . '</li>';
        }
        $text .= '</ul>'; // 1st level ul
    } else {
        /* Simple textual description for default rule. */
        $text .= _("The messages that match the system's default <strong>SPAM</strong> checks");
    }

    
    
    /* ------------------------ 'then' ------------------------ */

    $text .= ' ' . _("will be") . ' ';
    $terse .= '</td><td align="right">';

    /* FIXME - Temporary Copy/Paste kludge */
    switch($rule['action']) {
        /* Added */
        case '7':    /* junk folder */
            $out .= 'fileinto "INBOX.Junk";';
            $text .= _("stored in the <strong>Junk</strong> Folder.");
            $terse .= _("Junk");
            break;
        
        case '8':    /* junk folder */
            $text .= _("stored in the <strong>Trash</strong> Folder.");
        
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
            break;
    }

    return(array($out,$text,$terse));
}

