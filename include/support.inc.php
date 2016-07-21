<?php
/**
 * User-friendly interface to SIEVE server-side mail filtering.
 * Plugin for Squirrelmail 1.4+
 *
 * Licensed under the GNU GPL. For full terms see the file COPYING that came
 * with the Squirrelmail distribution.
 *
 * Various support functions, useful or useless.
 *
 * @version $Id: support.inc.php 1020 2009-05-13 14:10:13Z avel $
 * @author Alexandros Vellis <avel@users.sourceforge.net>
 * @copyright 2004-2007 The SquirrelMail Project Team, Alexandros Vellis
 * @package plugins
 * @subpackage avelsieve
 */

/**
 * Delete element from array.
 */
function array_del($array, $place) {
    $newarray = array();
    $n=0;
    for ($i=0; $i<sizeof($array); $i++)
        if ($i!=$place) 
            $newarray[$n++] = $array[$i];
    return $newarray;
} 


/**
 * Swap values of two elements in array.
 */
function array_swapval ($array, $i, $j) {
    $temp[$i] = $array[$j];
    $temp[$j] = $array[$i];

    $array[$i] = $temp[$i];
    $array[$j] = $temp[$j];

    return $array;
}

/**
 * This plugin's error display function.
 */
function print_errormsg($errormsg) {
    include_once(SM_PATH . 'functions/display_messages.php');
    global $color;
    error_box ($errormsg, $color);
    exit;
}

/**
 * Create a new folder: wrapper function for avelsieve.
 *
 * @param string $foldername
 * @param string $subfolder
 * @param string $created_mailbox_name
 * @param array $errmsg Array of error messages, in which to append an error
 *   message if it occurs.
 * @return boolean True upon success, otherwise false.
 * @todo Check for folders that already exist
 */
function avelsieve_create_folder($foldername, $subfolder = '', &$created_mailbox_name, &$errmsg) {
    /* Copy & paste magic (aka kludge) */
    global $mailboxlist, $delimiter, $username, $imapServerAddress, $imapPort, $imapConnection;
    
    sqgetGlobalVar('key', $key, SQ_COOKIE);
    sqgetGlobalVar('onetimepad', $onetimepad, SQ_SESSION);

    if(!isset($delimiter) && isset($_SESSION['delimiter'])) {
        $delimiter = $_SESSION['delimiter'];
    } else { /* Just in case... */
        if(!isset($imapConnection)) {
            $imapConnection = sqimap_login($username, $key, $imapServerAddress, $imapPort, 0); 
        }
        $delimiter = sqimap_get_delimiter($imapConnection);
        $_SESSION['delimiter'] = $delimiter;
    }

    if(isset($foldername) && trim($foldername) != '' ) {
        $foldername = imap_utf7_encode_local(trim($foldername));
    } else {
        $errmsg[] = _("You have not defined the name for the new folder.") .
                ' ' . _("Please try again.");
        return false;
    }

    if(empty($subfolder)) {
        $subfolder = "INBOX";
    }

    if (strpos($foldername, "\"") || strpos($foldername, "\\") ||
    strpos($foldername, "'") || strpos($foldername, "$delimiter")) {
        $errmsg[] = _("Illegal folder name.  Please select a different name"); 
        return false;
    }

    if (isset($contain_subs) && $contain_subs ) {
        $foldername = "$foldername$delimiter";
    }

    // $folder_prefix = "INBOX";
    $folder_prefix = '';

    if (!empty($folder_prefix) && (substr($folder_prefix, -1) != $delimiter)) {
        $folder_prefix = $folder_prefix . $delimiter;
    }
    if ($folder_prefix && (substr($subfolder, 0, strlen($folder_prefix)) != $folder_prefix)){
        $subfolder_orig = $subfolder;
        $subfolder = $folder_prefix . $subfolder;
    } else {
        $subfolder_orig = $subfolder;
    }
    if (trim($subfolder_orig) == '') {
        $mailbox = $folder_prefix.$foldername; 
    } else {
        $mailbox = $subfolder.$delimiter.$foldername;
    }
    /*    if (strtolower($type) == 'noselect') {
            $mailbox = $mailbox.$delimiter;
        }
    */
    /* Actually create the folder. */
        
    if(!isset($imapConnection)) {
        $imapConnection = sqimap_login($username, $key, $imapServerAddress, $imapPort, 0);
    }

    /* Here we could do some more error checking to see if the
     * folder already exists. If it exists, the creation will not
     * do anything ANW, so it works well as it is. It can be made
     * better, e.g. by printing a notice "Note that the folder you
     * wanted to create already exists". */
    
    // $boxes = sqimap_mailbox_list($imapConnection);

    /* Instead of using sqimap_mailbox_create(), I use sqimap_run_command so
     * that I will put 'false' in the error handling. */

    $response = '';
    $message = '';

    $read_ary = sqimap_run_command($imapConnection, "CREATE \"$mailbox\"", false, $response, $message);
       sqimap_subscribe ($imapConnection, $mailbox);

    if(strtolower($response) != 'ok') {
        $errmsg[] = $message;
        return false;
    }
    $created_mailbox_name = $mailbox;
    return true;
}

/**
 * Print mailbox select widget.
 * 
 * @param string $selectname name for the select HTML variable
 * @param string $selectedmbox which mailbox to be selected in the form
 * @param boolean $sub 
 */
function mailboxlist($selectname, $selectedmbox, $sub = false) {
    
    global $boxes_append, $boxes_admin, $imap_server_type,
    $default_sub_of_inbox;
    
        if(isset($boxes_admin) && $sub) {
            $boxes = $boxes_admin;
        } elseif(isset($boxes_append)) {
            $boxes = $boxes_append;
        } else {
            global $boxes;
        }
        
        if (count($boxes)) {
            $mailboxlist = '<select name="'.$selectname.'" onclick="checkOther(\'5\');" >';
        
            if($sub) {
            if ($default_sub_of_inbox == false ) {
                $mailboxlist = $mailboxlist."\n".'<option selected value="">[ '._("None")." ] </option>\n";    
            }
            }
    
            for ($i = 0; $i < count($boxes); $i++) {
                    $box = $boxes[$i]['unformatted-dm'];
                    $box2 = str_replace(' ', '&nbsp;', $boxes[$i]['formatted']);
                    //$box2 = str_replace(' ', '&nbsp;', $boxes[$i]['formatted']);
    
                    if (strtolower($imap_server_type) != 'courier' || strtolower($box) != 'inbox.trash') {
                        $mailboxlist .= '<option value="'.htmlspecialchars($box).'"';
                if($selectedmbox == $box) {
                    $mailboxlist .= ' selected="SELECTED"';
                }
                $mailboxlist .= '>'.$box2."</option>\n";
                    }
            }
            $mailboxlist .= "</select>\n";
    
        } else {
            $mailboxlist = "No folders found.";
        }
        return $mailboxlist;
}

/**
 * Get user's email addresses (from all identities). They are to be used in the
 * vacation ":address" field.
 * @return string A string with comma-separated email addresses
 */
function get_user_addresses() {
    $idents = get_identities();
    foreach($idents as $identity) {
        $emailaddresses[] = $identity['email_address'];
    }
    return implode(",", $emailaddresses);

    /* Rest of the code in this function is probably obsolete */
    
    global $data_dir, $username, $ldapuserdatamode;
    $default_emailaddress = getPref($data_dir, $username, 'email_address');

    if ($ldapuserdatamode) {
        /* Get user's email addresses from LDAP Prefs Backend plugin's cache */
        $addressarray[] = $default_emailaddress;

        if (isset($_SESSION['alternateemails'])) {
            $alternateemails = $_SESSION['alternateemails'];
            for ($i=0; $i<sizeof($alternateemails); $i++) {
                $addressarray[] = $alternateemails[$i];
            }
            $emailaddresses = implode(",", $addressarray);
        } else {
            $emailaddresses = $default_emailaddress;
        }
        
    } else {
        /* Normal Mode; get email address from user's prefs and from
         * user's possible identities. */
        
        $emailaddresses = $default_emailaddress;

        $idents = getPref($data_dir, $username, 'identities');
        if ($idents != '' && $idents > 1) {
            for ($i = 1; $i < $idents; $i ++) {
                $cur_email_address = getPref($data_dir, $username, 'email_address' . $i);
                $cur_email_address = strtolower(trim($cur_email_address));
                $emailaddresses = $emailaddresses . ',' . $cur_email_address;
            }
        }
    }
    return $emailaddresses;
}
 
/** 
 * Escape only double quotes and backslashes, as required by SIEVE RFC. For the
 * reverse procedure, PHP function stripslashes() will do.
 *
 * @param string $script
 * @return string
 */
function avelsieve_addslashes($string) {
    /* 1) quoted string
     * 2) str_replace
     * 3) sieve.lib.php
     * 4) .....
     */
    $temp =  str_replace("\\", "\\\\\\\\\\\\\\\\", $string);
    return str_replace('"', "\\\\\\\\\"", $temp);
}

