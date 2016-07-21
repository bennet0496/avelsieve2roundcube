<?php
/**
 * User-friendly interface to SIEVE server-side mail filtering.
 * Plugin for Squirrelmail 1.4
 *
 * Based on Dan Ellis' test scripts that came with sieve-php.lib
 * <danellis@rushmore.com> <URL:http://sieve-php.sourceforge.net>
 *
 * Licensed under the GNU GPL. For full terms see the file COPYING that came
 * with the Squirrelmail distribution.
 *
 * table.php: main routine that shows a table of all the rules and allows
 * manipulation.
 *
 * @version $Id: table.php 1031 2009-05-25 08:24:06Z avel $
 * @author Alexandros Vellis <avel@users.sourceforge.net>
 * @copyright 2004-2009 The SquirrelMail Project Team, Alexandros Vellis
 * @package plugins
 * @subpackage avelsieve
 */

/** Includes */
if (file_exists('../../include/init.php')) {
    include_once('../../include/init.php');
} else if (file_exists('../../include/validate.php')) {
    define('SM_PATH','../../');
    include_once(SM_PATH . 'include/validate.php');
    include_once(SM_PATH . 'include/load_prefs.php');
    include_once(SM_PATH . 'functions/page_header.php');
    include_once(SM_PATH . 'functions/date.php');
}
    
include_once(SM_PATH . 'functions/imap_general.php');

include(SM_PATH . 'plugins/avelsieve/config/config.php');
include_once(SM_PATH . 'plugins/avelsieve/include/support.inc.php');
include_once(SM_PATH . 'plugins/avelsieve/include/html_rulestable.inc.php');
include_once(SM_PATH . 'plugins/avelsieve/include/sieve.inc.php');
include_once(SM_PATH . 'plugins/avelsieve/include/spamrule.inc.php');
include_once(SM_PATH . 'plugins/avelsieve/include/styles.inc.php');

if(AVELSIEVE_DEBUG > 0) include_once(SM_PATH . 'plugins/avelsieve/include/dumpr.php');

sqsession_is_active();

sqgetGlobalVar('popup', $popup, SQ_GET);
sqgetGlobalVar('haschanged', $haschanged, SQ_SESSION);

$location = get_location();

sqgetGlobalVar('rules', $rules, SQ_SESSION);
sqgetGlobalVar('scriptinfo', $scriptinfo, SQ_SESSION);
sqgetGlobalVar('logout', $logout, SQ_POST);

sqgetGlobalVar('position', $position, SQ_FORM);

$prev = bindtextdomain ('avelsieve', SM_PATH . 'plugins/avelsieve/locale');
textdomain ('avelsieve');

$backend_class_name = 'DO_Sieve_'.$avelsieve_backend;
$s = new $backend_class_name;
$s->init();

$base_uri = sqm_baseuri();

isset($popup) ? $popup = '?popup=1' : $popup = '';

sqgetGlobalVar('delimiter', $delimiter, SQ_SESSION);
if(!isset($delimiter)) {
    $delimiter = sqimap_get_delimiter($imapConnection);
}

sqgetGlobalVar('sieve_capabilities', $sieve_capabilities, SQ_SESSION);
    
require_once (SM_PATH . 'plugins/avelsieve/include/constants.inc.php');

if (!isset($rules)) {
    /* Login. But if the rules are cached, don't even login to SIEVE
     * Server. */ 
    $s->login();

    /* Actually get the script 'phpscript' (hardcoded ATM). */
    if($s->load('phpscript', $rules, $scriptinfo)) {
        $_SESSION['rules'] = $rules;
        $_SESSION['scriptinfo'] = $scriptinfo;
    }
}

// unset($sieve->response);
// TODO

/* On to the code that executes if avelsieve script exists or if a new rule has
 * been created. */

if ($logout) {
    /* Activate phpscript and log out. */
    $s->login();

    if ($newscript = makesieverule($rules)) {

        $s->save($newscript, 'phpscript');
        avelsieve_spam_highlight_update($rules);

        if(!($s->setactive('phpscript'))){
            /* Just to be safe. */
            $errormsg = _("Could not set active script on your IMAP server");
            $errormsg .= " " . $imapServerAddress.".<br />";
            $errormsg .= _("Please contact your administrator.");
            print_errormsg($errormsg);
            exit;
        }
        $s->logout();
    
    } else {
        /* upload a null thingie!!! :-) This works for now... some time
         * it will get better. */
        $s->save('', 'phpscript'); 
        avelsieve_spam_highlight_update($rules);
        /* if(sizeof($rules) == "0") {
            $s->delete('phpscript');
        } */
    }
    unset($_SESSION['rules']);
    
    header("Location: $location/../../src/options.php\n\n");
    // header("Location: $location/../../src/options.php?optpage=avelsieve\n\n");
    exit;

} elseif (isset($_POST['addrule'])) {
    header("Location: $location/edit.php?addnew=1");
    exit;

} elseif (isset($_POST['addspamrule'])) {
    header("Location: $location/addspamrule.php");
    exit;
}

/* Routine for Delete / Delete selected / enable selected / disable selected /
 * edit / duplicate / moveup/down */

/* This is the flag to use in order to enable the actual 
 * routines, after we are done with all the variable retrieval
 * and validation */
$modifyEnable = false;

$allowed_actions = array('mvup','mvdown','mvtop','mvbottom','mvposition','duplicate','insert','sendemail', 'enable', 'disable');

if(isset($_POST['morecontrols'])) {
    foreach($_POST['morecontrols'] as $i => $act) {
        if(is_numeric($i) && isset($rules[$i]) && !empty($act) && in_array($act,$allowed_actions)) {
            $modifyRules[] = $i;
            $modifyAction = $act;
            $modifyEnable = true;
        }    
    }
}

if(isset($_GET['rule']) || isset($_POST['deleteselected']) ||
  isset($_POST['enableselected']) || isset($_POST['disableselected']) ) {
    // 'edit' and 'rm' are simple, get these over with:
    if (isset($_GET['edit'])) {
        header("Location: $location/edit.php?edit=".$_POST['rule']."");
        exit;

    } elseif (isset($_GET['dup'])) {
        header("Location: $location/edit.php?edit=".$_POST['rule']."&dup=1");
        exit;

    } elseif (isset($_GET['rm']) || ( isset($_POST['deleteselected']) && isset($_POST['selectedrules'])) ) {

        if (isset($_POST['deleteselected'])) {
            $rules2 = $rules;
            $deletedrules = array();
            $notdeletedrules = array();
            foreach($_POST['selectedrules'] as $no=>$sel) {
                if(isset($rules2[$sel])) {
                    if($avelsieve_maintypes[$rules2[$sel]['type']]['undeletable']) {
                        $notdeletedrules[] = $sel;
                    } else {
                        unset($rules2[$sel]);
                        $deletedrules[] = $sel;
                    }
                }
            } 
            // The human-readable messages:
            $rules = array_values($rules2);
            if(!empty($deletedrules)) {
                $_SESSION['comm']['deleted'] = $deletedrules;
            }
            if(!empty($notdeletedrules)) {
                if(sizeof($notdeletedrules) == 1) {
                    $errormsg = sprintf( _("Could not delete rule #%s: This type of rule cannot be deleted."), $notdeletedrules[0]);
                } else {
                    $errormsg = sprintf( _("Could not delete rules #%s: This type of rule cannot be deleted."), implode(', ', $notdeletedrules) );
                }
            }

        } elseif(isset($_GET['rm'])) {
            if(isset($rules[$_GET['rule']]) && $avelsieve_maintypes[$rules[$_GET['rule']]['type']]['undeletable'] ) {
                $errormsg = sprintf( _("Could not delete rule #%s: This type of rule cannot be deleted."), $_GET['rule']);
            } else {
                $rules2 = $rules;
                unset($rules2[$_GET['rule']]);
                $rules = array_values($rules2);
                $_SESSION['comm']['deleted'] = $_GET['rule'];
            }
        }

        if (!$conservative) {
            $s->login();
            if(sizeof($rules) == 0) {
                $s->delete('phpscript');
            }  else {
                $newscript = makesieverule($rules);
                $s->save($newscript, 'phpscript');

            }
            avelsieve_spam_highlight_update($rules);
            sqsession_register($rules, 'rules');
        } 
        /* Since removing rules is a destructive function, we should redirect
         * to ourselves so as to eliminate the 'rm' GET parameter. (User could
         * do "Reload Frame" in browser) */
        sqsession_register($rules, 'rules');
        session_write_close();
        header("Location: $location/table.php\n\n");
        exit;
    
    } elseif(isset($_POST['enableselected']) || isset($_POST['disableselected'])) {
        /* FIXME - in this block, define the $modifyEnable and $modifyAction vars
         * instead of doing the actual work. */
        foreach($_POST['selectedrules'] as $no=>$sel) {
            if(isset($_POST['enableselected'])) {
                /* Verify that it is enabled  by removing the disabled flag. */
                if(isset($rules[$sel]['disabled'])) {
                    unset($rules[$sel]['disabled']);
                    $haschanged = true;
                }
            } elseif(isset($_POST['disableselected'])) {
                /* Disable! */
                $rules[$sel]['disabled'] = 1;
                $haschanged = true;
            }
        } 

    } elseif (isset($_GET['mvup'])) {
        $modifyEnable = true;
        $modifyAction = 'mvup';
        $modifyRules = array($_GET['rule']);

    } elseif (isset($_GET['mvdn'])) {
        $modifyEnable = true;
        $modifyAction = 'mvdn';
        $modifyRules = array($_GET['rule']);
    
    } elseif (isset($_GET['mvtop'])) {
        // Left over for compatibility reasons or for when the icons are back
        // Rule to get to the top:
        $modifyEnable = true;
        $modifyAction = 'mvtop';
        $modifyRules = array($_GET['rule']);

    } elseif (isset($_GET['mvbottom'])) {
        $modifyEnable = true;
        $modifyAction = 'mvbottom';
        $modifyRules = array($_GET['rule']);
    }
}

// All (or most of the) actions on the rules, are to be performed in this 
// block:

if($modifyEnable) {

    switch($modifyAction) {
        case 'mvup':
            $rules = array_swapval($rules, $modifyRules[0], $modifyRules[0]-1);
            /* Flag to write changes back. */ 
            $haschanged = true;
            break;

        case 'mvdn':
            $rules = array_swapval($rules, $modifyRules[0], $modifyRules[0]+1);
            /* Flag to write changes back. */ 
            $haschanged = true;
            break;

        case 'mvtop':
            $ruletop = $rules[$modifyRules[0]];
            unset($rules[$modifyRules[0]]);
            array_unshift($rules, $ruletop);
            /* Flag to write changes back. */ 
            $haschanged = true;
            break;

        case 'mvbottom':
            /* Rule to get to the bottom: */
            $rulebot = $rules[$modifyRules[0]];
            unset($rules[$modifyRules[0]]);
            /* Reindex */
            $rules = array_values($rules);
            /* Now Append it */
            $rules[] = $rulebot;
            /* Flag to write changes back. */ 
            $haschanged = true;
            break;

        case 'mvposition':
            if(isset($position) && is_numeric($position)) {
                if(!is_numeric($position) || $position < 1) {
                    $errormsg = sprintf( _("The entered position, %s, is not valid."), htmlspecialchars($position));
                } elseif($position > sizeof($rules)) {
                    $errormsg = sprintf( _("The entered position, %s, is greater than the current number of rules."), htmlspecialchars($position));
                } else {
                    $tmprule = $rules[$modifyRules[0]];
                    unset($rules[$modifyRules[0]]);
                    array_splice($rules, $position-1, 0, array($tmprule));
                    // Reindex
                    $rules = array_values($rules);
                    $haschanged = true;
                }
            }
            break;

        case 'duplicate':
            header("Location: $location/edit.php?edit=".$modifyRules[0]."&dup=1");
            exit;
            break;

        case 'insert':
            header("Location: $location/edit.php?addnew=1&position=".$modifyRules[0]);
            break;

        case 'sendemail':
            break;
            
        case 'enable':
            if(isset($rules[$modifyRules[0]]['disabled'])) {
                unset($rules[$modifyRules[0]]['disabled']);
                $haschanged = true;
            }
            break;

        case 'disable':
            $rules[$modifyRules[0]]['disabled'] = 1;
            $haschanged = true;
            break;
    }


    sqsession_register($rules, 'rules');
    
    /* Register changes to timsieved if we are not conservative in our
     * connections with him. */

    if ($conservative == false && $rules) {
        $newscript = makesieverule($rules);
        $s->login();
        $s->save($newscript, 'phpscript');
        avelsieve_spam_highlight_update($rules);
    }
}    

if (isset($_SESSION['returnnewrule'])) {
    /* There is a new rule to be added */
    $newrule = $_SESSION['returnnewrule'];
    unset($_SESSION['returnnewrule']);
    $rules[] = $newrule;
    $haschanged = true;
}

if( (!$conservative && isset($haschanged) ) ) {
    /* Commit changes */
    $s->login();
    $newscript = makesieverule($rules);
    $s->save($newscript, 'phpscript');
    avelsieve_spam_highlight_update($rules);
    if(isset($_SESSION['haschanged'])) {
        unset($_SESSION['haschanged']);
    }

}

if(isset($rules)) {
    $_SESSION['rules'] = $rules;
    $_SESSION['scriptinfo'] = $scriptinfo;
}

if(isset($sieve_loggedin)) {
    $sieve->sieve_logout();
}
    
/* This is the place to do a consistency check, after all changes have been
 * done. We also grab the list of all folders. */
    
// $folder_prefix = "INBOX";
sqgetGlobalVar('key', $key, SQ_COOKIE);
$imapConnection = sqimap_login($username, $key, $imapServerAddress, $imapPort, 0); 
$boxes = sqimap_mailbox_list_all($imapConnection);
sqimap_logout($imapConnection); 

// In this variable, various script "hints" are to be stored. This is to be passed on /
// used by the UI, for better usability.
global $scriptHints;
$scriptHints = array();
$scriptHints['inconsistent_folders'] = avelsieve_folder_consistency_check($boxes, $rules);
$scriptHints['vacation_rules'] = avelsieve_vacation_check($rules);

global $javascript_on;

/* -------------------- Presentation Logic ------------------- */

$prev = bindtextdomain ('squirrelmail', SM_PATH . 'locale');
textdomain ('squirrelmail');
if($popup) {
    displayHtmlHeader('');
} else {
    displayPageHeader($color, 'None');
}

$prev = bindtextdomain ('avelsieve', SM_PATH . 'plugins/avelsieve/locale');
textdomain ('avelsieve');

if(isset($_GET['mode'])) {
    if(array_key_exists($_GET['mode'], $displaymodes)) {
        $mode = $_GET['mode'];
    } else {
        $mode = $avelsieve_default_mode;
    }
    sqsession_register($mode, 'mode');
    setPref($data_dir, $username, 'avelsieve_display_mode', $mode);
} else {
    if( ($mode_tmp = getPref($data_dir, $username, 'avelsieve_display_mode', '')) != '') {
        if(array_key_exists($mode_tmp, $displaymodes)) {
            $mode = $mode_tmp;
        } else {
            $mode = $avelsieve_default_mode;
        }
    } else {
        $mode = $avelsieve_default_mode;
    }
}
    
$ht = new avelsieve_html_rules($rules, $mode);
if(!empty($errormsg)) {
    $ht->set_errmsg(array($errormsg));
    $ht->print_errmsg();
}

if($popup) {
    echo $ht->rules_confirmation();
} else {
    echo $ht->rules_table();
}

?>
</body></html>
