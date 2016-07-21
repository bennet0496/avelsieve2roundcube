<?php
/**
 * Licensed under the GNU GPL. For full terms see the file COPYING that came
 * with the Squirrelmail distribution.
 *
 *
 * @version $Id: avelsieve_action_vacation.class.php 1053 2009-05-28 13:52:29Z avel $
 * @author Alexandros Vellis <avel@users.sourceforge.net>
 * @copyright 2002-2009 Alexandros Vellis
 * @package plugins
 * @subpackage avelsieve
 */

/**
 * Vacation / Autoresponder Action
 */
class avelsieve_action_vacation extends avelsieve_action {
    var $num = 6;
    var $capability = 'vacation';
    
    var $options = array(
        'vac_addresses' => '',
        'vac_days' => '7',
        'vac_subject' => '',
        'vac_message' => ''
    );
    var $image_src = 'images/icons/status_away.png';

    function avelsieve_action_vacation(&$s, $rule = array()) {
        $this->init();
        $this->text = _("Vacation / Autoresponder");
        $this->options['vac_addresses'] = get_user_addresses();

        if($this->translate_return_msgs==true) {
            $this->options['vac_message'] = _("This is an automated reply; I am away and will not be able to reply to you immediately."). ' '.
            _("I will get back to you as soon as I return.");
        } else {
            $this->options['vac_message'] = "This is an automated reply; I am away and will not be able to reply to you immediately.".
            "I will get back to you as soon as I return.";
        }
        
        $this->helptxt = _("The notice will be sent only once to each person that sends you mail, and will not be sent to a mailing list address.");

        $this->avelsieve_action($s, $rule);
    }


    function options_html($val) {
        /* Provide sane default for maxlength */
        $maxlength = 200;
        if(isset($val['vac_addresses']) && strlen($val['vac_addresses']) > 200) {
            $maxlength = (string) (strlen($val['vac_addresses']) + 50);
        }
        
        $out = '<table border="0" width="70%" cellpadding="3">'.
            '<tr><td align="right" valign="top">'.
            _("Subject:") .
            '</td><td align="left">'.
            '<input type="text" name="vac_subject" value="'.htmlspecialchars($val['vac_subject']).'" size="60" maxlength="300" />'.
            '<br/><small>'._("Optional subject of the vacation message.") .'</small>'.
            '</td></tr>'.

            '<tr><td align="right" valign="top">'.
            _("Your Addresses:").
            '</td><td align="left">'.
            ' <input type="text" name="vac_addresses" value="'.htmlspecialchars($val['vac_addresses']).'" size="60" maxlength="'.$maxlength.'" />'.
            '<br/><small>'._("A vacation / autorespond message will be sent only if an email is sent explicitly to one of these addresses.") .'</small>'.
            '</td></tr>'.

            '<tr><td align="right" valign="top">'.
            _("Days:").
            '</td><td align="left">'.
            ' <input type="text" name="vac_days" value="'.htmlspecialchars($val['vac_days']).'" size="3" maxlength="4" /> ' . _("days").
            '<br/><small>'._("A vacation / autorespond message will not be resent to the same address, within this number of days.") .'</small>'.
            '</td></tr>'.
            
            '<tr><td align="right" valign="top">'.
            _("Message:") . 
            '</td><td align="left">'.
            '<textarea name="vac_message" rows="4" cols="60">'.$val['vac_message'].'</textarea>'.
            '</td></tr>'.
        
            '</table>';

        return $out;
    }

    function validate($val, &$errormsg) {
        if(!is_numeric($val['vac_days']) || !($val['vac_days'] > 0)) {
            $errormsg[] = _("The number of days between vacation messages must be a positive number.");
        }
        if(!empty($val['vac_addresses'])) {
            $onemailregex = "[a-zA-Z0-9]+[a-zA-Z0-9\._-]*@[a-zA-Z0-9_-]+[a-zA-Z0-9\._-]+";
            if(!preg_match("/^$onemailregex(,$onemailregex)*$/" ,    $val['vac_addresses'])){
                $errormsg[] = _("Incorrect email address(es). You must enter one or more valid email addresses, separated by comma.");
            }
        }
    }
}

