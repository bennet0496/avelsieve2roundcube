<?php
/**
 * Licensed under the GNU GPL. For full terms see the file COPYING that came
 * with the Squirrelmail distribution.
 *
 *
 * @version $Id: avelsieve_action_fileinto.class.php 1021 2009-05-15 09:59:50Z avel $
 * @author Alexandros Vellis <avel@users.sourceforge.net>
 * @copyright 2002-2009 Alexandros Vellis
 * @package plugins
 * @subpackage avelsieve
 */

/**
 * Fileinto Action
 */
class avelsieve_action_fileinto extends avelsieve_action {
    var $num = 5;
    var $capability = 'fileinto';
    var $options = array(
        'folder' => '',
    );
    var $image_src = 'images/icons/folder_go.png';

    /**
     * The fileinto constructor, unlike other actions, uses the
     * property "helptxt" to put the actual option box.
     *
     * @param object $s
     * @param array $rule
     * @return void
     */
    function avelsieve_action_fileinto(&$s, $rule = array()) {
        $this->init();
        $this->text = _("Move to Folder");
        $this->avelsieve_action($s, $rule);
        if(isset($rule['folder'])) {
            $this->helptxt = mailboxlist('folder', $rule['folder']);
        } else {
            $this->helptxt = mailboxlist('folder', false);
        }
    }
    
    /**
     * Options for fileinto
     *
     * @param array $val
     * @todo Use "official" function sqimap_mailbox_option_list()
     */
    function options_html ($val) {
        /*
        if(isset($val['folder'])) {
            $this->helptxt = mailboxlist('folder', $val['folder']);
        } else {
            $this->helptxt = mailboxlist('folder', false);
        }
        */
            
        return sprintf( _("Or specify a new folder: %s to be created under %s"), 
                ' <input type="text" size="15" name="newfoldername" onclick="checkOther(\'5\');" /> ',
                mailboxlist('newfolderparent', false, true));
    }
}

