<?php
/**
 * User-friendly interface to SIEVE server-side mail filtering.
 * Plugin for Squirrelmail 1.4+
 *
 * This page will load in MANAGESIEVE and SIEVE includes.
 *
 * Licensed under the GNU GPL. For full terms see the file COPYING that came
 * with the Squirrelmail distribution.
 *
 * @version $Id: sieve.inc.php 1020 2009-05-13 14:10:13Z avel $
 * @author Alexandros Vellis <avel@users.sourceforge.net>
 * @copyright 2004-2007 The SquirrelMail Project Team, Alexandros Vellis
 * @package plugins
 * @subpackage avelsieve
 */

/** Includes */
include_once(SM_PATH . 'plugins/avelsieve/include/sieve_getrule.inc.php');
include_once(SM_PATH . 'plugins/avelsieve/include/sieve_buildrule.inc.php');
include_once(SM_PATH . 'plugins/avelsieve/include/DO_Sieve.class.php');

