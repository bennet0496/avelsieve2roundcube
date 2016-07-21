<?php
/**
 * Licensed under the GNU GPL. For full terms see the file COPYING that came
 * with the Squirrelmail distribution.
 *
 *
 * @version $Id: avelsieve_action_discard.class.php 1021 2009-05-15 09:59:50Z avel $
 * @author Alexandros Vellis <avel@users.sourceforge.net>
 * @copyright 2002-2009 Alexandros Vellis
 * @package plugins
 * @subpackage avelsieve
 */

/**
 * Discard Action
 */
class avelsieve_action_discard extends avelsieve_action {
    var $num = 2;
    var $capability = '';
    var $options = array(); 
    var $image_src = 'images/icons/cross.png';

    function avelsieve_action_discard(&$s, $rule = array()) {
        $this->init();
        $this->text = _("Discard");
        $this->helptxt = _("Silently discards the message; use with caution.");
        $this->avelsieve_action($s, $rule);
    }
}

