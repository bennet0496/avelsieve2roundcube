<?php
/**
 * Licensed under the GNU GPL. For full terms see the file COPYING that came
 * with the Squirrelmail distribution.
 *
 *
 * @version $Id: avelsieve_action_stop.class.php 1021 2009-05-15 09:59:50Z avel $
 * @author Alexandros Vellis <avel@users.sourceforge.net>
 * @copyright 2002-2009 Alexandros Vellis
 * @package plugins
 * @subpackage avelsieve
 */

/**
 * STOP Action
 */
class avelsieve_action_stop extends avelsieve_action {
    var $num = 0;
    var $name = 'stop';
    var $text = '';
    var $image_src = 'images/icons/stop.png';

    function avelsieve_action_stop(&$s, $rule = array()) {
        $this->init();
        $this->helptxt = _("If this rule matches, do not check any rules after it.");
        $this->text = _("STOP");
        $this->avelsieve_action($s, $rule);
    }
}

