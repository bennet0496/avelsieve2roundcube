<?php
/**
 * User-friendly interface to SIEVE server-side mail filtering.
 * Plugin for Squirrelmail 1.4+
 *
 * Licensed under the GNU GPL. For full terms see the file COPYING that came
 * with the Squirrelmail distribution.
 *
 * HTML Functions
 *
 * @version $Id: html_main.inc.php 1020 2009-05-13 14:10:13Z avel $
 * @author Alexandros Vellis <avel@users.sourceforge.net>
 * @copyright 2004-2007 The SquirrelMail Project Team, Alexandros Vellis
 * @package plugins
 * @subpackage avelsieve
 */

/**
 * Global HTML Output functions. These contain functions for starting/ending
 * sections, helper functions for determining and printing HTML snippets, 
 * as well as header/footer thingies.
 */
class avelsieve_html {
    /**
     * @var int Level of Javascript support
     */
    var $js = 0;
    
    /**
     * @param boolean Flag for image usage
     */
    var $useimages = true;

    /**
     * Constructor function will initialize some variables, depending on the
     * environment.
     */
    function avelsieve_html() {
        global $plugins, $javascript_on, $useimages;

        if($javascript_on) {
            $this->js++;
            if(in_array('javascript_libs', $plugins)) {
                $this->js++;
            }
        }
        $this->useimages = $useimages;
        $this->baseuri = sqm_baseuri();
        $this->imageuri = $this->baseuri . 'plugins/avelsieve/images/';
        $this->iconuri = $this->baseuri . 'plugins/avelsieve/images/icons/';
    }

    /**
     * Page Header
     */
    function header($customtitle = '') {
        $out = '<h1>'._("Server-Side Mail Filtering");
        if($customtitle) {
            $out .= ' - '.$customtitle;
        }
        $out .= '</h1>';
        return $out;
    }
    
    function my_header() {
        global $color;
        return '<br><table width="100%"><tr><td bgcolor="'.$color[0].'">'.
            '<center><b>' . _("Server-Side Mail Filtering"). '</b></center>'.
            '</td></tr></table>';
    }
    
    /**
     * Squirrelmail-style table header
     * @return string
     */
    function table_header($customtitle) {
        global $color;
        $out = "\n<!-- Table header --><br/>".
        '<table bgcolor="'.$color[0].'" width="95%" align="center" cellpadding="2" cellspacing="0" border="0">
        <tr><td align="center">
            <strong>'.  _("Server-Side Mail Filtering") .
            ( !empty($customtitle) ? ' - '.$customtitle : '' ) . '</strong>
            <table width="100%" border="0" cellpadding="5" cellspacing="0">
            <tr><td bgcolor="'.$color[4].'" align="center">
        ';
        return $out;
    }
    
    /** 
     * Squirrelmail-style table footer
     * @return string
     */
    function table_footer() {
        return '</td></tr></table>'.
            '</td></tr></table>';
    }
    
    /**
     * All sections table start
     * @return string
     */
    function all_sections_start() {
        return '<!-- All sect. start --><table width="95%" align="center" cellpadding="4" cellspacing="0" border="0">';
    }
    
    /**
     * All sections table end
     * @return string
     */
    function all_sections_end() {
        return '</table><!-- All sect. end -->';
    }
    
    /**
     * Table 'section' start
     * @return string
     */
    function section_start($title = '') {
        global $color;
        if(empty($title)) {
            return "\n<!-- Section start -->".
                '<tr><td bgcolor="'.$color[0].'" align="left">';
        } else {
            return "\n<!-- Section start -->".
                '<tr><td bgcolor="'.$color[9].'" align="center">'.
                '<strong>'.$title.'</strong></td></tr>'.
                '<tr><td bgcolor="'.$color[0].'" align="left">';
        }
    }
    
    /**
     * Table 'section' end
     * @return string
     */
    function section_end() {
        global $color;
        $out = "</td></tr>\n".
            "<tr><td bgcolor=\"$color[4]\">&nbsp;</td></tr>\n";
        return $out;
    }

    /**
      * Generic Listbox widget
      *
      * @param $selected_header Selected header
      * @param $n option number
      */
    function generic_listbox($name, $options, $selected_option = '') {
        $out = '<select name="'.$name.'">';
        foreach($options as $o => $desc) {
            if ($selected_option==$o) {
                $out .= '<option value="'.htmlspecialchars($o).'" selected="SELECTED">'.htmlspecialchars($desc).'</option>';
            } else {
                $out .= '<option value="'.htmlspecialchars($o).'">'.htmlspecialchars($desc).'</option>';
            }
        }
        $out .= '</select>';
        return $out;
    }
    
    /**
     * Explicitly set Error Messages that might have occured from an external 
     * source.
     *
     * Inside the classes themselves, I use
     * $this->errmsg[] = 'Message' ...
     *
     * @param array $array
     * @return void
     */
    function set_errmsg($array) {
        $this->errmsg = array_merge($this->errmsg, $array);
    }
    
    /**
     * Access the messages queue from session variables, and return what should 
     * be displayed in the end to the user.
     *
     * @todo Implement a proper message queue.
     * @return array
     */
    function retrieve_avelsieve_messages() {
        global $color;
        $out = '';
        $msgs = array();

        if(isset($_SESSION['comm'])) {
            $comm = $_SESSION['comm'];

            $out .= '<p style="background-color: '.$color[16].'; color:'.$color[8].'; text-align: center;">'.
                ($this->useimages == true? '<img src="'.$this->iconuri.'tick.png" alt="(OK)" /> ' : '');        
            
            if (isset($comm['raw'])) {
                $out .= $comm['raw'];

            } elseif(isset($comm['new'])) {
                $out .= _("Successfully added new rule.");
        
            } elseif (isset($comm['edited'])) {
                $out .= sprintf( _("Successfully updated rule #%s"), $comm['edited']+1);
        
            } elseif (isset($comm['deleted'])) {
                if(is_array($comm['deleted'])) {
                    $deletedRulesNumbers = '';
                    for ($i=0; $i<sizeof($comm['deleted']); $i++ ) {
                        $deletedRulesNumbers .= $comm['deleted'][$i] +1;
                        if($i != (sizeof($comm['deleted']) -1) ) {
                            $deletedRulesNumbers .= ", ";
                        }
                    }
                    $out .= sprintf( _("Successfully deleted rules #%s"), $deletedRulesNumbers);
                } else {
                    $out .= sprintf( _("Successfully deleted rule #%s"), $comm['deleted']+1);
                }
            }

            $out .= '</p>';
        }
        return $out;
    }
    
    /**
     * Remove messages from session. 
     * @return void
     */
    function clear_avelsieve_messages() {
        if(isset($_SESSION['comm'])) {
            unset($_SESSION['comm']);
        }
    }
    
    /**
     * Small helper function, that returns the appropriate javascript snippet 
     * for "toggle" links.
     *
     * @param string $divname ID of the DIV/SPAN
     * @param int $js Force Javascript level. for instance, sometimes we don't 
     *   want scriptaculous-style effects, so we set js=1.
     * @param boolean $imageChange Change the image "$divname_img" to 
     *   closed/open triangle
     * @return string
     */
    function js_toggle_display($divname, $js = '', $imageChange = '') {
        if($this->js == 0) {
            $js = 0;
        } elseif(empty($js)) {
            $js = $this->js;
        }
        if(empty($imageChange)) $imageChange = $this->useimages;

        if($js == 2) {
            /* Scriptaculous */
            if($imageChange) {
                return 'AVELSIEVE.edit.toggleShowDivWithImg(\''.$divname.'\', 1);';
            } else {
                return 'Effect.toggle(\''.$divname.'\', \'slide\');';
            }

        } elseif($js == 1) {
            /* Simple javascript */
            if($imageChange) {
                return 'AVELSIEVE.edit.toggleShowDivWithImg(\''.$divname.'\');';
            } else {
                return 'AVELSIEVE.edit.toggleShowDiv(\''.$divname.'\');';
            }
        }
    }

    /**
     * Print formatted error message(s), if they exist.
     * 
     * @return string
     */
    function print_errmsg() {
        $out = '';
        if(!empty($this->errmsg)) {
            global $color;
            $out .= $this->section_start( _("Error Encountered:") ).
                '<div style="text-align:center; color:'.$color[2].';">';

            if(is_array($this->errmsg)) {
                $out .= '<ul>';
                foreach($this->errmsg as $msg) {
                    $out .= '<li>'.$msg.'</li>';
                }
                $out .= '</ul>';
            } else {
                $out .= '<p>'.$this->errmsg .'</p>';
            }
            $out .= '<p>'. _("You must correct the above errors before continuing."). '</p>';
            $out .= '</div>' .     $this->section_end();
        }
        return $out;
    }
    
    /**
     * Helper function: Find if the option $optname is on, in the current rule.
     * (Checks if $this->rule[$optname] evaluates to true).
     *
     * @param string $optname
     * @return boolean
     */
    function determineState($optname) {
        if(!isset($this->rule[$optname])) {
            return false;
        }
        if($this->rule[$optname] == 0) {
            return false;
        }
        if($this->rule[$optname] || $this->rule[$optname] == 'on' || $this->rule[$optname] == true) {
            return true;
        }
        return false;
    }
    
    /**
     * Returns the checked state for a checkbox that corresponds to option 
     * $optname. (Helper function).
     *
     * @param string $optname
     * @return string
     */
    function stateCheckbox($optname) {
        if($this->determineState($optname) === true) {
            return 'checked="" ';
        } else {
            return '';
        }
    }

    /**
     * Returns the visibility state for a div that corresponds to more options 
     * under the "top" option $optname. (Helper function).
     *
     * @param string $optname
     * @return string
     */
    function stateVisibility($optname) {
        if($this->determineState($optname) === true) {
            return '';
        } else {
            return 'style="display:none;" ';
        }
    }
}

