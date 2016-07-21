<?php
/**
 * Licensed under the GNU GPL. For full terms see the file COPYING that came
 * with the Squirrelmail distribution.
 *
 *
 * @version $Id: avelsieve_action.class.php 1049 2009-05-28 10:17:26Z avel $
 * @author Alexandros Vellis <avel@users.sourceforge.net>
 * @copyright 2002-2009 Alexandros Vellis
 * @package plugins
 * @subpackage avelsieve
 */

/**
 * Root class for SIEVE actions.
 *
 * Each class that extends this class describes a SIEVE action and can contain
 * the following variables:
 *
 * num            Number of action
 * capability    Required capability(ies), if any
 * text            Textual description
 * helptxt        Explanation text
 * options        Array of Options and their default values
 *
 * It can also contain these functions:
 *
 * options_html()    Returns the HTML printout of the action's options 
 */
class avelsieve_action {
    /*
     * @var boolean Flag to enable use of images and visual enhancements.
     */
    var $useimages = true;

    /**
     * @var boolean Translate generated email messages?
     */
    var $translate_return_msgs = false;

    /**
     * @var int Level of Javascript support
     */
    var $js = 0;

    /**
     * Initialize variables that we get from the configuration of avelsieve and 
     * the environment of Squirrelmail.
     *
     * @return void
     */
    function init() {
        global $translate_return_msgs, $useimages, $javascript_on, $plugins;

        if(isset($translate_return_msgs)) {
            $this->translate_return_msgs = $translate_return_msgs;
        }
        if(isset($useimages)) {
            $this->useimages = $useimages;
        }
        if($javascript_on) {
            $this->js++;
            if(in_array('javascript_libs', $plugins)) {
                $this->js++;
            }
        }
    }

    /**
     * Initialize other properties based on the ones defined from child classes.
     * @return void
     */
    function avelsieve_action(&$s, $rule) {
        $this->rule = $rule;
        $this->s = $s;
        
        if ($this->useimages && isset($this->image_src)) {
            $this->text = ' <img src="'.$this->image_src.'" border="0" alt="'. $this->text.'" valign="middle" style="margin-left: 2px; margin-right: 4px;"/> '.
                '<strong>' . $this->text . '</strong>';
        }
    }

    /**
     * Check if this action is valid in the current server capabilities
     * ($this->capabilities array).
     * @return boolean
     */
    function is_action_valid() {
        if(isset($this->capability) && !empty($this->capability)) {
            if(!$this->s->capability_exists($this->capability)) {
                return false;
            }
        }
        return true;
    }

    /**
     * Return All HTML Code that describes this action.
     *
     * @return string
     */
    function action_html() {
        /* Radio button */
        $out = $this->action_radio();
        $identifier = ($this->num ? 'action_'.$this->num : $this->name);

        /* Main text */
        $out .= '<label for="'.$identifier.'">' . $this->text .'</label>';

        if(isset($this->helptxt)) {
                $out .= ' <span id="helptxt_'.$identifier.'"'.
                        ($this->is_selected() ? ' style="display:inline"' :
                            ($this->js ? 'style="display:none"': '') ) .
                        '> &#8211; '.$this->helptxt.'</span>';
        }

        /* Options */
        if(isset($this->options) and sizeof($this->options) > 0) {
            $optval = array();
            foreach($this->options as $opt=>$defaultval) {
                if(is_array($opt)) {
                    /* Two - level options, e.g. notify */
                    foreach($opt as $opt2=>$defaultval2) {
                        if(isset($this->rule[$opt][$opt2])) {
                            $optval[$opt][$opt2] = $this->rule[$opt][$opt2];
                        } else {
                            $optval[$opt][$opt2] = $defaultval2;
                        }
                    }
                } else {
                    /* Flat-level options schema */
                    if(isset($this->rule[$opt])) {
                        $optval[$opt] = $this->rule[$opt];
                    } else {
                        $optval[$opt] = $defaultval;
                    }
                }
            }
            if($this->num) {
                /* Radio Button */
                $out .= '<div id="options_'.$this->num.'"';
                if(isset($this->rule['action']) && $this->rule['action'] == $this->num) {
                    $out .= '';
                } elseif($this->js) {
                    $out .= ' style="display:none"';
                }
            } else {
                /* Checkbox */
                $out .= '<div id="options_'.$this->name.'"';
                if(isset($this->rule[$this->name]) && $this->rule[$this->name]) {
                    $out .= '';
                } elseif($this->js) {
                    $out .= ' style="display:none"';
                }
            }
            $out .= '>';

            $out .= '<blockquote>';
            if(method_exists($this, 'options_html')) {
                $out .= $this->options_html($optval);
            } else {
                $out .= $this->options_html_generic($optval);
            }
            $out .= '</blockquote>';
            $out .= '</div>';
            unset($val);
        }
        $out .= '<br />';
        return $out;
    }

    /**
     * Shows whether an action is selected for the current rule.
     *
     * @return boolean
     * @since 1.9.8
     */
    function is_selected() {
        if(is_numeric($this->num) && $this->num > 0) {
            // For Radio-style numeric id actions.
            if(isset($this->rule['action']) && $this->rule['action'] == $this->num) {
                return true;
            }

        } else {
            // For Checkbox-style actions.
            if(isset($this->two_dimensional_options) && $this->options[$this->name]['on']) {
                return true;
            } else {
                if(isset($this->rule[$this->name])) {
                    return true;
                }
            }
        }
        return false;
    }

    /**
     * Generic Options for an action.
     *
     * @todo Not implemented yet.
     */
    function options_html_generic($val) {
        return "Not implemented yet.";
    }

    /**
     * Output radio or checkbox button for this action.
     * @return string
     */
    function action_radio() {
        if($this->num) {
            /* Radio */
            $out = '<input type="radio" name="action" ';
            if($this->js) {
                $out .= 'onClick="';
                for($i=0;$i<9;$i++) {
                    if($i!=$this->num) {
                        if($this->js == 2) {
                            $out .= 'if(el(\'options_'.$i.'\')) { new Effect.BlindUp(\'options_'.$i.'\'); }
                                     if(el(\'helptxt_action_'.$i.'\')) { new Effect.Fade(\'helptxt_action_'.$i.'\'); } ';
                        } else {
                            $out .= 'AVELSIEVE.util.hideDiv(\'options_'.$i.'\'); AVELSIEVE.util.hideDiv(\'helptxt_action_'.$i.'\');';
                        }
                    }
                }
                if($this->js == 2) {
                    $out .= 'if(el(\'options_'.$this->num.'\')) { new Effect.BlindDown(\'options_'.$this->num.'\'); }
                             if(el(\'helptxt_action_'.$this->num.'\')) { new Effect.Appear(\'helptxt_action_'.$this->num.'\'); }';
                } else {
                    $out .= 'AVELSIEVE.util.showDiv(\'options_'.$this->num.'\'); AVELSIEVE.util.showDiv(\'helptxt_action_'.$this->num.'\');';
                }
                $out .= ' return true;"';
            }
            $out .= ' id="action_'.$this->num.'" value="'.$this->num.'" '.
                    ($this->is_selected() ? ' checked="CHECKED"' : '') . '/> ';

        } else {
            /* Checkbox */
            $out = '<input type="checkbox" name="'.$this->name;
            if(isset($this->two_dimensional_options)) {
                $out .= '[on]';
            }
            $out .= '" onClick="AVELSIEVE.edit.toggleShowDiv(\'helptxt_'.$this->name.'\');AVELSIEVE.edit.toggleShowDiv(\'options_'.$this->name.'\');return true;"'.
                    ' id="'.$this->name.'" ' . ( $this->is_selected() ? ' checked="CHECKED"' : '' ) .
                    '/> ';
        }
        return $out;
    }
}

