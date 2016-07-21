<?php
/**
 * User-friendly interface to SIEVE server-side mail filtering.
 * Plugin for Squirrelmail 1.4+
 *
 * This page is the main interface to editing and adding new rules.
 *
 * Licensed under the GNU GPL. For full terms see the file COPYING that came
 * with the Squirrelmail distribution.
 *
 * @version $Id: edit.php 1028 2009-05-22 10:01:15Z avel $
 * @author Alexandros Vellis <avel@users.sourceforge.net>
 * @copyright 2002-2009 Alexandros Vellis
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
    
include_once(SM_PATH . 'functions/imap.php');
require(SM_PATH . 'plugins/avelsieve/config/config.php');

$prev = bindtextdomain ('avelsieve', SM_PATH . 'plugins/avelsieve/locale');
textdomain ('avelsieve');

include_once(SM_PATH . 'functions/identity.php');
require_once(SM_PATH . 'plugins/avelsieve/include/constants.inc.php');
include_once(SM_PATH . 'plugins/avelsieve/include/html_rulestable.inc.php');
include_once(SM_PATH . 'plugins/avelsieve/include/html_ruleedit.inc.php');
include_once(SM_PATH . 'plugins/avelsieve/include/sieve_conditions.inc.php');
include_once(SM_PATH . 'plugins/avelsieve/include/sieve_actions.inc.php');
include_once(SM_PATH . 'plugins/avelsieve/include/sieve.inc.php');
include_once(SM_PATH . 'plugins/avelsieve/include/support.inc.php');
include_once(SM_PATH . 'plugins/avelsieve/include/styles.inc.php');

sqsession_is_active();

$errmsg = array();

/* Session / Server vars */
sqgetGlobalVar('sieve_capabilities', $sieve_capabilities, SQ_SESSION);
sqgetGlobalVar('key', $key, SQ_COOKIE);
sqgetGlobalVar('rules', $rules, SQ_SESSION);
/* Mode of operation */
sqgetGlobalVar('dup', $dup, SQ_GET & SQ_POST);
sqgetGlobalVar('addnew', $addnew, SQ_GET);
sqgetGlobalVar('position', $position, SQ_GET & SQ_POST);
/* New folder Creation */
sqgetGlobalVar('newfoldername', $newfoldername, SQ_POST);
if(isset($newfoldername)) $newfoldername = trim($newfoldername);
sqgetGlobalVar('newfolderparent', $newfolderparent, SQ_POST);

// intermediate_action is an action during an editing / creation of a rule,
// that does _not_ imply submitting the new rule data.
sqgetGlobalVar('intermediate_action', $intermediate_action, SQ_POST);

/* Essentials */
sqgetGlobalVar('popup', $popup, SQ_GET);
sqgetGlobalVar('edit', $edit, SQ_FORM);

sqgetGlobalVar('previoustype', $previoustype, SQ_POST);
sqgetGlobalVar('cond', $new_cond, SQ_POST);
sqgetGlobalVar('previous_cond', $previous_cond, SQ_POST);

sqgetGlobalVar('serialized_rule', $serialized_rule, SQ_GET);

sqgetGlobalVar('type', $type_get, SQ_GET);

$referrerUrl = $referrerArgs = '';
sqgetGlobalVar('referrerUrl', $referrerUrl, SQ_FORM);
sqgetGlobalVar('referrerArgs', $referrerArgs, SQ_FORM);
if(!empty($referrerArgs)) {
    $referrerArgs = unserialize($referrerArgs);
}

$base_uri = sqm_baseuri();

isset($popup) ? $popup = '?popup=1' : $popup = '';

$backend_class_name = 'DO_Sieve_'.$avelsieve_backend;
$s = new $backend_class_name;
$s->init();

/* If this page is called before table.php is ever shown, then we have to make
 * the current filtering rules available in the Session. This will happen when
 * a user clicks either:
 * i) creation of a rule from the message commands (while viewing a message)
 * ii) creation of a rule from some search criteria.
 */
if (!isset($rules)) {
    $s->login();
    /* Actually get the script 'phpscript' (hardcoded ATM). */
    if($s->load('phpscript', $rules, $scriptinfo)) {
        $_SESSION['rules'] = $rules;
        $_SESSION['scriptinfo'] = $scriptinfo;
    }
    $s->logout();
}

/* Create new mailbox, if required by the user. */
if(!empty($newfoldername)) {
    global $created_mailbox_name;
    $created_mailbox_name = '';
    avelsieve_create_folder($newfoldername, $newfolderparent, $created_mailbox_name, $errmsg);
}

/* Mode of operation */
if(isset($dup)) {
    $mode = 'duplicate';
} elseif(isset($addnew)) {
    $mode = 'addnew';
} else {
    $mode = 'edit';
}

if(is_numeric($type_get) && $type_get > 1 &&
    file_exists(SM_PATH . 'plugins/avelsieve/include/html_ruleedit.'.$type_get.'.inc.php')) {
    /**
     * Include the appropriate class for rule editing.
     */
    include_once(SM_PATH . 'plugins/avelsieve/include/html_ruleedit.'.$type_get.'.inc.php');
    $edit_class_name = 'avelsieve_html_edit_'. $type_get;

    /**
     * Some rules are unique only. Example: There is only one junk mail rule (#11) and
     * only one whitelist rule (#12). Here, we replace the 'addnew' mode with 'edit',
     * if the rule already exists.
     */
    if($avelsieve_maintypes[$type_get]['unique']) {
        foreach($rules as $no=>$rule) {
            if($rule['type'] == $type_get) {
                $mode = 'edit';
                $edit = $no;
            }
        }
    }

} else {
    $edit_class_name = 'avelsieve_html_edit';
}

$additional_options = array();
if(isset($position) && isset($rules[$position])) {
    $additional_options['position'] = $position;
}

$ruleobj = new $edit_class_name($s, $mode, $popup);
$ruleobj->set_errmsg($errmsg);

if(isset($edit)) {
    /* Editing an existing rule */
    $ruleobj->set_rule_type( (isset($rules[$edit]['type']) ? $rules[$edit]['type'] : 1));
    $ruleobj->set_rule_data($rules[$edit]);

} elseif(isset($serialized_rule)) {
    /* Adding a new rule through $_GET, e.g. from search integration feature. */
    $ruleobj->set_rule_type($type_get);
    $ruleobj->set_rule_data(unserialize(urldecode($serialized_rule)));

} elseif(!isset($edit) && isset($type_get) && empty($_POST)) {
    /* Adding a new rule through $_GET */
    $ruleobj->set_rule_type($type_get);
    if(isset($avelsieve_rules_settings[$type_get]['default_rule'])) {
        $ruleobj->set_rule_data($avelsieve_rules_settings[$type_get]['default_rule']);
    }
    $ruleobj->process_input($_GET, true);

} elseif(!isset($edit) && isset($type_get) && !empty($_POST)) {
    /* Continuing to add a new rule */
    $ruleobj->set_rule_type($type_get);
} else {
    /* Adding a new rule from scratch */
    $ruleobj->set_rule_type($type_get);
}
$type = $ruleobj->type;

if(!isset($type) || (isset($type) && !is_numeric($type)) ) $type = 1;

/* TODO - use a snippet like this to change type from the edit UI */
/*
if(isset($previoustype) && (
    $previoustype == 0 ||
    (isset($type) && $previoustype != $type)
  )) {
        $changetype = true;
} else {
        $changetype = false;
}
*/

/* This is for determining if the test of a specific condition has changed */
$changetype = false;

if(isset($previous_cond) && isset($new_cond)) {
    foreach($previous_cond as $n=>$t) {
        if(isset($new_cond[$n]['type']) && $t['type'] != $new_cond[$n]['type']) {
            $changetype = true;
        }
    }
}

/* Calculate referrer URL, if it exists. */

if(!empty($referrerUrl)) {
    $ruleobj->set_referrer($referrerUrl, $referrerArgs);
    $redirectUrl = $referrerUrl;
    if(!empty($referrerArgs)) {
        $xtra = array();
        foreach($referrerArgs as $k=>$v) {
            $xtra[] = $k.'='.$v;
        }
        if(strstr($redirectUrl, '?')) {
            $redirectUrl .= '&' . implode('&', $xtra);
        } else {
            $redirectUrl .= '?' . implode('&', $xtra);
        }
    }
} else {
    $redirectUrl = "table.php$popup";
}

/* Available Actions that occur if submitting the form in a number of ways */

if(isset($_POST['cancel'])) {
    /* Cancel Editing */
    header("Location: $redirectUrl");
    exit;

} elseif(isset($_POST['apply']) && !$changetype) {
    /* Apply change in existing rule */
    //print "/* Apply change in existing rule */";
    $ruleobj->process_input($_POST, true);
    if(empty($ruleobj->errmsg)) {
        $_SESSION['rules'][$edit] = $ruleobj->rule;
        if($rawSuccessMsg = $ruleobj->getSuccessMessage()) {
            $_SESSION['comm']['raw'] = $rawSuccessMsg;
        } else {
            $_SESSION['comm']['edited'] = $edit;
        }
        $_SESSION['haschanged'] = true;
        header("Location: $redirectUrl");
        exit;
    }

} elseif(isset($_POST['addnew']) && !$changetype) {
    /* Add new rule */
    $ruleobj->process_input($_POST, true);

    if(empty($ruleobj->errmsg)) {
        if(isset($dup)) {
            // insert moving rule in place
            array_splice($_SESSION['rules'], $edit+1, 0, array($ruleobj->rule));
            // Reindex
            $_SESSION['rules'] = array_values($_SESSION['rules']);
        } elseif(isset($position)) {
            array_splice($_SESSION['rules'], $position+1, 0, array($ruleobj->rule));
            $_SESSION['rules'] = array_values($_SESSION['rules']);
        } else {
            // Append the new rule at the end of rules table
            $_SESSION['rules'][] = $ruleobj->rule;
        }
        /* Communication: */
        if($rawSuccessMsg = $ruleobj->getSuccessMessage()) {
            $_SESSION['comm']['raw'] = $rawSuccessMsg;
        } else {
            $_SESSION['comm']['edited'] = $edit;
            $_SESSION['comm']['new'] = true;
        }
        $_SESSION['haschanged'] = true;
        header("Location: $redirectUrl");
        exit;
    }
} elseif($changetype || isset($_POST['append']) || isset($_POST['less']) || isset($intermediate_action)) {
    /* still in editing; apply any changes. */
    $ruleobj->process_input($_POST, false);
}

/* Grab the list of my IMAP folders. This is only needed for the GUI, and is
 * done as the last step. */
sqgetGlobalVar('delimiter', $delimiter, SQ_SESSION);
if(!isset($delimiter)) {
    $delimiter = sqimap_get_delimiter($imapConnection);
}
// $folder_prefix = "INBOX";
$imapConnection = sqimap_login($username, $key, $imapServerAddress, $imapPort, 0); 
if($SQM_INTERNAL_VERSION[0] == 1 && $SQM_INTERNAL_VERSION[1] == 5) {
    /* In Squirrelmail 1.5.x, use sqimap_mailbox_list() with
     * $show_only_subscribed_folders flag off. Thanks to Simon Matter */
    global $show_only_subscribed_folders;
    $old_show_only_subscribed_folders = $show_only_subscribed_folders;
    $show_only_subscribed_folders = false;
    $boxes = sqimap_mailbox_list($imapConnection,true);
    /* Restore correct folder cache */
    $show_only_subscribed_folders = $old_show_only_subscribed_folders;
    $dummy = sqimap_mailbox_list($imapConnection,true);

} else {
    /* In Squirrelmail 1.4.x, use sqimap_mailbox_list_all() */
    $boxes = sqimap_mailbox_list_all($imapConnection);
}
sqimap_logout($imapConnection); 


/* -------------- Presentation Logic ------------- */

$prev = bindtextdomain ('squirrelmail', SM_PATH . 'locale');
textdomain ('squirrelmail');
if($popup) {
    displayHtmlHeader('');
} else {
    displayPageHeader($color, 'None');
}

$prev = bindtextdomain ('avelsieve', SM_PATH . 'plugins/avelsieve/locale');
textdomain ('avelsieve');

if(isset($edit)) {
    echo $ruleobj->edit_rule($edit);
} else {
    echo $ruleobj->edit_rule();
}
    
?>
</body></html>
