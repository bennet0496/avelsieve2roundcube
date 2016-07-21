<?php
/**
 * User-friendly interface to SIEVE server-side mail filtering.
 * Plugin for Squirrelmail 1.4+
 *
 * Licensed under the GNU GPL. For full terms see the file COPYING that came
 * with the Squirrelmail distribution.
 *
 * @version $Id: sieve_buildrule.13.inc.php 1020 2009-05-13 14:10:13Z avel $
 * @author Kostantinos Koukopoulos <kouk@noc.uoa.gr>
 * @copyright 2007 Alexandros Vellis
 * @package plugins
 * @subpackage avelsieve
 */

/**
 * Rule #13: Custom Sieve Code
 *
 * @param array $rule
 * @return array array($out,$text,$terse, array('skip_further_execution'=>true, 'replace_output'=>true))
 * @todo Make hyperlink go to the specific rule
 */
function avelsieve_buildrule_13($rule) {
    global $displaymodes; 
    $sourcelnk = '<a href="table.php?mode=source" title="'.$displaymodes['source'][1].'">%s</a>';
    $out = $rule['code']; 
    $text = _("Custom Sieve Code") . ' - '. sprintf($sourcelnk, _("View Source")); 
    $terse = $text; 
    
    return(array($out,$text,$terse, array('skip_further_execution'=>true, 'replace_output'=>true)));
}

