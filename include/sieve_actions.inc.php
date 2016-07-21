<?php
/**
 * This script includes all avelsieve_action classes.
 *
 * Licensed under the GNU GPL. For full terms see the file COPYING that came
 * with the Squirrelmail distribution.
 *
 * @version $Id: sieve_actions.inc.php 1023 2009-05-21 08:08:45Z avel $
 * @author Alexandros Vellis <avel@users.sourceforge.net>
 * @copyright 2002-2009 Alexandros Vellis
 * @package plugins
 * @subpackage avelsieve
 */

/** Include Base Avelsieve Action class */
include_once(SM_PATH . 'plugins/avelsieve/include/avelsieve_action.class.php');

/** Include Avelsieve Action classes (Radio-buttons) */
include_once(SM_PATH . 'plugins/avelsieve/include/avelsieve_action_keep.class.php');
include_once(SM_PATH . 'plugins/avelsieve/include/avelsieve_action_discard.class.php');
include_once(SM_PATH . 'plugins/avelsieve/include/avelsieve_action_reject.class.php');
include_once(SM_PATH . 'plugins/avelsieve/include/avelsieve_action_redirect.class.php');
include_once(SM_PATH . 'plugins/avelsieve/include/avelsieve_action_fileinto.class.php');
include_once(SM_PATH . 'plugins/avelsieve/include/avelsieve_action_vacation.class.php');

/** Include Avelsieve Additional Action classes (Checkboxes) */
include_once(SM_PATH . 'plugins/avelsieve/include/avelsieve_action_stop.class.php');
include_once(SM_PATH . 'plugins/avelsieve/include/avelsieve_action_notify.class.php');
include_once(SM_PATH . 'plugins/avelsieve/include/avelsieve_action_imapflags.class.php');
include_once(SM_PATH . 'plugins/avelsieve/include/avelsieve_action_keepdeleted.class.php');

/** Include Custom Avelsieve Action classes */
include_once(SM_PATH . 'plugins/avelsieve/include/avelsieve_action_junk.class.php');
include_once(SM_PATH . 'plugins/avelsieve/include/avelsieve_action_trash.class.php');

/** Include other Avelsieve Action classes (Checkboxes) */
include_once(SM_PATH . 'plugins/avelsieve/include/avelsieve_action_disabled.class.php');

