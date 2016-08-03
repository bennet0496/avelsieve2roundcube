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
 * @version $Id: html_rulestable.inc.php 1026 2009-05-21 09:30:49Z avel $
 * @author Alexandros Vellis <avel@users.sourceforge.net>
 * @copyright 2004-2007 The SquirrelMail Project Team, Alexandros Vellis
 * @package plugins
 * @subpackage avelsieve
 */

/** Includes */
include_once(dirname(__FILE__).'/html_main.inc.php');

/**
 * HTML Output functions for rule editing / adding
 */
class avelsieve_html_rules extends avelsieve_html {
    /**
     * @param array SIEVE Rules that are to be printed.
     */
    var $rules = array();
    
    /**
     * @param string Display mode: 'verbose','terse','source' or 'debug'
     */
    var $mode = 'terse';
    
    /**
     * Constructor function, that initializes the environment for proper
     * displaying of the rules table.
     *
     * @return void
     */
    function avelsieve_html_rules(&$rules, $mode = 'terse') {
        global $avelsieve_maintypes, $scriptHints;

        $this->avelsieve_html();
        $this->rules = $rules;
        $this->mode = $mode;
        
        // These UI "hints" are initialized and discovered in the table.php script.
        if(isset($scriptHints)) {
            $this->scriptHints = $scriptHints;
        }
    }

    /**
     * "Create new rules" text, for when no rules exist.
     * @return string
     */
    function rules_create_new() {
        return ' <p>'.
            _("Here you can add or delete filtering rules for your email account. These filters will always apply to your incoming mail, wherever you check your email.").
            '</p>' .
            '<p>' . _("You don't have any rules yet. Feel free to add any with the button &quot;Add a New Rule&quot;. When you are done, please select &quot;Save Changes&quot; to get back to the main options screen.") . "</p>";
    }
    
    /**
     * Introductory text.
     * @return string
     */
    function rules_blurb() {
        global $color, $conservative, $scriptinfo;
        
        $out = '';
        if(sizeof($this->rules) < 3) {
            $out = " <p>"._("Here you can add or delete filtering rules for your email account. These filters will always apply to your incoming mail, wherever you check your email.")."</p> ";
        }
        
        if($conservative) {
            $out .= "<p>"._("When you are done with editing, <strong>remember to select &quot;Save Changes&quot;</strong> to activate your changes!")."</p>";
        }
    
        $out .= $this->rules_confirmation_text();
    
        if(isset($scriptinfo['created'])) {
            $out .= $this->scriptinfo($scriptinfo);
        }

        $out .= $this->scripthints();
        
        // Removed ATM
        $dummy = "<p>"._("The following table summarizes your current mail filtering rules.")."</p>";

        return $out;
    }

    /**
     * Output UI-friendly script "hints":
     * 1) "inconsistent" folders: a folder is mentioned in "fileinto", but the
     *    actual folder does not exist.
     * 2) warning that vacation rule is active. (so that the user will not forget
     *    to disable it when she comes from vacation).
     *
     * @return string
     */
    function scripthints() {
        global $color;
        $out = '';

        // 1) inconsistent folders / fileinto
        if(!empty($this->scriptHints['inconsistent_folders'])) {
            $out .= '<p style="color:'.$color[2].'">' .
                ($this->useimages ? '<img src="'.$this->iconuri.'exclamation.png" alt="(!)" border="0" />'. ' ' : '' ) .
                _("Warning: In your rules, you have defined an action that refers to a folder that does not exist or a folder where you do not have permission to append to.") .
                '</p>';
        }

        // 2) vacation rule is active
        if(!empty($this->scriptHints['vacation_rules'])) {
            $fnum = $this->scriptHints['vacation_rules'][0]; // First rule number
            $out .= '<p style="color:'.$color[8].'; font-weight: bold;">' .
                ($this->useimages ? '<img src="'.$this->iconuri.'lightbulb.png" alt="(i)" border="0" />'. ' ' : '' ) .
                sprintf( _("Note: A <a href=\"%s\">Vacation Autoresponder</a> is active (<a href=\"%s\">Rule #%s</a> in your current Mail Filtering Rules).<br/>Don't forget to disable it or delete it when you are back."),
                    'edit.php?edit='.$fnum, '#rule_row_'.$fnum, $fnum+1) .
                '</p>';
        }

        return $out;
    }

    /**
     * Rules Table Header + 1st row, which is the heading
     * @return string
     */
    function rules_table_header() {
        global $color, $displaymodes;
        $out = '
        <table id="avelsieve_rules_table" cellpadding="3" cellspacing="2" border="0" align="center" width="97%" frame="box">
        <tr bgcolor="'.$color[0].'">
        <td style="white-space:nowrap" valign="middle">';
        
        $out .= _("No") . '</td><td></td><td class="avelsieve_controls" valign="middle">'. _("Options").'</td>'.
            '<td>'. _("Description of Rule").
            ' <small>(' . _("Display as:");
        
        foreach($displaymodes as $id=>$info) {
            if($this->mode == $id) {
                $out .= ' <strong><span title="'.$info[1].'">'.$info[0].'</span></strong>';
            } else {
                $out .= ' <a href="'.$_SERVER['SCRIPT_NAME'].'?mode='.$id.'" title="'.$info[1].'">'.$info[0].'</a>';
            }
        }
        $out .= ')</small>'.
            '</td><td style="white-space:nowrap;">'. _("Position") .'</td>'.
            "</td></tr>\n";
        return $out;
    }
        
    /**
     * Returns the 'communication' aka 'comm' string from the previous screen,
     * for instance edit.php.
     * @return string
     */
    function rules_confirmation_text() {
        $out = $this->retrieve_avelsieve_messages();
	unset($_SESSION['comm']);
        return $out;
    }
    
    /**
     * Footer
     * @return string
     */
    function rules_table_footer() {
        return '</table>';
    }
        
    /**
     * Searches the available rules in a script for the rules that are of "unique" 
     * type. E.g. whitelist, Junk Mail.
     * 
     * @todo Probably a duplicate-rule supression / notification could be handy.
     *    However, it will do no major harm to leave this without one.
     *
     * @todo This is a good place to do the suggesting of rule placement.
     *
     * @return array Key: the rule number, Value: the rule position.
     */
    function discoverUniqueRules() {
        global $avelsieve_enable_rules, $avelsieve_maintypes;

        $ret = array();

        foreach($avelsieve_maintypes as $no=>$info) {
            if($info['unique']) {
                for($i=0; $i<sizeof($this->rules); $i++) {
                    if(isset($this->rules[$i]['type']) && $this->rules[$i]['type'] == $no) {
                        $ret[$no] = $i;
                        continue 2;
                    }
                }
            }
        }
    }

    /**
     * Submit Links / Buttons for adding new rules and edit screens.
     *
     * @param boolean $horizontal
     * @return string
     */
    function button_addnewrule($horizontal = false) {
        global $avelsieve_enable_rules, $avelsieve_maintypes;

        if($horizontal) {
            $links_delimiter = ' | ';
        } else {
            $links_delimiter = '<br/>';
        }

        $out = ' <a href="edit.php?addnew=1" rel="nofollow" style="white-space: nowrap;">'. 
            ($this->useimages == true ? '<img src="'.$this->iconuri.'add.png" alt="[]" border="0" /> ' : '') .
            '<strong>' .
            _("Add a new Rule") . '</strong></a>';

        if(!empty($avelsieve_enable_rules)) {
            $uniqueRulesPositions = $this->discoverUniqueRules();
            foreach($avelsieve_enable_rules as $r) {
                if(!isset($avelsieve_maintypes[$r]['linktext'])) continue;

                if($r == 12 && isset($uniqueRulesPositions[12])) {
                    // Global whitelist special: edit existing whitelist
                    $href = 'edit.php?edit='.$uniqueRulesPositions[12].'&amp;type='.$r;
                } else {
                    $href = 'edit.php?addnew=1&amp;type='.$r;
                }
                $out .= $links_delimiter . '<a href="'.$href.'" rel="nofollow" style="white-space: nowrap;">'.
                        ($this->useimages == true ? '<img src="'.$avelsieve_maintypes[$r]['img'].'" alt="[]" border="0" /> ' : '') .
                        '<strong>' . $avelsieve_maintypes[$r]['linktext'] . '</strong></a>';
            }

        }
        $null = null;
        $out .= concat_hook_function('avelsieve_rulestable_buttons', $null);
        return $out;
    }
    
    /**
     * Submit button for deleting selected rules
     * @return string
     */
    function button_deleteselected() {
            return '<input type="submit" name="deleteselected" value="' . _("Delete") . '" '.
                'onclick="return confirm(\''._("Really delete selected rules?").'\');" />';
    }

    /**
     * Submit button for enabling selected rules
     * @return string
     */
    function button_enableselected() {
        return '<input type="submit" name="enableselected" value="' . _("Enable") . '" />';
    }

    /**
     * Submit button for disabling selected rules
     * @return string
     */
    function button_disableselected() {
        return '<input type="submit" name="disableselected" value="' . _("Disable") . '" />';
    }
    
    
    function rules_footer() {
        global $conservative;
        $out = '';
        if($conservative) {
            $out = '<div style="text-align: center;"><p>'.
                _("When you are done, please click the button below to return to your webmail.").
                '</p><input name="logout" value="'._("Save Changes").'" type="submit" /></div>';
        }
        return $out;
    }
    
    /**
     * Output link for corresponding rule function (such as edit, delete, move).
     *
     * @param string $name
     * @param int $i
     * @param string $url Which page to link to
     * @param string $xtra Extra stuff to be passed to URL
     * @param array $attribs Additional attributes for <a> element.
     * @param boolean $showText Show the relevant Text alongside
     * @return string
     */
    function toolicon ($name, $i, $url = "table.php", $xtra = "", $attribs=array(), $showText = false) {
        global $imagetheme, $location, $avelsievetools;
    
        $desc = $avelsievetools[$name]['desc'];
        $img = $avelsievetools[$name]['img'];

        $out = ' <a href="'.$url.'?rule='.$i.'&amp;'.$name.'='.$i.
                (!empty($xtra) ? '&amp;'.$xtra : '') .
                '" rel="nofollow"';
    
        if(sizeof($attribs) > 0) {
            foreach($attribs as $key=>$val) {
                $out .= ' '.$key.'="'.$val.'"';
            }
        }
        $out .= '>';
    
        if($this->useimages) {
            $out .= '<img title="'.$desc.'" src="'.$location.'/images/'.$imagetheme.
            '/'.$img.'" alt="'.$desc.'" border="0" />';
            if($showText) {
                $out .= ' '.$desc;
            }
        } else {
            $out .= $desc;
        }

        $out .= '</a>';
        return $out;
    }
    
    /**
     * Output script information (last modification date etc.)
     * @param array $scriptinfo
     * @return string
     * @todo Move these to a different page, so that it will not clutter the 
     *   main table screen.
     */
    function scriptinfo($scriptinfo) {
        $out = '';
        if(function_exists('getLongDateString')) {
            bindtextdomain('squirrelmail', SM_PATH . 'locale');
            textdomain('squirrelmail');
            $cr = getLongDateString($scriptinfo['created']);
            $mo = getLongDateString($scriptinfo['modified']);
            bindtextdomain ('avelsieve', SM_PATH . 'plugins/avelsieve/locale');
            textdomain ('avelsieve');
            
            // $out = '<p><em>'. _("Last modified:").'</em> <strong>'.$mo.'</strong></p>';
        
            /*
            $out = '<p><em>'._("Created:").'</em> '.$cr.'.<br /><em>'.
            _("Last modified:").'</em> <strong>'.$mo.'</strong></p>';
            */
        
        } else {
            /* Pretty useless information to be displayed every time */
            $dummy = _("Created:");
            /*
            $out = '<p><em>'._("Created:").'</em> '.
            date("Y-m-d H:i:s",$scriptinfo['created']).'. <em>'.
            */
            $dummy = _("Last modified:");
            
            // '</em> <strong>'.
            //date("Y-m-d H:i:s",$scriptinfo['modified']).'</strong></p>';
        }
    
        if(AVELSIEVE_DEBUG > 0) {
            global $avelsieve_version;
            $out .= '<p>Versioning Information:</p>' .
                '<ul><li>Script Created using Version: '.$scriptinfo['version']['string'].'</li>'.
                '<li>Installed Avelsieve Version: '.$avelsieve_version['string'] .'</li></ul>';
        }
        return $out;
    }

    /**
     *
     */
    function rules_confirmation() {
        global $color;
        $out = $this->table_header( _("Current Mail Filtering Rules") ).
            $this->all_sections_start().
            $this->rules_confirmation_text().
            $this->all_sections_end().
            ' <br/><input type="button" name="Close" onClick="window.close(); return false;" value="'._("Close").'" />';
            $this->table_footer();
        return $out;
    }

    /**
     * Main function to output a whole table of SIEVE rules.
     * @return string
     */
    function rules_table() {
        global $color, $avelsieve_maintypes;
        $null = null; // For plugin hooks and PHP4/5 compatibility
        
        $out = '<form name="rulestable" method="POST" action="table.php">'.
                '<input name="position" value="" type="hidden" />';

        if(empty($this->rules)) {
            $out .= $this->table_header(_("No Filtering Rules Defined Yet")).
                $this->all_sections_start().
                '<tr><td bgcolor="'.$color[4].'" align="center">'.
                $this->rules_create_new().
                $this->button_addnewrule().
                $this->rules_footer().
                '</td></tr>'.
                $this->all_sections_end() .
                $this->table_footer().
                '</form>';
            return $out;
        }

        $out .= // $this->all_sections_start().
            $this->table_header( _("Current Mail Filtering Rules") ).
            '<p>'. $this->button_addnewrule(true) . '</p>'.
            // '<tr><td bgcolor="'.$color[4].'" align="center">'.
            $this->rules_blurb().
            $this->rules_table_header();
            // '</td></tr>';

        $toggle = false;
        for ($i=0; $i<sizeof($this->rules); $i++) {
            $bgcolor = ($toggle? $color[12] : $color[4]);
            $out .="\n" . '<tr bgcolor="'.$bgcolor.'" id="rule_row_'.$i.'">'.
                '<td>'.($i+1).'</td>';
            
            /* === Column:: Checkbox === */
            $out .= '<td>'.
                '<input type="checkbox" name="selectedrules[]" value="'.$i.'" '.
                ($this->js ? 'onclick="if(this.checked) { document.getElementById(\'rule_row_'.$i.'\').style.backgroundColor = \''.$color[16].'\'; }
                        else { document.getElementById(\'rule_row_'.$i.'\').style.backgroundColor = \''.$bgcolor.'\'; }; return true; " ' : '' ) 
                . ' /></td>';
                
            /* === Column:: Controls (Edit/ Delete), Buttons === */
            $out .= '<td style="white-space: nowrap" class="avelsieve_controls">';

            /* 1) Important controls: Edit, Move Up, Move Down, Delete */
            /* Edit */
            if($this->rules[$i]['type'] < 100) {
                $out .= $this->toolicon('edit', $i, 'edit.php', "type=".$this->rules[$i]['type'],array(),true);
            } else {
                $args = do_hook('avelsieve_edit_link', $null);
                $out .= $this->toolicon('edit', $i, $args[0], $args[1]);
                unset($args);
            }
            
            
            /* Delete */
            if(!$avelsieve_maintypes[$this->rules[$i]['type']]['undeletable']) {
                $out .= '<br/>'.$this->toolicon("rm", $i, "table.php", "",
                    array('onclick'=>'return confirm(\''._("Really delete this rule?").'\')'), true);
            }


            /* Duplicate */
            /*
            if($this->rules[$i]['type'] < 100) {
                $out .= $this->toolicon('dup', $i, "edit.php", "type=".$this->rules[$i]['type']."&amp;dup=1");
            } else {
                $args = do_hook('avelsieve_duplicate_link', $null); 
                $out .= $this->toolicon('dup', $i, $args[0], $args[1]);
                unset($args);
            }
             */
        
            if($this->js) {
                $out .= '<br/><div id="show_more_options_'.$i.'">'.
                    '<a class="avelsieve_more_options_link" onclick="'.
                        ($this->js == 2 ?
                            // scriptaculous
                            'new Effect.Highlight(\'rule_row_'.$i.'\', {duration: 4}); new Effect.SlideDown(\'morecontrols_'.$i.'\');
                            new Effect.SlideUp(\'show_more_options_'.$i.'\'); return true;' :
                            // simpler javascript
                            'AVELSIEVE.util.hideDiv(\'show_more_options_'.$i.'\'); AVELSIEVE.util.showDiv(\'morecontrols_'.$i.'\');return true;'
                        ) .
                    '">'.
                    '<img src="'.$this->iconuri.'arrow_right.png" /> '. _("More Options...") .'</a>'.
                    '</div>';
            
                $out .= '<small><a class="avelsieve_expand_link" onclick="'.$this->js_toggle_display("morecontrols_$i", true).'return true;">';
            }

            $out .= '<br/><div id="morecontrols_'.$i.'" name="morecontrols_'.$i.'" '.
                    ($this->js ? 'style="display:none"' : '' ) . '>';

            $out .= '<select name="morecontrols['.$i.']" onchange="return AVELSIEVE.table.handleOptionsSelect(this, '.$i.');">'.
                    '<option value="" style="font-weight: bold" selected="">'. _("More Options...") . '</option>'.

                    /* Movement */
                    '<option value="mvtop" '.($i == 0? 'disabled=""' : '').'>'. _("Move to Top") . '</option>'.
                    '<option value="mvbottom" '.($i == sizeof($this->rules) -1 ? 'disabled=""': '').'>'.
                        _("Move to Bottom") . '</option>'.
                    ($this->js ? '<option value="mvposition" '.(sizeof($this->rules) < 2 ? ' disabled=""': '').'>'. _("Move to Position...") . '</option>' : '') .
                    '<option value="" disabled="">'. _("--------") . '</option>'.
                    
                    /* Duplicate */
                    '<option value="duplicate"'.($avelsieve_maintypes[$this->rules[$i]['type']]['unique'] ? ' disabled=""' : '') .
                        '>'. _("Duplicate this Rule") . '</option>'.

                    /* Insert */
                    '<option value="insert">'. _("Insert a New Rule here") . '</option>'.

                    '<option value="" disabled="">'. _("--------") . '</option>';

                    /* Enable / Disable */
            
                    /* TODO - accomodate for rule #11 which uses the reverse flag 'enabled' */
            $out .= (isset($this->rules[$i]['disabled']) ?
                        '<option value="enable"'.($avelsieve_maintypes[$this->rules[$i]['type']]['undeletable'] ? ' disabled=""' : '').'>'. _("Enable") . '</option>' :
                        '<option value="disable"'.($avelsieve_maintypes[$this->rules[$i]['type']]['undeletable'] ? ' disabled=""' : '').'>'. _("Disable") . '</option>') . 
                    //'<option value="" disabled="">'. _("--------") . '</option>'.
                    '</select>'.
                    '</div>';
            // Future functionality:
            $dummy = '<option value="sendemail">'. _("Send this Rule by Email") . '</option>';

            if(!$this->js) {
                $out .= '<input type="submit" name="morecontrols_submit" value="'. _("Go") .'" />';
            }
            $out .= '</td>';
            
            
            /* Column:: Rule description */
            $out .= '<td class="avelsieve_rule_description">'. makesinglerule($this->rules[$i], $this->mode) .'</td>';

            /* Column:: Movement controls */
            $out .= '<td style="white-space: nowrap" class="avelsieve_controls">'.
                ($i != 0 ? $this->toolicon("mvup", $i, "table.php", "") : '<span style="margin-left: 12px; margin-right: 6px; display: inline;">&nbsp;</span>').
                ($i != sizeof($this->rules)-1 ? $this->toolicon("mvdn", $i, "table.php", "") : '<span style="margin-left: 6px; margin-right: 6px; display: inline;">&nbsp;</span>').
                "</td></tr>\n";
        
            if(!$toggle) {
                $toggle = true;
            } elseif($toggle) {
                $toggle = false;
            }
        }
        
        $out .='<tr><td colspan="5">'.
            '<table width="100%" border="0"><tr><td align="left">'.
            _("Action for Selected Rules:") . '<br/>' .
            $this->button_enableselected(). 
            $this->button_disableselected(). '<br/>' .
            $this->button_deleteselected(). '<br/>' .
            '</td><td align="right">'.
            $this->button_addnewrule().
            '</td></tr></table>'. 
            '</td></tr>'.
            $this->rules_footer().
            $this->all_sections_end() .
            $this->table_footer().
            '</form>';

        return $out;
    }
}

