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
 * This file contains functions related to printing out information in 
 * Squirrelmail's src/right_main.php
 *
 * @version $Id: right_main.inc.php 1020 2009-05-13 14:10:13Z avel $
 * @author Alexandros Vellis <avel@users.sourceforge.net>
 * @copyright 2007 Alexandros Vellis
 * @package plugins
 * @subpackage avelsieve
 */

/**
 * The actual function that prints out information in message lists 
 * (src/right_main.php).
 *
 * The following are currently performed:
 *
 * 1) Junk Mail functionality: Link to options from Junk folder. (If rule #11 
 * is enabled).
 *
 * 2) Vacation Rule reminder, from INBOX folder.
 */
function avelsieve_right_main_do() {
    global $avelsieve_enable_rules, $mailbox, $color;

    if(in_array(11,$avelsieve_enable_rules) && ($mailbox == 'Junk' || $mailbox == 'INBOX.Junk')) {
        include_once(SM_PATH . 'plugins/avelsieve/include/junkmail.inc.php');
        junkmail_right_main_do();
    }
    
    bindtextdomain ('avelsieve', SM_PATH . 'plugins/avelsieve/locale');
    textdomain ('avelsieve');

    if($mailbox == 'INBOX') {
        if ( sqgetGlobalVar('just_logged_in', $just_logged_in, SQ_SESSION) && $just_logged_in == true) {
            include_once(SM_PATH . 'plugins/avelsieve/include/sieve_getrule.inc.php');
            include_once(SM_PATH . 'plugins/avelsieve/include/html_main.inc.php');
            sqgetGlobalVar('rules', $rules, SQ_SESSION);
            if(!isset($rules)) {
                global $avelsieve_backend;
                $backend_class_name = 'DO_Sieve_'.$avelsieve_backend;
                include_once(SM_PATH . 'plugins/avelsieve/include/sieve.inc.php');
                $s = new $backend_class_name;
                $s->init();
                $s->login();
                /* Actually get the script 'phpscript' (hardcoded ATM). */
                if($s->load('phpscript', $rules, $scriptinfo)) {
                    $_SESSION['rules'] = $rules;
                    $_SESSION['scriptinfo'] = $scriptinfo;
                }
                $s->logout();
            }

            $vacation_rules = avelsieve_vacation_check($rules);

            if(!empty($vacation_rules)) {
                $ht = new avelsieve_html;
                $ht->useimages = true; // FIXME
                    
                echo $ht->all_sections_start() . $ht->section_start(
                     ($ht->useimages == true ? '<img src="'.$ht->iconuri.'information.png" alt="(i)" /> ' : '')
                     .   _("Vacation Filter Reminder"))          
                     . '<p>';
                    // if(!$rule_exists || !$rule_enabled)

                $fnum = $vacation_rules[0]; // First rule number
                echo '<p style="color:'.$color[8].'; text-align: center; font-weight: normal;">' .
                      ($ht->useimages ? '<img src="'.$ht->iconuri.'lightbulb.png" alt="(i)" border="0" />'. ' ' : '' ) .
                      sprintf( _("Note: A <a href=\"%s\">Vacation Autoresponder</a> is active (<a href=\"%s\">Rule #%s</a> in your current Mail Filtering Rules).<br/>Don't forget to disable it or delete it when you are back."),
                      '../plugins/avelsieve/edit.php?edit='.$fnum, '../plugins/avelsieve/table.php#rule_row_'.$fnum, $fnum+1) .
                      '</p>'.
                       $ht->section_end() . $ht->all_sections_end();
            }
        }
    }
    bindtextdomain('squirrelmail', SM_PATH . 'locale');
    textdomain('squirrelmail');
}

