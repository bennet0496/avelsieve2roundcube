<?php
/**
 * Licensed under the GNU GPL. For full terms see the file COPYING that came
 * with the Squirrelmail distribution.
 *
 *
 * @version $Id: avelsieve_action_reject.class.php 1021 2009-05-15 09:59:50Z avel $
 * @author Alexandros Vellis <avel@users.sourceforge.net>
 * @copyright 2002-2009 Alexandros Vellis
 * @package plugins
 * @subpackage avelsieve
 */

/**
 * Reject Action
 */
class avelsieve_action_reject extends avelsieve_action {
    var $num = 3;
    var $capability = 'reject';
    var $options = array(
        'excuse' => ''
    );
    var $image_src = 'images/icons/arrow_undo.png';
     
    function avelsieve_action_reject(&$s, $rule = array()) {
        $this->init();
        $this->text = _("Reject");
        $this->helptxt = _("Send the message back to the sender, along with an excuse");

        if($this->translate_return_msgs==true) {
            $this->options['excuse'] = _("Please do not send me large attachments.");
        } else {
            $this->options['excuse'] = "Please do not send me large attachments.";
        }
        $this->avelsieve_action($s, $rule);
    }

    function options_html($val) {
        return '<textarea name="excuse" rows="4" cols="50">'.$val['excuse'].'</textarea>';
    }
}

