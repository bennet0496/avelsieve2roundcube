<?php
/**
 * Licensed under the GNU GPL. For full terms see the file COPYING that came
 * with the Squirrelmail distribution.
 *
 *
 * @version $Id: avelsieve_action_trash.class.php 1021 2009-05-15 09:59:50Z avel $
 * @author Alexandros Vellis <avel@users.sourceforge.net>
 * @copyright 2002-2009 Alexandros Vellis
 * @package plugins
 * @subpackage avelsieve
 */

/**
 * SPAM-rule-specific action: Store into trash folder.
 */
class avelsieve_action_trash extends avelsieve_action {
    var $num = 8;
    var $name = 'junk';
    var $image_src = 'images/icons/bin.png';
    
    function avelsieve_action_trash(&$s, $rule = array()) {
        $this->init();
        $this->text = _("Move to Trash");
        $this->helptxt = _("Store message in your Trash Folder. You will have to purge the folder yourself.");
        $this->avelsieve_action($s, $rule);
    }
}

