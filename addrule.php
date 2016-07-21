<?php
/**
 * User-friendly interface to SIEVE server-side mail filtering.
 * Plugin for Squirrelmail 1.4+
 *
 * Wizard-like form for adding new rules.
 *
 * Licensed under the GNU GPL. For full terms see the file COPYING that came
 * with the Squirrelmail distribution.
 *
 * @version $Id: addrule.php 935 2008-07-04 10:25:39Z avel $
 * @author Alexandros Vellis <avel@users.sourceforge.net>
 * @copyright 2004 The SquirrelMail Project Team, Alexandros Vellis
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
    include_once(SM_PATH . 'functions/imap.php');
    include_once(SM_PATH . 'functions/date.php');
}

include_once(SM_PATH . 'functions/identity.php');
include_once(SM_PATH . 'plugins/avelsieve/config/config.php');
include_once(SM_PATH . 'plugins/avelsieve/include/support.inc.php');
include_once(SM_PATH . 'plugins/avelsieve/include/html_ruleedit.inc.php');
include_once(SM_PATH . 'plugins/avelsieve/include/sieve.inc.php');

sqsession_is_active();

if(isset($_SESSION['newrule'])) {
	$newrule = $_SESSION['newrule'];
}

if(isset($_POST['cancel'])) {
	unset($_SESSION['newrule']);
	unset($part);
	if (isset($_SESSION['part']))
		unset($_SESSION['part']);
	header("Location: ./table.php");
	exit;
}

/* Import variables */

sqgetGlobalVar('sieve_capabilities', $sieve_capabilities, SQ_SESSION);

/* Where are we? (aka $part)
   * not set or 1 => 1/4 to 2/4
   * 2            => 2/4 to 3/4
   * 3            => 3/4 to 4/4
   * 4            => 4/4 to end.
*/

/* Set up locale, for the error messages. */
$prev = bindtextdomain ('avelsieve', SM_PATH . 'plugins/avelsieve/locale');
textdomain ('avelsieve');

if(isset($_SESSION['part'])) {
	$part = $_SESSION['part'];
}

if(isset($_POST['startover']) ||  !isset($_SESSION['part']) || isset($_POST['add']) ) {
	$part = "1";
	$_SESSION['part'] = 1;
}

if(isset($_POST['next'])) {
	$part++;
} elseif(isset($_POST['prev'])) {
	$part--;
}

if($part==2 && isset($_POST['next'])) {
	$newrule['type'] = $_POST['type']; 
}

if($part==3 && $newrule['type'] == 2  &&
  ( !isset($_POST['headermatch'][0]) || trim($_POST['headermatch'][0]) == "" ) ) {
	$addrule_error =  _("You have to define at least one header match text.");
	$part--;
}

if(isset($_POST['append'])) {
	$part=2;
	if (!isset($_POST['items'])) {
		$items = $startitems;
	} else {
		$items = $_POST['items'] + 1;
	}
} elseif(isset($_POST['less'])) {
	$part=2;
	if (!isset($_POST['items'])) {
		$items = $startitems;
	} else {
		$items = $_POST['items'] - 1;
	}
} else {
	$items = $startitems;
}

if(isset($newrule) && ($newrule['type']==4) && ($part==2)) {
 	$part++;

}


/* End of "where we are" thingie */



/* If user asked to create a new folder.
 * Variables:
 *	$folder_name = name of folder to create
 *	$subfolder   = name of folder under which to create $folder_name
*/

if(isset($_POST['action']) && ($_POST['action'] == 5) && 
  (isset($_POST['newfolder']) && $_POST['newfolder'] == "5b" ) &&
  !isset($_POST['startover']) && !isset($_POST['cancel']) ) {

	$errmsg = avelsieve_create_folder($_POST['folder_name'], $_POST['subfolder'], &$mailbox);
	if($errmsg) {
		/* There was some error. Remain in the same page. */
		$part--;
	} else {
		$folder = $mailbox;
		$_SESSION['folder'] = $mailbox;
		$newrule['folder'] = $mailbox;
	}
}


/* Register the 'where are we' thingie to the session. */

$_SESSION['part'] = $part;



if(!isset($_POST['finished'])) {

if(isset($newrule)) {
switch ($newrule['type']) { 
	case "1":
		$vars = array( 'address', 'addressrel');
		foreach($vars as $myvar) {
			$newrule[$myvar]= ${$myvar};
		}
		break;
	case "2":
		/* Decide how much of the items to use for the rule, based on
		 * the first zero variable to be found. */

		if(isset($_POST['headermatch'][0])) {
		for ($i=0; $i<sizeof($_POST['headermatch']) ; $i++) {
			if ($_POST['headermatch'][$i]) {
				$newrule['header'][$i] = trim($_POST['header'][$i]);
				$newrule['matchtype'][$i] = trim($_POST['matchtype'][$i]);
				$newrule['headermatch'][$i] = trim($_POST['headermatch'][$i]);
				if($i>0) {
					$newrule['condition'] = $_POST['condition'];
				}
			} elseif (!$_POST['headermatch'][$i]) { /* End */
				break 1;
			} else {
				//print "Huh?"; 
			}
		}
		} else {
			/* More error checking here. ? */
		}
		break;

	case "3":
		if(isset($_POST['sizeamount'])) {
			$vars = array( 'sizerel', 'sizeamount', 'sizeunit');
			foreach($vars as $myvar) {
				$newrule[$myvar]= $_POST[$myvar];
			}
		}
		break;

	case "4":
		$dont = "1";
		break;

	default:
		$dont = "1";
		break;
}
}

if(isset($_POST['action'])) {
/* What was && ($new_folder != "5b" )  ???? */
	switch ($_POST['action']) { 
		case "1": /* keep */
		case "2": /* discard */
			$vars = array( 'action');
			break;
		case "3": /* reject w/ excuse */
			$vars = array( 'action', 'excuse');
			break;
		case "4": /* redirect */
			$vars = array( 'action', 'redirectemail', 'keep');
			break;
		case "5": /* fileinto */
			$vars = array( 'action', 'keepdeleted');
			if($_POST['newfolder'] != "5b") {
				$vars = array_merge($vars, array('folder'));
			}
			break;
		case "6": /* vacation */
			$vars = array( 'action', 'vac_addresses', 'vac_days', 'vac_message');
			break;
		default:
			print "Invalid action value!";
			break;
	}
	if(isset($_POST['stop'])) {
		$vars = array_merge($vars, array('stop'));
	}
	if(isset($_POST['notifyme'])) {
		$vars = array_merge($vars, array('notify'));
	}

	foreach($vars as $myvar) {
		// print "DEBUG: Putting vars... newrule[".$myvar."] = " . $_POST[$myvar] . " <br />"; 
		if(isset($_POST[$myvar])) {
			$newrule[$myvar]= $_POST[$myvar];
		}
	}
}

} /* !$_POST['finished'] */

if(isset($_POST['finished']) || isset($_POST['apply'])) {

	/* New rule to transfer to table.php: */
	$_SESSION['returnnewrule'] = $newrule;
	// urlencode(base64_encode(serialize($_SESSION['newrule'])));

	/* Communication: */
	$_SESSION['comm']['new'] = true;

	/* Remove addrule.php stuff */
	unset($_SESSION['newrule']);
	unset($_SESSION['part']);

	/* go to table.php */
	session_write_close();
	header('Location: table.php');
	exit;
}

if(isset($newrule)) {
	$_SESSION['newrule'] = $newrule;
}

session_write_close();
// session_register('part');

/* END SESSION CODE */

/* ----------------- start printing --------------- */

$prev = bindtextdomain ('squirrelmail', SM_PATH . 'locale');
textdomain ('squirrelmail');

// displayPageHeader($color, 'None', $xtra);
displayPageHeader($color, 'None');

$prev = bindtextdomain ('avelsieve', SM_PATH . 'plugins/avelsieve/locale');
textdomain ('avelsieve');

print '
<script language="JavaScript" type="text/javascript">
function checkOther(id){
	for(var i=0;i<document.addrule.length;i++){
		if(document.addrule.elements[i].value == id){
			document.addrule.elements[i].checked = true;
		}
	}
}
// -->
</script>
';

include "constants.php";

printheader2( _("Add New Rule") );
avelsieve_printheader();
print_all_sections_start();

/* BEGIN DEBUG CODE */
//print "<pre> DEBUG: Session "; print_r($_SESSION); print "</pre>";
//print "<pre> DEBUG: HTTP Post "; print_r($_POST); print "</pre>";
/* END DEBUG CODE */

switch ($part) {

case "1":
	print_section_start( _("New Rule Wizard - Step") . " " . $part . " " . _("of") . " 4: " . _("Rule Type") );
	print_1_ruletype();
	break;

case "2":	
	print_section_start( _("New Rule Wizard - Step") . " " . $part . " " . _("of") . " 4: " . _("Condition") );
	switch ($newrule['type']) {
	
		case "1":
			print_2_1_addressmatch();
			break;

		case "2":
			print_2_2_headermatch($items);
			break;

		case "3":
			print_2_3_sizematch();
			break;

		case "4": /* redundant */
			print 'Move on to the next step. Nothing to see here. :-)';
			break;

		default:
			print 'Oops, some error here. This shouldnt have happened.';
			print $newrule['type'];
			printnakedfooter();
			exit;
			break;

		}
	break;		/* Im getting dizzy */


case "3":

	global $mailboxlist, $delimiter, $emailaddresses;

	if(isset($_SESSION['delimiter'])) {
		$delimiter = $_SESSION['delimiter'];
	} else { /* These aren't likely to be executed.. just in case... */
		$delimiter = sqimap_get_delimiter($imapConnection);
		$_SESSION['delimiter'] = $delimiter;
	}

	// $cleartext_password = OneTimePadDecrypt($key, $onetimepad);
	
	// $folder_prefix = "INBOX";
	
	if(!isset($boxes)) {
		$imapConnection = sqimap_login($username, $_COOKIE['key'], $imapServerAddress, $imapPort, 1); 
		$boxes = sqimap_mailbox_list_all($imapConnection);
	
		/* If we do not have append permission to some folders, use
		 * separate structures */
		if(in_array('useracl', $plugins)) {
			include_once(SM_PATH.'plugins/useracl/imap_acl.php');

			for ($i = 0; $i < count($boxes); $i++) {
				$boxes[$i]['acl'] = sqimap_myrights($imapConnection, $boxes[$i]['unformatted-dm']);
				/* Append permission for target folders */
				if(strstr($boxes[$i]['acl'], 'i')) {
					$boxes_append[] = $boxes[$i];
				}
				/* Admin permission for parent folders */
				if(strstr($boxes[$i]['acl'], 'a')) {
					$boxes_admin[] = $boxes[$i];
				}
			}
		}
	}
		
	if(isset($imapConnection)) {
		sqimap_logout($imapConnection); 
	}
	
	$emailaddresses = get_user_addresses();

	print_section_start( _("New Rule Wizard - Step") . " " . $part . " " . _("of") . " 4: " . _("Action") );
	
	print_3_action();
	break;


case "4":
	$sieverule = makesinglerule($newrule,"rule");
	$text = makesinglerule($newrule,"verbose");
	
	print_section_start( _("New Rule Wizard - Step") . " " . $part . " " . _("of") . " 4: " . _("Confirmation") );

	if($newrule['type']==4 && $newrule['action']==1 &&
	   !isset($newrule['notify'])) {

		print _("<p>You have chosen to keep every mail. This is the default action anyway, so you might want to start over with a rule that makes more sense. Select &quot;finished&quot; to save this rule nonetheless.</p>");
		break;
	}
	
	print_4_confirmation();
	break;

default:
	print '<p>';
	print _("Please use the buttons on the bottom instead of your browser's reload, back and forward buttons, to build a rule.");
	print '</p>';
	break;

}

print_section_end();
print_all_sections_end();
printaddbuttons();
printfooter2();

?>
