<?php
/**
 * Licensed under the GNU GPL. For full terms see the file COPYING that came
 * with the Squirrelmail distribution.
 *
 * @version $Id: html_ruleedit.12.inc.php 935 2008-07-04 10:25:39Z avel $
 * @author Alexandros Vellis <avel@users.sourceforge.net>
 * @copyright 2007 Alexandros Vellis
 * @package plugins
 * @subpackage avelsieve
 */

/** Includes */
include_once(SM_PATH . 'plugins/avelsieve/include/html_main.inc.php');
include_once(SM_PATH . 'plugins/avelsieve/include/html_ruleedit.inc.php');

/**
 * Rule #12: A global whitelist.
 *
 * This does not produce any Sieve output, but _does_ affect the output of a 
 * SPAM rule.
 *
 * @package plugins
 * @subpackage avelsieve
 */
class avelsieve_html_edit_12 extends avelsieve_html_edit {
    /**
     * Constructor, that just calls the parent one.
     */     
    function avelsieve_html_edit_12(&$s, $mode = 'edit', $rule = array(), $popup = false, $errmsg = '') {
        global $avelsieve_rules_settings;
        $this->settings = $avelsieve_rules_settings[12];
        $this->avelsieve_html_edit($s, $mode, $rule, $popup, $errmsg);
    }

    /**
     * HTML for editing the whitelist.
     *
     * @param mixed $edit
     * @return string
     */
    function edit_rule($edit = false) {
        global $PHP_SELF, $color;
        
        $out = '<form name="addrule" action="'.$PHP_SELF.'" method="POST">';

        if($this->mode == 'edit') {
            $out .= '<input type="hidden" name="edit" value="'.$edit.'" />';
        }
        $out .= $this->table_header( _("Editing Whitelist") ).
                $this->all_sections_start();
        
        /* ---------- Error (or other) Message, if it exists -------- */
        $out .= $this->print_errmsg();
        
        $out .= $this->section_start( _("Whitelist") ).
                '<p>'. _("Messages coming from the email addresses that you enter here will never end up in your Junk folder or be considered as SPAM.") . '</p>'.
                '<p>'. _("Enter <strong>one (1) email address per line</strong>. Note that you can also enter an incomplete address or a mail domain.") . '</p>'.
                '<p><blockquote>'. _("Example:") . "<br/><pre>friend@example.org\nbusiness@example\n@example.edu\n" . '</blockquote><br/></p>';

        $out .= '<div style="text-align: center; margin-left: auto; margin-right: auto;">
                <textarea name="whitelist" cols="60" rows="30">';

        if(!empty($this->rule['whitelist'])) {
            for($i=0; $i<sizeof($this->rule['whitelist']); $i++) {
                $out .= $this->rule['whitelist'][$i] . "\n";
            }
        }
        $out .= '</textarea>
                </div>';

        $out .= $this->section_end();

        $out .= $this->submit_buttons().
            '</div></td></tr>'.
            $this->all_sections_end() .
            $this->table_footer().
            '</form>';

        return $out;
    }

    /**
     * Process HTML submission from namespace $ns (usually $_POST),
     * and put the resulting whitelist in $this->rule class variable.
     *
     * @param array $ns
     * @param array $rule
     * @return void
     */
    function process_input(&$ns, $unused = false) {
        $this->rule['type'] = 12;
        if(!isset($ns['whitelist'])) {
            return;
        }

        $input = trim($ns['whitelist']);
        if(empty($input)) {
            $this->rule['whitelist'] = array();
            return;
        }
        if($arr = explode("\n", $input)) {
            $whitelist_temp = array();
            foreach($arr as $k=>$val) {
                $val = trim($val);
                if(!empty($val)) {
                    $whitelist_temp[] = trim($val);
                }
            }
            $whitelist_temp = array_unique($whitelist_temp);
            $this->rule['whitelist'] = array_values($whitelist_temp);
        }
    }

    /**
     * Return a customized "Rule has been successfully changed"-type message.
     * @return string
     */
    function getSuccessMessage() {
        return '<strong>'.  _("Whitelist has been updated.") . '</strong>';
    }
}

