<?php
/**
 * Licensed under the GNU GPL. For full terms see the file COPYING that came
 * with the Squirrelmail distribution.
 *
 * @version $Id: html_ruleedit.10.inc.php 1020 2009-05-13 14:10:13Z avel $
 * @author Alexandros Vellis <avel@users.sourceforge.net>
 * @copyright 2004-2007 Alexandros Vellis
 * @package plugins
 * @subpackage avelsieve
 */

/** Includes */
include_once(SM_PATH . 'plugins/avelsieve/include/html_main.inc.php');
include_once(SM_PATH . 'plugins/avelsieve/include/html_ruleedit.inc.php');
include_once(SM_PATH . 'plugins/avelsieve/include/sieve_rule_spam.inc.php');

/**
 * Rule #10: Customized Anti-SPAM rule (original version)
 *
 * @package plugins
 * @subpackage avelsieve
 */
class avelsieve_html_edit_10 extends avelsieve_html_edit_spamrule {
    /**
     * Constructor, that just calls the parent one.
     */     
    function avelsieve_html_edit_10(&$s, $mode = 'edit', $rule = array(), $popup = false, $errmsg = '') {
        global $avelsieve_rules_settings;
        $this->settings = $avelsieve_rules_settings[10];
        $this->avelsieve_html_edit($s, $mode, $rule, $popup, $errmsg);
    }

    /**
     * Spamrule module settings
     *
     * @return string
     */
    function module_settings($module) {
        $out = '';
        foreach($this->settings['spamrule_tests'][$module]['available'] as $key=>$val) {
            $out .= '<div id="test_'.$key.'"> <p><strong>'.$val.'</strong>';
            $out .= '<br/><input type="radio" name="'.$key.'" value="NONE" id="'.$key.'_NONE" /> '.
                    '<label for="'.$key.'_NONE">'. _("No check") . '</label>';

            foreach($this->settings['spamrule_tests'][$module]['values'] as $res=>$res_desc) {
                    $out .= '<br/><input type="radio" name="'.$key.'" value="'.$res.'"" id="'.$key.'_'.$res.'" /> '.
                        '<label for="'.$key.'_'.$res.'">'.
                        ( isset($this->settings['icons'][$res]) ?  '<img src="'.$this->settings['icons'][$res].'" alt="[]" /> ' : '' ) .
                        $res_desc.'</label>';
                            
            }
        }
        return $out;
    }
    
    /**
     * Main function that outputs a form for editing a whole rule.
     *
     * @param int $edit Number of rule that editing is based on.
     * @return string
     */
    function edit_rule($edit = false) {
        global $PHP_SELF, $color, $avelsieve_maintypes, $spamrule_actions, 
                $data_dir, $username, $plugins, $junkfolder_days;

        $out = '<form name="addrule" action="'.$PHP_SELF.'" method="POST">';
        $out .= '<input type="hidden" name="type" value="10" />';
        
        if($this->mode == 'edit') {
            /* 'edit' */
            $out .= '<input type="hidden" name="edit" value="'.$edit.'" />'.
                $this->table_header( sprintf( _("Editing %s"),  (sprintf(_("(Rule #%s)"), ($edit+1))) ));
        } else {
            /* 'duplicate' or 'addnew' */
            $out .= $this->table_header( sprintf(_("Add a new %s "), $avelsieve_maintypes[$this->rule['type']]['desc']) );
        }
        
        $out .= $this->all_sections_start().
            $this->section_start( _("Configure Anti-SPAM Protection") ).
            '<p>' . _("All incoming mail is checked for unsolicited commercial content (SPAM) and marked accordingly. This special rule allows you to configure what to do with such messages once they arrive to your Inbox.") . '</p>';
        
        /* ---------- Error (or other) Message, if it exists -------- */
        $out .= $this->print_errmsg();
    
        if(!$this->spamrule_advanced) {
            // FIXME string
            $out .= '<p>'. sprintf( _("Select %s to add the predefined rule, or select the advanced SPAM filter to customize the rule."), '<strong>' . _("Add Spam Rule") . '</strong>' ) . '</p>'.
                '<p style="text-align:center"> <input type="submit" name="intermediate_action[spamrule_switch_to_advanced]" value="'. _("Advanced Spam Filter...") .'" /></p>';
        
        } else {
        
            /*
            include_once(SM_PATH . 'plugins/filters/filters.php');
            $spamfilters = load_spam_filters();
            */
    
            $out .= '<input type="hidden" name="spamrule_advanced" value="1" />'.
                '<ul><li><strong>'. _("Target Score") . '</strong>';
        
            /* If using sendmail LDAP configuration, get the sum of maximum score
             * and overwrite default setting 'spamrule_score_max'. */
            if(isset($spamrule_rbls)) {
                $this->settings['spamrule_score_max'] = 0;
                foreach($spamrule_rbls as $no=>$info) {
                    if(isset($info['serverweight'])) {
                        $this->settings['spamrule_score_max'] += $info['serverweight'];
                    }
                }
            }
        
            $out .= '<br/>'. sprintf( _("Messages with SPAM-Score higher than the target value, the maximum value being %s, will be considered SPAM.") , $this->settings['spamrule_score_max'] ) .
                '<br/>'. _("Target Score") . ': <input name="score" id="score" value="'.$this->rule['score'].'" size="4" /><br/><br/>'.
        
                '</li><li><strong>'. _("SPAM Lists to check against") .'</strong><br/>';
            
            /**
            * Print RBLs that are available in this system.
            * 1) Check for RBLs in LDAP Sendmail configuration
            * 2) Use RBLs supplied in config.php
            */
            
            if(isset($spamrule_rbls)) {
                /* from LDAP */
                foreach($spamrule_rbls as $no=>$info) {
                    $out .= '<input type="checkbox" name="tests[]" value="'.$info['test'].'" id="spamrule_test_'.$no.'" ';
                    if(in_array($info['test'], $tests)) {
                        $out .= 'checked="CHECKED" ';
                    }
                    $out .= '/> '.
                        '<label for="spamrule_test_'.$no.'">'.$info['name'].' ('.$info['serverweight'].')</label><br />';
                }
                    
            } elseif(isset($this->settings['spamrule_tests'])) {
            /* from rule configuration */
                foreach($this->settings['spamrule_tests'] as $st=>$txt) {
                    $out .= '<input type="checkbox" name="tests[]" value="'.$st.'" id="spamrule_test_'.$st.'" ';
                    if(in_array($st, $this->rule['tests'])) {
                        $out .= 'checked="CHECKED" ';
                    }
                    $out .= '/> '.
                        '<label for="spamrule_test_'.$st.'">'.$txt.'</label><br />';
                }
            }
        /*
        } elseif(isset($spamrule_filters)) {
        foreach($spamrule_filters as $st=>$fi) {
            $out .= '<input type="checkbox" name="tests[]" value="'.$st.'" id="spamrule_test_'.$st.'" ';
            if(in_array($st, $tests)) {
                $out .= 'checked="CHECKED" ';
            }
            $out .= '/> '.
                '<label for="spamrule_test_'.$st.'">'$fi.['name'].'</label><br />';
        }
        */
        $out .= '<br/><br/></li>';
    
        /**
         * Whitelist 
         */
        
        $out .= '<li><strong>' . _("Whitelist") . '</strong>'.
            '<br/>'. _("Messages that match any of these header rules will never end up in Junk Folders or regarded as SPAM.") .
            '<br/><br/>';
        $out .= $this->edit_whitelist() . '<br/></li>';
    
        /**
         * Action
         */
        $out .= '<li><strong>'. _("Action") . '</strong><br/>';
        
        $trash_folder = getPref($data_dir, $username, 'trash_folder');
        foreach($spamrule_actions as $ac=>$in) {
        
            if($ac == 'junk' && (!in_array('junkfolder', $plugins))) {
                continue;
            }
            if($ac == 'trash' && ($trash_folder == '' || $trash_folder == 'none')) {
                continue;
            }
        
            $out .= '<input type="radio" name="action" id="action_'.$ac.'" value="'.$ac.'" '; 
            if($this->rule['action'] == $ac) {
                $out .= 'checked="CHECKED" ';
            }
            $out .= '/> ';
        
            $out .= ' <label for="action_'.$ac.'"><strong>'.$in['short'].'</strong> - '.$in['desc'].'</label><br/>';
            }
            $out .= '</li></ul>';
        }

        if(isset($junkprune_saveme)) {
            $out .= '<input type="hidden" name="junkprune_saveme" value="'.$junkfolder_days.'" />';
        }
    
        /* STOP */
            
        $out .= '<br /><input type="checkbox" name="stop" id="stop" value="1" ';
        if(isset($stop)) {
            $out .= 'checked="CHECKED" ';
        }
        $out .= '/><label for="stop">';
        if ($this->useimages) {
            $out .= '<img src="images/stop.gif" width="35" height="33" border="0" alt="'. _("STOP") . '" align="middle" /> ';
        } else {
            $out .= "<strong>"._("STOP").":</strong> ";
        }
        $out .= _("If this rule matches, do not check any rules after it."). '</label>'.
            $this->section_end();

        /* --------------------- buttons ----------------------- */

        $out .= $this->submit_buttons().
            '</div></td></tr>'.
            $this->all_sections_end().
            $this->table_footer() . '</form>';
        
        return $out;
    }

    /**
     * Process HTML submission from namespace $ns (usually $_POST),
     * and put the resulting rule structure in $this->rule class variable.
     *
     * @param array $ns
     * @param array $rule
     * @return void
     */
    function process_input(&$ns, $unused = false) {
        global $startitems;
        if(isset($ns['intermediate_action']['spamrule_switch_to_advanced'])) {
            // Just switched to advanced.
            $this->spamrule_advanced = true;
            $this->rule['advanced'] = 1;
        } elseif(isset($ns['spamrule_advanced'])) {
            $this->spamrule_advanced = true;
            $this->rule['advanced'] = 1;
        } elseif (isset($edit) && isset($this->rule['advanced'])) {
            $this->spamrule_advanced = true; // FIXME
            $this->rule['advanced'] = 1;
        } else {
            $this->spamrule_advanced = false;
            $this->rule['advanced'] = 0;
        }
        
        /* Spam Rule variables */
        /* If we need to get spamrule RBLs from LDAP, then do so now. */
        
        if(isset($_SESSION['spamrule_rbls'])) {
            $spamrule_rbls = $_SESSION['spamrule_rbls'];
        } elseif(isset($this->settings['spamrule_tests_ldap']) && $this->settings['spamrule_tests_ldap'] == true &&
        !isset($_SESSION['spamrule_rbls'])) {
            include_once(SM_PATH . 'plugins/avelsieve/include/spamrule.inc.php');
            $spamrule_rbls = avelsieve_askldapforrbls();
            $_SESSION['spamrule_rbls'] = $spamrule_rbls;
        }
        
        if(isset($ns['tests'])) {
            $tests = $ns['tests'];
        } elseif (isset($edit) && isset($this->rule['tests'])) {
            $tests = $this->rule['tests'];
        } else {
            $tests = array_keys($this->settings['spamrule_tests']);
        }
        
        if(isset($ns['score'])) {
            $score = $ns['score'];
        } elseif (isset($edit) && isset($this->rule['score'])) {
            $score = $this->rule['score'];
        } else {
            $score = $this->settings['spamrule_score_default'];
        }
        
        /* Whitelist number of items to display */
        if(isset($ns['whitelistitems'])) {
            $this->whitelistitems = $ns['whitelistitems'];
        } elseif (isset($edit) && isset($this->rule['whitelist'])) {
            $this->whitelistitems = sizeof($this->rule['whitelist']) + 1;
        } else {
            $this->whitelistitems = $startitems;
        }
        if(isset($ns['whitelist_add'])) {
            $this->whitelistitems++;
        }
        
        /* The actual whitelist */
        if(isset($ns['whitelist_add']) || isset($ns['apply'])) {
            $j=0;
            for($i=0; $i< $this->whitelistitems; $i++) {
                if(!empty($ns['cond'][$i]['headermatch'])) {
                    $whitelist[$j]['header'] = $ns['cond'][$i]['header'];
                    $whitelist[$j]['matchtype'] = $ns['cond'][$i]['matchtype'];
                    $whitelist[$j]['headermatch'] = $ns['cond'][$i]['headermatch'];
                    $j++;
                }
            }
        } elseif (isset($edit) && isset($this->rule['whitelist'])) {
            $whitelist = $this->rule['whitelist'];
        }
        
        if(isset($ns['action']))  {
            $action = $ns['action'];
        } elseif (isset($edit) && isset($this->rule['action'])) {
            $action = $this->rule['action'];
        } else {
            $action = $this->settings['spamrule_action_default'];
        }
        
        
        if(isset($ns['stop']))  {
            $stop = 1;
        } elseif (isset($edit) && isset($this->rule['stop']) && !isset($ns['apply'])) {
            $stop = $this->rule['stop'];
        } else {
            $stop = 1;
        }
        
        /* After all that story, now overwrite the variables of the rule. */
        $this->rule['type'] = 10;
        $this->rule['tests'] = $tests;
        $this->rule['score'] = $score;
        $this->rule['action'] = $action;
        if(isset($whitelist)) {
            $this->rule['whitelist'] = $whitelist;
        }
        if($this->spamrule_advanced) {
            $this->rule['advanced'] = 1;
        }
        if(isset($stop) && $stop) {
            $this->rule['stop'] = $stop;
        }
    }
}

