<?php
/**
 * Licensed under the GNU GPL. For full terms see the file COPYING that came
 * with the Squirrelmail distribution.
 *
 * @version $Id: sieve_rule_spam.inc.php 1020 2009-05-13 14:10:13Z avel $
 * @author Alexandros Vellis <avel@users.sourceforge.net>
 * @copyright 2007 Alexandros Vellis
 * @package plugins
 * @subpackage avelsieve
 */

/** Includes */
include_once(SM_PATH . 'plugins/avelsieve/include/html_main.inc.php');

/**
 * This class is to extend the main rule class (avelsieve_html_edit.php), with
 * some functions that are to be available to various Anti-SPAM-style rules.
 *
 * @package plugins
 * @subpackage avelsieve
 */
class avelsieve_html_edit_spamrule extends avelsieve_html_edit {

    /** @var boolean Advanced SPAM rule? */
    var $spamrule_advanced = false;

    /** @var int Initial number of whitelist items (input boxes) to display in 
     * the UI, if none are set in the rule.
     */
    var $whitelistitems = 3;

    /**
     * Empty Constructor
     */     
    function avelsieve_html_edit_spamrule() {
    }

    /**
     * Overwrite 'set rule data' to set the 'advanced' flag in the class, if it exists.
     *
     * @param array $data
     * @return void
     */
    function set_rule_data($data) {
        parent::set_rule_data($data);
        if(isset($data['advanced']) && $data['advanced']) {
            $this->spamrule_advanced = true;
        }
        if(isset($data['whitelist']) && sizeof($data['whitelist']) > $this->whitelistitems) {
            $this->whitelistitems = sizeof($data['whitelist']) + 1;
        }
    }
    
    /**
     * Whitelist.
     *
     * Emails with these header-criteria will never end up in Junk / Trash
     * / discard. This resembles the well-known list of conditions that is
     * available for a normal Sieve rule.
     *
     * This function can be resused by spam-type rules.
     * 
     * @return string
     */
    function edit_whitelist() {   
        $out = '<input type="hidden" name="whitelistitems" value="'.$this->whitelistitems.'" />';
    
        for($i=0; $i<$this->whitelistitems; $i++) {
            $out .= $this->header_listbox(
                isset($this->rule['whitelist'][$i]['header']) ? $this->rule['whitelist'][$i]['header'] : 'From' , $i
            );
            $out .= $this->matchtype_listbox(
                isset($this->rule['whitelist'][$i]['matchtype']) ?  $this->rule['whitelist'][$i]['matchtype'] : '' , $i, 'matchtype'
            );
            $out .= '<input name="cond['.$i.'][headermatch]" value="'.
                ( isset($this->rule['whitelist'][$i]['headermatch']) ? $this->rule['whitelist'][$i]['headermatch'] : '' ) .
                '" size="18" />'.
                '<br/>';
        }
        $out .= '<br/><input type="submit" name="whitelist_add" value="'._("More...").'"/><br/>';
        return $out;
    }
}


