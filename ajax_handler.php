<?php
/**
 * User-friendly interface to SIEVE server-side mail filtering.
 * Plugin for Squirrelmail 1.4+
 *
 * This page manages various XHR requests and sends the appropriate responses.
 *
 * Licensed under the GNU GPL. For full terms see the file COPYING that came
 * with the Squirrelmail distribution.
 *
 * @version $Id: ajax_handler.php 1039 2009-05-26 14:43:24Z avel $
 * @author Alexandros Vellis <avel@users.sourceforge.net>
 * @copyright 2009 Alexandros Vellis
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
}
    
require(SM_PATH . 'plugins/avelsieve/config/config.php');

$prev = bindtextdomain ('avelsieve', SM_PATH . 'plugins/avelsieve/locale');
textdomain ('avelsieve');

include_once(SM_PATH . 'functions/imap.php');
require_once(SM_PATH . 'plugins/avelsieve/include/constants.inc.php');
include_once(SM_PATH . 'plugins/avelsieve/include/html_rulestable.inc.php');
include_once(SM_PATH . 'plugins/avelsieve/include/html_ruleedit.inc.php');
include_once(SM_PATH . 'plugins/avelsieve/include/sieve_conditions.inc.php');
include_once(SM_PATH . 'plugins/avelsieve/include/sieve_actions.inc.php');
include_once(SM_PATH . 'plugins/avelsieve/include/sieve.inc.php');
include_once(SM_PATH . 'plugins/avelsieve/include/support.inc.php');

sqsession_is_active();

if(!isset($_REQUEST['avaction'])) exit;
$action = $_REQUEST['avaction'];

/* First off, common initialization code for many of the actions */
switch($action) {
case 'edit_condition':
case 'edit_condition_kind':
case 'datetime_get_snippet':
    // TODO - perhaps avoid connecting to ManageSieve and use cached capabilities
    $backend_class_name = 'DO_Sieve_'.$avelsieve_backend;
    $s = new $backend_class_name;
    $s->init();
    
    // $edit_class_name = 'avelsieve_html_edit_'. $type_get;
    $edit_class_name = 'avelsieve_html_edit';
    $ruleobj = new $edit_class_name($s, 'edit');

    break;

default:
    break;
}


switch($action) {
case 'edit_condition':
    $index = ( isset($_GET['index']) && is_numeric($_GET['index']) ) ? $_GET['index'] : 1;
    $type = isset($_GET['type']) ? $_GET['type'] : 1;

    $temprules = array( 'cond' => array( $index => array('type' => $type ) ) );
    $ruleobj->set_rule_data($temprules);

    echo '<span id="condition_line_'.$index.'">'. $ruleobj->condition($index) .'</span>';
    exit;

case 'edit_condition_kind':
    /* Return a new condition line - when changing condition_kind.
     * Arguments:
     * index: numeric index of line
     * value: condition_kind to use
     */

    $index = ( isset($_GET['index']) && is_numeric($_GET['index']) ) ? $_GET['index'] : 1;
    $value = isset($_GET['value']) ? $_GET['value'] : 'message';

    $temprules = array( 'cond' => array( $index => array('kind' => $value ) ) );
    $ruleobj->set_rule_data($temprules);

    echo '<span id="condition_line_'.$index.'">'. $ruleobj->condition($index) .'</span>';
    exit;

case 'datetime_get_snippet':
        
    $index = ( isset($_POST['index']) && is_numeric($_POST['index']) ) ? $_POST['index'] : 1;
    $name = isset($_POST['varname']) ? $_POST['varname'] : '';
    $value = isset($_POST['varvalue']) ? $_POST['varvalue'] : '';

    if(empty($name) || empty($value)) exit;

    $temprules = array( 'cond' => array( $index => array('type' => '1' ) ) );
	$ruleobj->process_input($_POST, true);
    $cond = array();
    if(isset($ruleobj->rule['cond']) && isset($ruleobj->rule['cond'][$index])) {
        $cond = $ruleobj->rule['cond'][$index];
    }
    
    $myCondition = new avelsieve_condition_datetime($s, $temprules, $index); // XXX
    $htmlOut = $myCondition->ui_tree_output($name, $value);

    echo json_encode( array('html' => $htmlOut) );

    exit;

default:
    break;
}

