<?php
/**
 * Licensed under the GNU GPL. For full terms see the file COPYING that came
 * with the Squirrelmail distribution.
 *
 *
 * @version $Id: avelsieve_action_redirect.class.php 1021 2009-05-15 09:59:50Z avel $
 * @author Alexandros Vellis <avel@users.sourceforge.net>
 * @copyright 2002-2009 Alexandros Vellis
 * @package plugins
 * @subpackage avelsieve
 */

/**
 * Redirect Action
 */
class avelsieve_action_redirect extends avelsieve_action {
    var $num = 4;
    var $image_src = 'images/icons/arrow_divide.png';

    function avelsieve_action_redirect(&$s, $rule = array()) {
        $this->init();
        $this->text = _("Redirect");
        $this->helptxt = _("Automatically redirect the message to a different email address");
        $this->options = array(
            'redirectemail' => _("someone@example.org"),
            'keep' => ''
        );
        $this->avelsieve_action($s, $rule);
    }

    function options_html($val) {
        $out = '<input type="text" name="redirectemail" size="26" maxlength="100" value="'.htmlspecialchars($val['redirectemail']).'"/>'.
                '<br />'.
                '<input type="checkbox" name="keep" id="keep" ';
        if(!empty($val['keep'])) {
                $out .= ' checked="CHECKED"';
        }
        $out .= '/>'.
                '<label for="keep">'. _("Keep a local copy as well.") . '</label>';
        return $out;
    }

    function validate($val, &$errormsg) {
        $onemailregex = "[A-Z0-9]+[A-Z0-9\._\+-]*@[A-Z0-9_-]+[A-Z0-9\._-]+";
        
        if(!preg_match("/^$onemailregex(,$onemailregex)*$/i" ,    $val['redirectemail'])){
                $errormsg[] = _("Incorrect email address(es). You must enter one or more valid email addresses, separated by comma.");
        }
    }
}

