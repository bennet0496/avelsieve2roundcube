<?php
/**
 * Licensed under the GNU GPL. For full terms see the file COPYING that came
 * with the Squirrelmail distribution.
 *
 * This file contains functions that spit out HTML, mostly intended for use by
 * addrule.php and edit.php.
 *
 * @version $Id: html_ruleedit.inc.php 1057 2009-05-29 08:16:11Z avel $
 * @author Alexandros Vellis <avel@users.sourceforge.net>
 * @copyright 2004-2007 Alexandros Vellis
 * @package plugins
 * @subpackage avelsieve
 */

/** Includes */
include_once(SM_PATH . 'plugins/avelsieve/include/html_main.inc.php');

/**
 * HTML Output functions for rule editing / adding
 */
class avelsieve_html_edit extends avelsieve_html {
    /**
     * @var boolean Enable spamrule building?
     */
    var $spamrule_enable = false;

    /**
     * @var mixed Is the window a pop-up window (called from elsewhere)?
     */
    var $popup = false;

    /**
     * @var string Mode of operation, for editing new rule. One of:
     * 'wizard', 'addnew', 'edit', 'duplicate'
     */
    var $mode;
    
    /**
     * @var int Rule type
     */
    var $type = 0;

    /**
     * @var array The rule itself.
     */
    var $rule = array();

    /**
     * @var array Error messages during rule validation or addition
     */
    var $errmsg = array();

    /**
     * Constructor function.
     *
     * @param string $s Our Sieve Handler (Data Object). This is needed in
     *   order to have certain checks for capabilities of the specific backend.
     * @param string $mode
     * @param boolean $popup
     * @param mixed $errmsg Array or string of error messages to display.
     * @param array $additional_options Array of these options:
     *    * 'position' => int  (requested position for a new rule)
     * @return void
     */
    function avelsieve_html_edit(&$s, $mode = 'edit', $popup = false, $additional_options = '') {
        $this->avelsieve_html();
        $this->mode = $mode;
        $this->popup = $popup;
        $this->s = $s;
        
        $this->active_types = $this->get_active_types();

        $additional_options_available = array('position');
        if(!empty($additional_options) && is_array($additional_options)) {
           foreach($additional_options as $opt=>$val) {
               if(in_array($opt, $additional_options_available )) {
                   $this->$opt = $val;
               }
           }
        }
    }

    /**
     * Set Rule data.
     *
     * @param array $data
     * @return void
     */
    function set_rule_data($data) {
        $this->rule = $data;
    }

    /**
     * Set Rule type.
     *
     * @param int $type
     * @return void
     */
    function set_rule_type($type) {
        $this->type = $type;
        $this->rule['type'] = $type;
    }
    
    /**
     * Set Referrer. This is intended to be set, when after editing a rule / 
     * Sieve snippet, we want to go back to a different page than the "Rules 
     * Table" (plugins/avelsieve/table.php).
     *
     * @param string $referrerUrl
     * @param array $referrerArgs
     */
    function set_referrer($referrerUrl, $referrerArgs) {
        $this->referrerUrl = $referrerUrl;
        $this->referrerArgs = $referrerArgs;
    }
    
    /**
     * @return array of types valid for the current capabilities.
     */
    function get_active_types() {
        global $types;

        $active_types = array();
        foreach($types as $i=>$tp) {
            /* Skip disabled or not-supported */
            if(isset($tp['disabled'])) {
                continue;
            }
            if(array_key_exists('dependencies', $tp)) {
                foreach($tp['dependencies'] as $no=>$dep) {
                    if(!$this->s->capability_exists($dep)) {
                        continue 2;
                    }
                }
            }
            $active_types[$tp['order']] = $i;
        }
        ksort($active_types);
        return $active_types;
    }

    
    function select_condition_type($n, &$rule) {
        $out = '<select name="cond['.$n.'][kind]" id="condition_type_'.$n.'" ';
        if($this->js) {
            $out .= ' onChange="AVELSIEVE.edit.changeConditionKind('.$n.', this.value);"';
        }
        $out .= '>';

        foreach($this->s->condition_kinds as $kind=>$desc) {
            $out .= '<option value="'.$kind.'"';
            if(isset($rule['cond'][$n]['kind']) && $rule['cond'][$n]['kind'] == $kind) {
                $out .= ' selected="SELECTED"';
            }
            $out .= '>'.$desc.'</option>';
        }
        $out .= '</select>';
        return $out;
    }

    /**
     * Output rule type select widget.
     *
     * @param integer $index
     * @param string
     */
    function select_type($index, $name, $selected) {
        global $types;

        /*
            $dummy = '<p align="center">' . _("Rule Type") . ': '.
            $dum = '<p>'._("What kind of rule would you like to add?"). '</p>';

        if($this->rule['type'] == 0 && $select == 'select') {
            $dum = '<option value="">'. _(" -- Please Select -- ") .'</option>';
        }
        */
        /* FOR OLD TYPES */
        /*
        for($i=0; $i<sizeof($active_types); $i++) {
            $k = $active_types[$i];
            if($select == 'radio') {
                $out .= '<input type="radio" name="type" id="type_'.$k.'" value="'.$k.'" ';
                if($this->rule['type'] == $k) {
                    $out .= 'selected="SELECTED"';
                }
                $out .= '/> '.
                    '<label for="type_'.$k.'">'.$types[$k]['name'].'<br />'.
                    '<blockquote>'.$types[$k]['description'].'</blockquote>'.
                    '</label>';
            } elseif($select == 'select') {
                $out .= '<option value="'.$k.'" ';
                if($this->rule['type'] == $k) {
                    $out .= 'selected="SELECTED"';
                }
                $out .= '>'. $types[$k]['name'] .'</option>';
            }
        }
        if($select == 'select') {
                $out .= '</select>';
        }
        if(!$this->js) {
            $out .= ' <input type="submit" name="changetype" value="'._("Change Type").'" />';
        }
        $out .= '<br/>';
        */

        $out = '
            <input type="hidden" name="previous_'.$name.'" value="'.htmlspecialchars($selected).'" />'.
            '<select name="'.$name.'" id="condition_select_'.$index.'" id="'.$name.'" ';
        if($this->js) {
            $out .= ' onChange="AVELSIEVE.edit.changeCondition('.$index.', this.value); return false;"';
        }
        $out .= '>';

        foreach($this->active_types as $no=>$type) {
            $out .= '<option value="'.$type.'"';
            if($selected == $type) {
                $out .= ' selected="SELECTED"';
            }
            $out .= '>'.$types[$type]['name'].'</option>';
        }
        $out .= '</select>';
        return $out;
    }

    /**
      * Listbox widget with available headers to choose from.
      *
      * @param string $selected_header Selected header
      * @param int $n option number
      */
    function header_listbox($selected_header, $n) {
        global $headers;

        $options = array('toorcc' => _("To: or Cc") );
        foreach($headers as $no=>$h){
            $options[$h] = $h;
        }

        $out = $this->generic_listbox('cond['.$n.'][header]', $options, $selected_header);
        return $out;
    }
    
    /**
      * Listbox widget with available address headers to choose from.
      *
      * @param string $selected_header Selected header
      * @param int $n option number
      */
    function address_listbox($selected_header, $n) {
        global $available_address_headers;
        $options = array('toorcc' => _("To: or Cc") );
        foreach($available_address_headers as $no=>$h){
            $options[$h] = $h;
        }
        $out = $this->generic_listbox('cond['.$n.'][address]', $options, $selected_header);
        return $out;
    }
    
    /**
      * Listbox widget with available envelope values to choose from.
      *
      * @param string $selected_envelope Selected header
      * @param int $n option number
      */
    function envelope_listbox($selected_envelope, $n) {
        global $available_envelope;
        foreach($available_envelope as $no=>$h){
            $options[$h] = $h;
        }

        $out = $this->generic_listbox('cond['.$n.'][envelope]', $options, $selected_envelope);
        return $out;
    }
    
    /**
     * Matchtype listbox. Returns an HTML select listbox with available match
     * types, such as 'contains', 'is' etc.
     *
     * @param string $selected_matchtype
     * @param int $n
     * @param string $varname
     * @return string
     */
    function matchtype_listbox($selected_matchtype, $n, $varname = 'matchtype') {
        global $matchtypes, $comparators, $matchregex;

        $options = $matchtypes;
        if($this->s->capability_exists('relational')) {
            $options = array_merge($options, $comparators);
        }
        if($this->s->capability_exists('regex')) {
            $options = array_merge($options, $matchregex);
        }
        
        $out = $this->generic_listbox('cond['.$n.']['.$varname.']', $options, $selected_matchtype);
        return $out;
    }
    
    /**
     * The condition listbox shows the available conditions for a given match
     * type. Usually 'and' and 'or'.
     *
     * @param string $selected_condition
     * @return string
     */
    function condition_listbox($selected_condition) {
        global $conditions;
        $out = _("The condition for the following rules is:").
            $this->generic_listbox('condition', $conditions, $selected_condition);
        return $out;
    }

    /**
     * Output a whole line that represents a condition, that is the $n'th
     * condition in the array $this->rule['cond'].
     *
     * @param int $n
     * @return string
     * @see condition_header()
     * @see condition_address()
     * @see condition_envelope()
     * @see condition_size()
     * @see condition_body()
     * @see condition_datetime()
     * @see condition_all()
     */
    function condition($n) {
        global $types;
        $out = '<br/>';
        // FIXME
        if(isset($this->rule['cond'][$n]['kind'])) {
            $kind = $this->rule['cond'][$n]['kind'];
        } else { 
            $kind = $this->rule['cond'][$n]['kind'] = 'message';
        }

        if($kind == 'message') {
            if(!isset($this->rule['cond'][$n]['type'])) {
                $this->rule['cond'][$n]['type'] = 'header';
            }
        }

        $out .= $this->select_condition_type($n, $this->rule);
        $out .= '<span id="condition_type_div_'.$n.'">';

        if($kind == 'message') {
            $out .= $this->select_type($n, 'cond['.$n.'][type]', $this->rule['cond'][$n]['type']);

            if(isset($types[$this->rule['cond'][$n]['type']])) {
                $methodname = 'condition_' . $this->rule['cond'][$n]['type'];
                $out .= $this->$methodname($n);
            }

        } elseif($kind == 'datetime') {
            $myCondition = new avelsieve_condition_datetime($this->s, $this->rule, $n);
            $out .= $myCondition->datetime_common_ui();

        } elseif($kind == 'all') {
            $out .= $this->condition_all();
        }
        
        $out .= '</span>';

        //$out .= '</span>';
        return $out;
    }

    /** 
     * Output all conditions
     * @return string
     */
    function all_conditions() {
        global $maxitems, $comparators;

        if(isset($this->rule['condition'])) {
            $condition = $this->rule['condition'];
        } else {
            $condition = 'and';
        }

        if(isset($_POST['items'])) {
            $items = $_POST['items'];

        } elseif(isset($this->rule['cond'])) {
            $items = sizeof($this->rule['cond']);
        } else {
            global $items;
            if(!isset($items)) {
                $items = 1;
            }
        }
        if(isset($_POST['append'])) {
            $items++;
        } elseif(isset($_POST['less'])) {
            $items--;
        }

        $out = '';
        // if($items > 1) {
            $out .= $this->condition_listbox($condition) .'<br/>';
        // }

        $out .= '<div id="conditions">';
        for ( $n=0; $n < $items; $n++) {
            $out .= '<span id="condition_line_'.$n.'">' .  $this->condition($n) . '</span>';
        }
        $out .= '</div>';
        $out .= '<br/><br/>';

        $out .= '<input type="hidden" id="condition_items" name="items" value="'.$items.'" />';
        // FIXME What does the type have to do in here?
        $out .= '<input type="hidden" name="type" value="1" />';
        
        if(true || $items > 1) {
            $out .= '<input name="less" id="avelsieveconditionless" value="'. _("Less...") .'" onclick="AVELSIEVE.edit.deleteLastCondition(); return false;" type="button" />';
        }
        if(true || $items < $maxitems) {
            $out .= '<input name="append" id="avelsieveconditionmore" value="'. _("More..."). '" onclick="AVELSIEVE.edit.changeCondition(-1, \'header\'); return false;" type="button" />';
        }
        return $out;
        
    }
    
    /**
     * Output HTML code for header match rule.
     *
     * @param int $n Number of current condition (index of 'cond' array)
     * @return string
     */
    function condition_header($n) {
        $header = $matchtype = $headermatch = $index = $index_last = '';

        if(isset($this->rule['cond'][$n]['header'])) {
            $header = $this->rule['cond'][$n]['header'];
        }
        if(isset($this->rule['cond'][$n]['matchtype'])) {
            $matchtype = $this->rule['cond'][$n]['matchtype'];
        }
        if(isset($this->rule['cond'][$n]['headermatch'])) { 
            $headermatch = $this->rule['cond'][$n]['headermatch'];
        }
        if($this->s->capability_exists('index')) {
            if(isset($this->rule['cond'][$n]['index'])) { 
                $index = $this->rule['cond'][$n]['index'];
            }
            if(isset($this->rule['cond'][$n]['index_last'])) { 
                $index_last = $this->rule['cond'][$n]['index_last'];
            }
        }
        
        $out = $this->header_listbox($header, $n) .
            ($this->s->capability_exists('index') ? $this->index_option($n, $index, $index_last) : '') .
            $this->matchtype_listbox($matchtype, $n) .
            '<input type="text" name="cond['.$n.'][headermatch]" size="24" maxlength="255" value="'.
            htmlspecialchars($headermatch).'" />';
        
        return $out;
    }
    
    /**
     * Output HTML code for address match rule.
     *
     * @param int $n Number of current condition (index of 'cond' array)
     * @return string
     */
    function condition_address($n) {
        if(isset($this->rule['cond'][$n]['address'])) {
            $address = $this->rule['cond'][$n]['address'];
        } else {
            $address = '';
        }
        if(isset($this->rule['cond'][$n]['matchtype'])) {
            $matchtype = $this->rule['cond'][$n]['matchtype'];
        } else {
            $matchtype = '';
        }
        if(isset($this->rule['cond'][$n]['addressmatch'])) { 
            $addressmatch = $this->rule['cond'][$n]['addressmatch'];
        } else {
            $addressmatch = '';
        }
        $out = $this->address_listbox($address, $n) .
            $this->matchtype_listbox($matchtype, $n) .
            '<input type="text" name="cond['.$n.'][addressmatch]" size="24" maxlength="255" value="'.
            htmlspecialchars($addressmatch).'" />';
        return $out;
    }
    
    /**
     * Output HTML code for envelope match rule.
     *
     * @param int $n Number of current condition (index of 'cond' array)
     * @return string
     */
    function condition_envelope($n) {
        if(isset($this->rule['cond'][$n]['envelope'])) {
            $envelope = $this->rule['cond'][$n]['envelope'];
        } else {
            $envelope = '';
        }
        if(isset($this->rule['cond'][$n]['matchtype'])) {
            $matchtype = $this->rule['cond'][$n]['matchtype'];
        } else {
            $matchtype = '';
        }
        if(isset($this->rule['cond'][$n]['envelopematch'])) { 
            $envelopematch = $this->rule['cond'][$n]['envelopematch'];
        } else {
            $envelopematch = '';
        }
        $out = $this->envelope_listbox($envelope, $n) .
            $this->matchtype_listbox($matchtype, $n) .
            '<input type="text" name="cond['.$n.'][envelopematch]" size="24" maxlength="255" value="'.
            htmlspecialchars($envelopematch).'" />';
        return $out;
    }
        
    /**
     * Size match
     *
     * @param int $n Number of current condition (index of 'cond' array)
     * @return string
     */
    function condition_size($n) {
        if(isset($this->rule['cond'][$n]['sizerel'])) {
            $sizerel = $this->rule['cond'][$n]['sizerel'];
        } else {
            $sizerel = 'bigger';
        }
        if(isset($this->rule['cond'][$n]['sizeamount'])) {
            $sizeamount = $this->rule['cond'][$n]['sizeamount'];
        } else {
            $sizeamount = '';
        }
        if(isset($this->rule['cond'][$n]['sizeunit'])) {
            $sizeunit = $this->rule['cond'][$n]['sizeunit'];
        } else {
            $sizeunit = 'kb';
        }

        // $out = '<p>'._("This rule will trigger if message is").
        $out = '<select name="cond['.$n.'][sizerel]"><option value="bigger" name="sizerel"';
        if($sizerel == "bigger") $out .= ' selected="SELECTED"';
        $out .= '>'. _("bigger") . '</option>'.
            '<option value="smaller" name="sizerel"';
        if($sizerel == 'smaller') $out .= ' selected="SELECTED"';
        $out .= '>'. _("smaller") . '</option>'.
            '</select>' .
            _("than") . 
            '<input type="text" name="cond['.$n.'][sizeamount]" size="10" maxlength="10" value="'.$sizeamount.'" /> '.
            '<select name="cond['.$n.'][sizeunit]">'.
            '<option value="kb" name="sizeunit';
        if($sizeunit == 'kb') $out .= ' selected="SELECTED"';
        $out .= '">' . _("KB (kilobytes)") . '</option>'.
            '<option value="mb" name="sizeunit"';
        if($sizeunit == "mb") $out .= ' selected="SELECTED"';
        $out .= '">'. _("MB (megabytes)") . '</option>'.
            '</select>';
        return $out;
    }
        
    /**
     * Output HTML code for body match rule.
     *
     * @param int $n Number of current condition (index of 'cond' array)
     * @return string
     */
    function condition_body($n) {
        if(isset($this->rule['cond'][$n]['matchtype'])) {
            $matchtype = $this->rule['cond'][$n]['matchtype'];
        } else {
            $matchtype = '';
        }
        if(isset($this->rule['cond'][$n]['bodymatch'])) { 
            $bodymatch = $this->rule['cond'][$n]['bodymatch'];
        } else {
            $bodymatch = '';
        }
        $out = $this->matchtype_listbox($matchtype, $n) .
            '<input type="text" name="cond['.$n.'][bodymatch]" size="24" maxlength="255" value="'.
            htmlspecialchars($bodymatch).'" />';
        return $out;
    }
    
    function condition_datetime($n) {
        $myCondition = new avelsieve_condition_datetime($this->s, $this->rule, $n, 'date');
        $out = $myCondition->datetime_header_ui();
        $out .= $myCondition->datetime_common_ui();
        return $out;
    }
        
    /**
     * All messages 
     * @return string
     * @obsolete
     */
    function condition_all() {
        $out = _("All Messages");
        $dum = _("The following action will be applied to <strong>all</strong> incoming messages that do not match any of the previous rules.");
        return $out;
    }

    /**
     * Index
     *
     * @param string $selected
     * @param string $last
     * @return string
     */
    function index_option($n, $selected, $last) {
        $index_options = array(
            '' => '',
            '1' => _("1st"),
            '2' => _("2nd"),
            '3' => _("3rd"),
            '4' => _("4th"),
            '5' => _("5th"),
            '6' => _("6th"),
            '7' => _("7th"),
            '8' => _("8th"),
            '9' => _("9th"),
        );
        $index_last_options = array(
            '' => '',
            '1' => _("from the end"),
        );

        $out = 
            $this->generic_listbox('cond['.$n.'][index]', $index_options, $selected) .
            $this->generic_listbox('cond['.$n.'][index_last]', $index_last_options, $last)
            ;

        return $out;
    }

    /**
     * Return the HTML markup of the options of Sieve action $action.
     * This is a wrap around the relevant class of the Avelsieve action.
     *
     * If no such action exists, the function simply returns an empty
     * string.
     *
     * @return string
     */
    function action_html($action) {
        $out = '';
        $classname = 'avelsieve_action_'.$action;
         
        if(class_exists($classname)) {
            $$classname = new $classname($this->s, $this->rule);
            if($$classname != null) {
                if(AVELSIEVE_DEBUG >= 3 || $$classname->is_action_valid()) {
                    $out .= $$classname->action_html();
                }
            }
        }
        return $out;
    }

    /**
     * Output available actions in a radio-button style.
     * @return string
     */
    function rule_3_action() {
        /* Preferences from config.php */
        global $translate_return_msgs;
        /* Data taken from addrule.php */
        global $boxes, $emailaddresses;
        /* Other */
        global $avelsieve_actions;
        $out = '<p>'. _("Choose what to do when this rule triggers, from one of the following:"). '</p>';
        
        foreach($avelsieve_actions as $action) {
            $out .= $this->action_html($action);
        }
        return $out;
    }
    
    /**
     * Output available *additional* actions (notify, stop etc.) in a
     * checkbox-button style.
     *
     * @return string
     */
    function rule_3_additional_actions() {
        /* Preferences from config.php */
        global $translate_return_msgs;
        /* Data taken from addrule.php */
        global $boxes, $emailaddresses;
        /* Other */
        global $additional_actions;

        $out = '';
        
        foreach($additional_actions as $action) {
            $out .= $this->action_html($action);
        }
        return $out;
    }

    /**
     * Submit buttons for edit form -- not applicable for wizard
     * @return string
     */
    function submit_buttons() {
        $out = '<tr><td><div style="text-align: center">';
        switch ($this->mode) {
            case 'addnew':
                $out .= '<input type="submit" name="addnew" value="'._("Add New Rule").'" />';
                break;
            case 'addnewspam':
                $out .= '<input type="submit" name="addnew" value="'._("Add SPAM Rule").'" />';
                break;
            case 'duplicate':
                $out .= '<input type="hidden" name="dup" value="1" />';
                $out .= '<input type="submit" name="addnew" value="'._("Add New Rule").'" />';
                break;
            case 'duplicatespam':
                $out .= '<input type="hidden" name="dup" value="1" />';
                $out .= '<input type="submit" name="addnew" value="'._("Add SPAM Rule").'" />';
                break;
            case 'edit':
                $out .= '<input type="submit" name="apply" value="'._("Apply Changes").'" />';
                break;
        }
        if($this->popup) {
            $out .= ' <input type="submit" name="cancel" onClick="window.close(); return false;" value="'._("Cancel").'" />';
        } else {
            $out .= ' <input type="submit" name="cancel" value="'._("Cancel").'" />';
        }
        return $out;
    }

    /**
     * Output the HTML with the hidden input fields, for the referrer function. 
     * (After saving a rule or pressing "Cancel", the user is supposed to go 
     * back to where he was before).
     *
     * @return string
     */
    function referrer_html() {
        $out = '';
        if(isset($this->referrerUrl)) {
            $out .= '<input name="referrerUrl" type="hidden" value="'.htmlspecialchars($this->referrerUrl).'"/>';
            if(isset($this->referrerArgs) & !empty($this->referrerArgs)) {
                $out .= '<input name="referrerArgs" type="hidden" value="'.htmlspecialchars(serialize($this->referrerArgs)).'"/>';
            }
        }
        return $out;
    }

    /**
     * Main function that outputs a form for editing a whole rule.
     *
     * @param int $edit Number of rule that editing is based on.
     */
    function edit_rule($edit = false) {
        global $PHP_SELF, $color;

        if($this->mode == 'edit') {
            /* 'edit' */
            $out = '<form id="avelsieve_addrule" name="addrule" action="'.$PHP_SELF.'" method="POST">'.
                '<input type="hidden" name="edit" value="'.$edit.'" />'.
                (isset($this->position) ? '<input type="hidden" name="position" value="'.$this->position.'" />' : '') .
                $this->table_header( _("Editing Mail Filtering Rule") . ' #'. ($edit+1) ).
                $this->all_sections_start();
        } else {
            /* 'duplicate' or 'addnew' */
            $out = '<form id="avelsieve_addrule" name="addrule" action="'.$PHP_SELF.'" method="POST">'.
                $this->table_header( _("Create New Mail Filtering Rule") ).
                $this->all_sections_start();
        }
        /* ---------- Error (or other) Message, if it exists -------- */
        $out .= $this->print_errmsg();
        
        /* --------------------- 'if' ----------------------- */
        $out .= $this->section_start( _("Condition") );

        switch ($this->type) { 
            case 0:
            case 1: 
            default:
                // New-style generic conditions
                $out .= $this->all_conditions();
                break;
            case 2:            /* header */
            case 3:         /* size */
            case 4:         /* All messages */
                /* Obsolete */
                /* Something went wrong. Probably re-migrate. */
                /* FIXME */
                print "DEBUG: Something went wrong. Probably re-migrate.";
                break;
                
        }
        $out .= $this->section_end();

        /* --------------------- 'then' ----------------------- */
        
        $out .= $this->section_start( _("Action") );
        
        // if(isset($this->rule['folder'])) {
        //    $selectedmailbox = $this->rule['folder'];
        //}
        
        $out .= $this->rule_3_action().
            $this->section_end();

        $out .= $this->section_start( _("Additional Actions") );
        $out .= $this->rule_3_additional_actions().
            $this->section_end();


        /* --------------------- buttons ----------------------- */

        $out .= $this->submit_buttons().
            '</div></td></tr>'.
            $this->all_sections_end() .
            $this->table_footer().
            '</form>';

        return $out;
    }

    /**
     * Process HTML submission from namespace $ns (usually $_POST),
     * and put the resulting rule structure in $this->rule class variable.
     *
     * If any error happens,  put a human-readable error message in
     * $this->errmsg array, and return false. Otherwise, return true.
     *
     * @param array $ns
     * @param boolean $truncate_empty_conditions 
     * @return boolean
     *
     * @todo Provide a better interface for $created_mailbox_name (creation
     *  of a folder on-the-spot, for various operations), instead of the
     *  current hack.
     */
    function process_input(&$ns, $truncate_empty_conditions = false) {
        /* Reset current rule, as it will be overwritten from input */
        $this->rule = array();

        /* Type is needed for later */
        //if(isset($ns['type'])) $type = $ns['type'];
        /* If Part */
        $vars = array('type', 'condition');
    
        if($truncate_empty_conditions && isset($ns['cond'])) {
            /* Decide how much of the items to use for the condition of the
            * rule, based on the first zero / null /undefined variable to be
            * found. Also, reorder the conditions. */

            $match_vars = array('headermatch', 'addressmatch', 'envelopematch', 'sizeamount', 'bodymatch', 'datetype');
            $new_cond_indexes = array();
            foreach($ns['cond'] as $n => $c) {
                if(isset($c['kind'])) {
                    $kind = $c['kind'];
                } else {
                    $kind = 'message';
                }

                switch($kind) {
                case 'message':

                    foreach($match_vars as $m) {
                        if(!empty($c[$m]) || $c['type'] == 'all') {
                            $new_cond_indexes[] = $n;
                        }
                    }
                    $new_cond_indexes = array_unique($new_cond_indexes);
                    $new_cond_indexes = array_values($new_cond_indexes);
                    break;

                case 'datetime':
                default:
                    // TODO - for datetime, perform truncating / checking
                    $new_cond_indexes = array_keys($ns['cond']);
                }
            }
        
            $this->rule['cond'] = array();
            foreach($new_cond_indexes as $n => $index) {
                $this->rule['cond'][] = $ns['cond'][$index];
            }
            /* If it is completely empty, we must return an error. */
            if(empty($this->rule['cond'])) {
                $this->errmsg[] =  _("You have to define at least one condition.");
            }

        } else {
            $vars[] = 'cond';
        }
    
        if(isset($ns['action'])) {
            array_push($vars, 'action');
            switch ($ns['action']) { 
                case "1": /* keep */
                    break;
                case "2": /* discard */
                    break;
                case "3": /* reject w/ excuse */
                    array_push($vars, 'excuse');
                    break;
                case "4": /* redirect */
                    avelsieve_action_redirect::validate($ns, $this->errmsg);
                    array_push($vars, 'redirectemail', 'keep');
                    break;
                case "5": /* fileinto */
                    array_push($vars, 'folder');
                    break;
                case "6": /* vacation */
                    avelsieve_action_vacation::validate($ns, $this->errmsg);
                    array_push($vars, 'vac_addresses', 'vac_subject', 'vac_days', 'vac_message');
                    break;
                default:
                    break;
            }
        } else {
            /* User did not select anything from the radio buttons; default to
            * 'keep' */
            $this->rule['action'] = '1';
        }
        
        if(isset($ns['keepdeleted'])) {
            $vars[] = 'keepdeleted';
        }
        if(isset($ns['stop'])) {
            $vars[] = 'stop';
        }
        if(isset($ns['notify']['on']) && isset($ns['notify']['options']) &&
            !empty($ns['notify']['options'])) {
            $vars[] = 'notify';
        }
        
        if(isset($ns['imapflags']['on'])) {
            $flagsOptions = array('setflag', 'addflag', 'removeflag', 'flags');
            $flagsGroups = array('standard', 'labels');
            foreach($flagsOptions as $O) { 
                if(!empty($ns['imapflags'][$O])) {
                    $this->rule['imapflags'][$O] = array();
                    // For the two groups ('standard', 'labels') that have checkboxes 
                    foreach($ns['imapflags'][$O] as $k => $v) {
                        $this->rule['imapflags'][$O][base64_decode($k)] = 1;
                    }
                }
            }

            // For the final group ('custom') that is free-form
            if(!empty($ns['imapflags']['custom']) && !empty($ns['imapflags']['custom'][$O])) {
                $input = trim($ns['imapflags']['custom'][$O]);
                $flagsParsed = preg_split("/[\s,]+/", $input);
                foreach($flagsParsed as $newflag) {
                    $this->rule['imapflags'][$O][$newflag] = 1;
                }
            }
        }
    
        if(isset($ns['disabled'])) {
            $this->rule['disabled'] = 1;
        }
        
        /* Put all variables from the defined namespace (e.g. $_POST) in the rule
        * array. */
        foreach($vars as $myvar) {
            if(isset($ns[$myvar])) {
                $this->rule[$myvar]= $ns[$myvar];
            }
        }
    
        /* Special hack for newly-created folder */
        if(isset($this->rule['folder'])) {
            global $created_mailbox_name;
            if(!empty($created_mailbox_name)) {
                $this->rule['folder'] = $created_mailbox_name;
            }
        }
        
        /* For standard avelsieve rules > #10 */
        /*
        if(is_numeric($type) && $type >= 10) {
            $class_name = 'avelsieve_html_edit_'.$type;
            if(class_exists($class_name)) {
                call_user_func(array($class_name, 'process_user_input'), array(&$ns, &$rule, &$this->errmsg));
            }
        }
         */
    }
        
    /**
     * Return a customized "Rule has been successfully changed"-type message.
     * 
     * Child classes may change this as they see fit.
     *
     * @return string
     */
    function getSuccessMessage() {
        return '';
    }
}

