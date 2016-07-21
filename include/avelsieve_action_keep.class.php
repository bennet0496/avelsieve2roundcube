<?php
/**
 * Licensed under the GNU GPL. For full terms see the file COPYING that came
 * with the Squirrelmail distribution.
 *
 *
 * @version $Id: avelsieve_action_keep.class.php 1021 2009-05-15 09:59:50Z avel $
 * @author Alexandros Vellis <avel@users.sourceforge.net>
 * @copyright 2002-2009 Alexandros Vellis
 * @package plugins
 * @subpackage avelsieve
 */



/**
 * Keep Action
 */
class avelsieve_action_keep extends avelsieve_action {
    var $num = 1;
    var $capability = '';
    var $options = array(); 
    var $image_src = 'images/icons/accept.png';

    function avelsieve_action_keep(&$s, $rule = array()) {
        $this->init();
        $this->text = _("Keep Message");
        $this->helptxt = _("Save the message in your INBOX.");
        if(!isset($rule['action'])) {
            /* Hack to make the radio button selected for a new rule, for GUI
             * niceness */
            $this->rule['action'] = 1;
        }
        $this->avelsieve_action($s, $rule);
    }
}

