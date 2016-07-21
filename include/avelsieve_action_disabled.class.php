<?php
/**
 * Licensed under the GNU GPL. For full terms see the file COPYING that came
 * with the Squirrelmail distribution.
 *
 *
 * @version $Id: avelsieve_action_disabled.class.php 1021 2009-05-15 09:59:50Z avel $
 * @author Alexandros Vellis <avel@users.sourceforge.net>
 * @copyright 2002-2009 Alexandros Vellis
 * @package plugins
 * @subpackage avelsieve
 */

/**
 * Disabled rule.
 *
 * Perhaps this "action" should be refactored towards a flags / metadata scheme
 * in the future.
 */
class avelsieve_action_disabled extends avelsieve_action {
    var $num = 0;
    var $name = 'disabled';
    var $image_src = 'images/icons/disconnect.png';

    function avelsieve_action_disabled(&$s, $rule = array()) {
        $this->init();
        $this->text = _("Disable");
        $this->helptxt = _("The rule will have no effect for as long as it is disabled.");
        $this->avelsieve_action($s, $rule);
    }
}

