<?php
/**
 * User-friendly interface to SIEVE server-side mail filtering.
 * Plugin for Squirrelmail 1.4+
 *
 * Licensed under the GNU GPL. For full terms see the file COPYING that came
 * with the Squirrelmail distribution.
 *
 * @version $Id: index.php 935 2008-07-04 10:25:39Z avel $
 * @author Alexandros Vellis <avel@users.sourceforge.net>
 * @copyright 2004 The SquirrelMail Project Team, Alexandros Vellis
 * @package plugins
 * @subpackage avelsieve
 */

/** Includes */
require_once('../../functions/strings.php');
$location = get_location();
header("Location: $location/src/webmail.php");
exit();

?>
