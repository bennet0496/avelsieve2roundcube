<?php
/**
 * Licensed under the GNU GPL. For full terms see the file COPYING that came
 * with the Squirrelmail distribution.
 *
 *
 * @version $Id: avelsieve_action_junk.class.php 1021 2009-05-15 09:59:50Z avel $
 * @author Alexandros Vellis <avel@users.sourceforge.net>
 * @copyright 2002-2009 Alexandros Vellis
 * @package plugins
 * @subpackage avelsieve
 */

/**
 * SPAM-rule-specific action: Store into junk folder.
 */
class avelsieve_action_junk extends avelsieve_action {
    var $num = 7;
    var $name = 'junk';
    var $image_src = 'images/icons/bin.png';
    
    function avelsieve_action_junk(&$s, $rule = array()) {
        global $junkfolder_days;
        $this->init();
        $this->text = _("Move to Junk");
        $this->helptxt = sprintf( _("Store message in your Junk Folder. Messages older than %s days will be deleted automatically."), $junkfolder_days).
               ' ' . _("Note that you can set the number of days in Folder Preferences.");
        $this->avelsieve_action($s, $rule);
    }
}

