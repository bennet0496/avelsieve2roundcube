<?php
/**
 * Licensed under the GNU GPL. For full terms see the file COPYING that came
 * with the Squirrelmail distribution.
 *
 *
 * @version $Id: avelsieve_action_notify.class.php 1021 2009-05-15 09:59:50Z avel $
 * @author Alexandros Vellis <avel@users.sourceforge.net>
 * @copyright 2002-2009 Alexandros Vellis
 * @package plugins
 * @subpackage avelsieve
 */

/**
 * Notify Action
 */
class avelsieve_action_notify extends avelsieve_action {
    var $num = 0;
    var $name = 'notify';
    var $options = array(
        'notify' => array(
            'on' => '',
            'method' => '',
            'id' => '',
            'options' => ''
        )
    );
    var $capability = 'notify';
    var $image_src = 'images/icons/email.png';
    var $two_dimensional_options = true;

    /**
     * The notification action is a bit more complex than the others. The
     * oldcyrus variable is for supporting the partially implemented notify
     * extension implementation of Cyrus < 2.3.
     *
     * @see https://bugzilla.andrew.cmu.edu/show_bug.cgi?id=2135
     */
    function avelsieve_action_notify(&$s, $rule = array()) {
        $this->init();
        global $notifymethods, $avelsieve_oldcyrus;
        if(isset($notifymethods)) {
            $this->notifymethods = $notifymethods;
        } else {
            $this->notifymethods = false;
        }
        
        $this->text = _("Notify");
        $this->helptxt = _("Send a notification ");
        $this->notifystrings = array(
            'sms' => _("Mobile Phone Message (SMS)") ,
            'mailto' => _("Email notification") ,
            'zephyr' => _("Notification via Zephyr") ,
            'icq' => _("Notification via ICQ")
        );
        
        $this->oldcyrus = $avelsieve_oldcyrus;
        $this->avelsieve_action($s, $rule);
    }

    /**
     * Notify Options
     * @param array $val
     * @return string
     */
    function options_html($val) {
        global $prioritystrings;
        $out = '<blockquote>
            <table border="0" width="70%">';

        $out .= '<tr><td align="right" valign="top">'.
            _("Method") . ': </td><td align="left">';

        if(is_array($this->notifymethods) && sizeof($this->notifymethods) == 1) {
                /* No need to provide listbox, there's only one choice */
                $out .= '<input type="hidden" name="notify[method]" value="'.htmlspecialchars($this->notifymethods[0]).'" />';
                if(array_key_exists($this->notifymethods[0], $this->notifystrings)) {
                    $out .= $this->notifystrings[$this->notifymethods[0]];
                } else {
                    $out .= $this->notifymethods[0];
                }
    
        } elseif(is_array($this->notifymethods)) {
                /* Listbox */
                $out .= '<select name="notify[method]">';
                foreach($this->notifymethods as $no=>$met) {
                    $out .= '<option value="'.htmlspecialchars($met).'"';
                    if(isset($val['notify']['method']) &&
                      $val['notify']['method'] == $met) {
                        $out .= ' selected=""';
                    }
                    $out .= '>';
        
                    if(array_key_exists($met, $this->notifystrings)) {
                        $out .= $this->notifystrings[$met];
                    } else {
                        $out .= $met;
                    }
                    $out .= '</option>';
                }
                $out .= '</select>';
                
        } elseif($this->notifymethods == false) {
                $out .= '<input name="notify[method]" value="'.htmlspecialchars($val['notify']['method']). '" size="20" />';
        }
    
        $out .= '</td></tr>';
        
            /* TODO Not really used, reconsider / remove it. */
            $dummy =  _("Notification ID"); // for gettext
            /*
            $out .= _("Notification ID") . ": ";
            $out .= '<input name="notify[id]" value="';
            if(isset($edit)) {
                if(isset($_SESSION['rules'][$edit]['notify']['id'])) {
                    $out .= htmlspecialchars($_SESSION['rules'][$edit]['notify']['id']);
                }
            }
            $out .= '" /><br />';
            */
        
        $out .= '<tr><td align="right">'.
            _("Destination") . ": ".
            '</td><td align="left" valign="top">'.
            '<input name="notify[options]" size="30" value="' . 
            ( isset($val['notify']['options']) ? htmlspecialchars($val['notify']['options']) : '') .
            '" /></td></tr>';
        
        $out .= '<tr><td align="right">'.
            _("Priority") . ':'.
            '</td><td align="left" valign="top">'.
            '<select name="notify[priority]">';
            foreach($prioritystrings as $pr=>$te) {
                $out .= '<option value="'.htmlspecialchars($pr).'"';
                if(isset($val['notify']['priority']) && $val['notify']['priority'] == $pr) {
                    $out .= ' checked="CHECKED"';
                }
                $out .= '>';
                $out .= $prioritystrings[$pr];
                $out .= '</option>';
            }
        $out .= '</select></td></tr>';
        
        $out .= '<tr><td align="right">'.
            _("Message") . ": ".
            '</td><td align="left" valign="top">'.
            '<textarea name="notify[message]" rows="4" cols="50">'.
            (isset($val['notify']['message']) ? htmlspecialchars($val['notify']['message']) : '') .
            '</textarea><br />';
            
        $out .= '<small>'. _("Help: Valid variables are:");
        if($this->oldcyrus) {
            /* $text$ is not supported by Cyrus IMAP < 2.3 . */
            $out .= ' $from$, $env-from$, $subject$</small>';
        } else {
            $out .= ' $from$, $env-from$, $subject$, $text$, $text[n]$</small>';
        }
        $out .= '</td></tr></table></blockquote>';
        return $out;
    }
}

