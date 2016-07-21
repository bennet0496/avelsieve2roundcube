<?php
/**
 * User-friendly interface to SIEVE server-side mail filtering.
 * Plugin for Squirrelmail 1.4+
 *
 * Licensed under the GNU GPL. For full terms see the file COPYING that came
 * with the Squirrelmail distribution.
 *
 * @version $Id: configtest.inc.php 1020 2009-05-13 14:10:13Z avel $
 * @author Alexandros Vellis <avel@users.sourceforge.net>
 * @copyright 2004-2007 Alexandros Vellis
 * @package plugins
 * @subpackage avelsieve
 */

/** Includes necessary for configtest */
include_once(SM_PATH . 'functions/imap.php');
include_once(SM_PATH . 'plugins/avelsieve/config/config.php');
include_once(SM_PATH . 'plugins/avelsieve/include/support.inc.php');
include_once(SM_PATH . 'plugins/avelsieve/include/html_rulestable.inc.php');
include_once(SM_PATH . 'plugins/avelsieve/include/sieve.inc.php');
include_once(SM_PATH . 'plugins/avelsieve/include/spamrule.inc.php');

/**
 * Perform configuration test. This is a simple one at the moment and no fatal 
 * errors are ever reported. In the future it can be accomodated for the 
 * various backends.
 *
 * @return boolean
 */
function avelsieve_configtest_do() {
    global $avelsieve_backend;

    $backend_class_name = 'DO_Sieve_'.$avelsieve_backend;
    $s = new $backend_class_name;
    $s->init(true);
    print '<strong>Avelsieve</strong> plugin details: backend = '.$avelsieve_backend.'<br/>';
    if(empty($s->capabilities)) {
            do_err('I could not determine the capabilities for Sieve Mail Filtering. Perhaps connectivity
                    with ManageSieve server (if backend=Managesieve) is bad?', false);
    } else {
            print 'Sieve Server capabilities = '. print_r($s->capabilities, true) . '<br/>';
    }
    return false;
}

