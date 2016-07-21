<?php
/**
 * User-friendly interface to SIEVE server-side mail filtering.
 * Plugin for Squirrelmail 1.4+
 *
 * Licensed under the GNU GPL. For full terms see the file COPYING that came
 * with the Squirrelmail distribution.
 *
 * @version $Id: constants.inc.php 1054 2009-05-28 13:53:23Z avel $
 * @author Alexandros Vellis <avel@users.sourceforge.net>
 * @copyright 2004-2007 The SquirrelMail Project Team, Alexandros Vellis
 * @package plugins
 * @subpackage avelsieve
 */

/** Email where bug reports should be sent. */
define('AVELSIEVE_BUGREPORT_EMAIL', 'avelsieve_bug_report@edunet.gr');

$conditions = array(
    "and" => _("AND (Every item must match)"),
    "or" => _("OR (Either item will match)")
);

global $avelsieve_maintypes;
$avelsieve_maintypes = array(
        1 => array(
                'desc' =>_("Rule"),
                'linktext' => sprintf( _("Add a new %s"), _("Rule")),
                'img' => 'images/icons/add.png',
                'unique' => false,
                'undeletable' => false,
        ),
        10 => array(
                'desc' =>  _("SPAM Rule"),
                'linktext' => sprintf( _("Add a new %s"), _("SPAM Rule")),
                'img' => 'images/icons/add.png',
                'unique' => false,
                'undeletable' => false,
        ),
        11 => array(
                'desc' => _("Junk Mail Rule"),
                'linktext' => sprintf( _("Edit %s"), _("Junk Mail Rule")),
                'img' => 'images/icons/email_edit.png',
                'unique' => true,
                'undeletable' => true,
        ),
        12 => array(
                'desc' => _("Whitelist"),
                //'linktext' => sprintf( _("Edit %s"), _("Whitelist")),
                'img' => 'images/icons/email_edit.png',
                'unique' => true,
                'undeletable' => false,
        ),
        13 => array(
                'desc' => _("Whitelist"),
                'linktext' => sprintf( _("Add new %s"), _("Sieve Code")),
                'img' => 'images/icons/add.png',
                'unique' => false,
                'undeletable' => false,
        ),
);

$types = array(
    'address' => array(
        'order' => 1,
        'name' => _("Address"),
        'description' => _("Perform an action depending on email addresses appearing in message headers.")
    ),
    'header' => array(
        'order' => 0,
        'name' => _("Header"),
        'description' => _("Perform an action on messages matching a specified header (From, To etc.).")
    ),
    'envelope' => array(
        'order' => 2,
        'name' => _("Envelope"),
        'description' => _("Perform an action on messages matching a specified envelope header (Envelope FROM, TO)."),
        'dependencies' => array('envelope')
    ),
    'size' => array(
        'order' => 5,
        'name' => _("Size"),
        'description' => _("Perform an action on messages depending on their size.")
    ),
    'body' => array(
        'order' => 3,
        'name' => _("Body"),
        'description' => _("Perform an action on messages depending on their content (body text)."),
        'dependencies' => array('body')
    ),
    'datetime' => array(
        'order' => 4,
        'name' => _("Date"),
        'description' => _("Perform an action on messages depending on date or time related to the message."),
        'dependencies' => array('date')
    ),
    'all' => array(
        'order' => 10,
        'name' => _("All"),
        'description' => _("Perform an action on <strong>all</strong> incoming messages.")
    )
);

$avelsieve_actions = array(
    'keep', 'fileinto', 'redirect', 'reject', 'discard', 'vacation'
);
$additional_actions = array(
    'stop', 'notify', 'imapflags', 'keepdeleted', 'disabled'
);


$matchtypes = array(
    "contains" => _("contains"),
    "does not contain" => _("does not contain"),
    "is" => _("is"),
    "is not" => _("is not"),
    "matches" => _("matches") . " " . _("wildcard"),
    "does not match" => _("does not match") . " " . _("wildcard")
);

$matchregex = array(
    'regex' => _("matches") . " " . _("regexp"),
    'not regex' => _("does not match") . " " . _("regexp")
);


$comparators = array(
    'gt' => '>  ' . _("is greater than"),
    'ge' => '=> ' . _("is greater or equal to"),
    'lt' => '<  ' . _("is lower than"),
    'le' => '<= ' . _("is lower or equal to"),
    'eq' => '=  ' . _("is equal to"),
    'ne' => '!= ' . _("is not equal to")
) ;

$displaymodes = array(
    'verbose' => array( _("verbose"), _("Textual descriptions of the rules")),
    'terse' => array( _("terse"), _("More suitable for viewing the table of rules at once")),
    'source' => array( _("source"), _("Display SIEVE source"))
);

if(AVELSIEVE_DEBUG > 0) {
    $displaymodes['debug'] = array('debug', 'Debugging mode (avelsieve variables)');
}

global $implemented_capabilities;
$implemented_capabilities = array('fileinto', 'envelope', 'reject', 'vacation', 'imapflags', 'imap4flags', 'relational', 'regex', 'notify', 'body', 'date', 'index');

global $cap_dependencies;  
$cap_dependencies['relational'] = array("comparator-i;ascii-numeric");

global $prioritystrings;
$prioritystrings = array(
    'low' => _("Low"),
    'normal' => _("Normal"),
    'high' => _("High")
);

/* Tools (Icons in table.php) */
global $imagetheme;
switch($imagetheme) {
    case 'famfamfam':
        $fmt = 'png';
        break;
    default:
        $fmt = 'gif';
        break;
}


$avelsievetools = array(
    'rm' => array(
        'desc' => _("Delete"),
        'img' => "del.$fmt"
        ),
    'edit' => array(
        'desc' => '<strong>' . _("Edit") . '</strong>',
        'img' => "edit.$fmt"
        ),
    'dup' => array(
        'desc' => _("Duplicate"),
        'img' => "dup.$fmt"
        ),
    'mvup' => array(
        'desc' => _("Move Up"),
        'img' => "up.$fmt"
        ),
    'mvtop' => array(
        'desc' => _("Move to Top"),
        'img' => "top.$fmt"
        ),
    'mvdn' => array(
        'desc' => _("Move Down"),
        'img' => "down.$fmt"
        ),
    'mvbottom' => array(
        'desc' => _("Move to Bottom"),
        'img' => "bottom.$fmt"
        )
);
    

global $spamrule_actions;
$spamrule_actions = array(
    // FIXME - number of days in this message.
    'junk' => array(
        'short' => _("Junk Folder"),
        'desc' => sprintf( _("Store SPAM message in your Junk Folder. Messages older than %s days will be deleted automatically."), 7)
        ),
    'trash' => array(
        'short' => _("Trash Folder"),
        'desc' => _("Store SPAM message in your Trash Folder. You will have to purge the folder yourself.")
        ),

    'discard' => array(
        'short' => _("Discard"),
        'desc' => _("Discard SPAM message. You will get no indication that the message ever arrived.")
        )
);

/* Version Info for SIEVE scripts */
global $avelsieve_version;
$avelsieve_version = array(
    'major' => 1,
    'minor' => 9,
    'release' => 9,
    'string' => "1.9.9"
);

$available_envelope = array('from', 'to');

global $avelsieve_enable_envelope_auth;
if($avelsieve_enable_envelope_auth) {
    $available_envelope[] = 'auth';
}

/* Headers that typically include email addresses, for the :address check */
global $available_address_headers;
$available_address_headers = array(
    'From', 'To', 'Cc', 'Bcc', 'Reply-To', 'Sender', 'Resent-From', 'Resent-To'
);

