<?php
/**
 * Licensed under the GNU GPL. For full terms see the file COPYING that came
 * with the Squirrelmail distribution.
 *
 *
 * @version $Id: avelsieve_action_keepdeleted.class.php 1021 2009-05-15 09:59:50Z avel $
 * @author Alexandros Vellis <avel@users.sourceforge.net>
 * @copyright 2002-2009 Alexandros Vellis
 * @package plugins
 * @subpackage avelsieve
 */

/**
 * Keep a copy in INBOX marked as Deleted
 *
 * @deprecated To be replaced by imap4flags implementation.
 */
class avelsieve_action_keepdeleted extends avelsieve_action {
    var $num = 0;
    var $name = 'keepdeleted';
    var $capability = 'imapflags';
    var $image_src = 'images/icons/email_delete.png';

    function avelsieve_action_keepdeleted(&$s, $rule = array()) {
        $this->init();
        $this->text = _("Also keep copy in INBOX, marked as deleted.");
        $this->avelsieve_action($s, $rule);
    }
}

