<?php
/**
 * Licensed under the GNU GPL. For full terms see the file COPYING that came
 * with the Squirrelmail distribution.
 *
 * This file contains functions that spit out HTML, mostly intended for use by
 * addrule.php and edit.php.
 *
 * @version $Id: html_ruleedit_wizard.inc.php 1020 2009-05-13 14:10:13Z avel $
 * @author Alexandros Vellis <avel@users.sourceforge.net>
 * @copyright 2004-2007 Alexandros Vellis
 * @package plugins
 * @subpackage avelsieve
 */

/** Includes */
include_once(SM_PATH . 'plugins/avelsieve/include/html_main.inc.php');

/**
 * HTML Output functions for rule editing / adding in a wizard form.
 *
 * These are some old functions that I decided to store in this class, and
 * probably re-use them later when I reenable the wizard.
 *
 * @obsolete
 */
class avelsieve_html_edit_wizard extends avelsieve_html_edit {
    /**
     * @var int Which part of add new rule wizard are we in. 0 means 'any'.
     */
    var $part = 0;


    /**
     * Start form.
     * @return string
     */
    function formheader() {
        global $PHP_SELF;
        return '<form name="addrule" action="'.$PHP_SELF.'" method="POST">';
    }

    /**
     * Bottom control and navigation buttons.
     * @return string
     */
    function addbuttons() {
        $out = '<input name="reset" value="' . _("Clear this Form") .'" type="reset" />';

        if (isset($part) && $part != 1) {
            $out .= '<input name="startover" value="'. _("Start Over") .'" type="submit" />';
        }
        $out .= '<input name="cancel" value="'. _("Cancel").'" type="submit" /><br />';
    
        if ($this->spamrule) {
            $out .= '<input style="font-weight:bold" name="finished" value="'.
                _("Add SPAM Rule") . '" type="submit" />';
        }
        return $out;
    
        /*
        if ($part!=1) {
            $out .= '<input name="prev" value="&lt;&lt; ';
            $out .= _("Move back to step");
            $out .= ' '.($part-1).'" type="submit" />';
        }
        */
        $dummy = _("Move back to step");
        
        if ($part=="4") {
            $out .= '<input style="font-weight:bold"  name="finished" value="'.
                _("Finished").'" type="submit" />';
        } else {
            $out .= '<input name="next" value="'._("Move on to step").' '.($part+1).' &gt;&gt;" type="submit" />';
        }
    }

    /**
     * Simple footer that closes tables, form and HTML.
     * @return string
     * @obsolete
     */
    function nakedfooter() {
        return '</td></tr></table> </form></body></html>';
    }
    
    /**
     * Output notification message for new rule wizard
     * @param string $text
     * @return string
     */
    function confirmation($text) {
        $out = '<p>'. _("Your new rule states:") .
            '</p><blockquote><p>'.$text.'</p></blockquote><p>'.
            _("If this is what you wanted, select Finished. You can also start over or cancel adding a rule altogether.").
            '</p>';
        return $out;
    }
}


