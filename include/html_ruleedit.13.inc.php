<?php
/**
 * Licensed under the GNU GPL. For full terms see the file COPYING that came
 * with the Squirrelmail distribution.
 *
 * @version $Id: html_ruleedit.13.inc.php 935 2008-07-04 10:25:39Z avel $
 * @author Kostantinos Koukopoulos <kouk@noc.uoa.gr>
 * @copyright 2007 Alexandros Vellis
 * @package plugins
 * @subpackage avelsieve
 */

/** Includes */
include_once(SM_PATH . 'plugins/avelsieve/include/html_main.inc.php');
include_once(SM_PATH . 'plugins/avelsieve/include/html_ruleedit.inc.php');

/**
 * Rule #13: Custom Sieve Code
 *
 * @package plugins
 * @subpackage avelsieve
 */
class avelsieve_html_edit_13 extends avelsieve_html_edit {

   /**
    * A rather empty constructor.
    */
   function avelsieve_html_edit_13(&$s, $mode = 'edit', $rule = array(), $popup = false, $errmsg = '') {
        $this->avelsieve_html_edit($s, $mode, $rule, $popup, $errmsg);
   }
   
   /**
    * Editing of the custom Sieve code just presents a textarea to the user.
    *
    * @param mixed $edit
    * @return string
    */
   function edit_rule($edit = false) {
      global $PHP_SELF, $color;

      if ($this->mode == 'edit') {
         $out = '<form name="addrule" action="'.$PHP_SELF.'" method="POST">'.
                '<input type="hidden" name="edit" value="'.$edit.'" />'.
         $this->table_header( sprintf( _("Editing Sieve Code (Rule #%s)"),  ($edit+1))).
         $this->all_sections_start();
      } else {
         $out = '<form name="addrule" action="'.$PHP_SELF.'" method="POST">'.
         $this->table_header( _("Create New Custom Sieve Rule") ).
         /* 'duplicate' or 'addnew' */
         $this->all_sections_start();
      }

      $out .= $this->print_errmsg();

      $out .= $this->section_start( _("Custom Rule") ).
            '<div style="text-align: center; margin-left: auto; margin-right: auto;">'.
            '<p>'. _("Please enter valid Sieve code in the textbox below.") . '<br/>'.
            '<small>'. _("Note: The <tt>require</tt> command is not needed; it will be added automatically at the start of the script.") .
            '</small><br/><small>'.
            sprintf( _("Sieve Capabilities supported: %s"), implode(', ', array_keys($this->s->capabilities))) .
            '</small></p>' .
            '<textarea name="customrule" cols="60" rows="15">'.
             (isset($this->rule['code']) ? trim(htmlspecialchars($this->rule['code'])) : '')
             ."\n".
            '</textarea>
             </div>'.
            $this->section_end();
      
      $out .= $this->submit_buttons().
         '</div></td></tr>'.
         $this->all_sections_end().
         $this->table_footer().
         '</form>';

      return $out; 
    }

    /**
     * Process HTML submission from namespace $ns (usually $_POST),
     * and put the resulting code in $this->rule class variable.
     *
     * @param array $ns
     * @param array $rule
     * @return void
     */
    function process_input(&$ns, $unused = false) {
        $this->rule['type'] = 13;
        if(!isset($ns['customrule'])){
           $this->errmsg = _("Please enter a valid Sieve code snippet.");
        } else {
           $code = trim($ns['customrule']);
        }

        if(!empty($code)) {
           $this->rule['code'] = $code;
        } else {
           $this->rule['code'] = '';
           $this->errmsg = _("Please enter a valid Sieve code snippet.");
        }
    }
}

