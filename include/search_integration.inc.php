<?php
/**
 * User-friendly interface to SIEVE server-side mail filtering.
 * Plugin for Squirrelmail 1.4+
 *
 * Licensed under the GNU GPL. For full terms see the file COPYING that came
 * with the Squirrelmail distribution.
 *
 * @version $Id: search_integration.inc.php 1020 2009-05-13 14:10:13Z avel $
 * @author Alexandros Vellis <avel@users.sourceforge.net>
 * @copyright 2004-2007 The SquirrelMail Project Team, Alexandros Vellis
 * @package plugins
 * @subpackage avelsieve
 */

/** Includes */
include_once(SM_PATH . 'plugins/avelsieve/include/managesieve_wrapper.inc.php');
include_once(SM_PATH . 'plugins/avelsieve/include/html_main.inc.php');

/**
 * The main search-integration routine.
 *
 * @uses asearch_to_avelsieve()
 * @return void
 */
function avelsieve_search_integration_do() {
    global $mailbox_array, $biop_array, $unop_array, $where_array, $what_array,
        $exclude, $color, $compose_new_win, $javascript_on;
    
    $rule = asearch_to_avelsieve($mailbox_array, $biop_array, $unop_array, $where_array, $what_array, $exclude, $info);

    if(!empty($rule) && isset($_GET['submit'])) {
        bindtextdomain('avelsieve', SM_PATH . 'plugins/avelsieve/locale');
        textdomain ('avelsieve');
        
        $url = '../plugins/avelsieve/edit.php?addnew=1&amp;type=1&amp;serialized_rule='.rawurlencode(serialize($rule));
    
        if(!$compose_new_win) {
            /* For non-popup page we need to come back to the search results. */
            /* FIXME */
        }
        if($compose_new_win == '1') {
            $url .= '&amp;popup=1';
        }

        echo '<table border="0" width="100%" cellpadding="0" cellspacing="0">'.
            avelsieve_html::section_start( _("Create Filter") );
        echo '<div align="center" style="text-align:center; font-size:120%; padding: 0.3em;">';
        
        if($compose_new_win == '1') {
            if($javascript_on) {
                echo "<a href=\"javascript:void(0)\" onclick=\"comp_in_new('$url')\" ".
                    'style="font-size:120%"><strong>'. _("Create Filter") . '</strong></a> ';
            } else {
                echo '<a href="'.$url.'" style="font-size: 120%" target="_blank">'.
                    '<strong>'. _("Create Filter") . '</strong></a> ';
            }
        } else {
            echo '<a href="'.$url.'" style="font-size: 120%"">'.
                '<strong>'. _("Create Filter") . '</strong></a> ';
        }
        echo _("(Creates a new server-side filtering rule, based on the above criteria)") . '</a>';

        if(isset($info['features_disabled'])) {
            echo '<br/><em>' .
                _("Notice: The following criteria cannot be expressed as server-side filtering rules:") . '</em>'.
                '<ul style="margin: 3px;">';

            foreach($info['disabled_criteria'] as $no) {
                $mailbox_array_tmp = array($mailbox_array[$no]);
                if(isset($biop_array[$no])) {
                    $biop_array_tmp = array($biop_array[$no]);
                } else {
                    $biop_array_tmp = array();
                }
                $unop_array_tmp = array($unop_array[$no]);
                $where_array_tmp = array($where_array[$no]);
                $what_array_tmp = array($what_array[$no]);
                if(isset($exclude_array[$no])) {
                    $exclude_array_tmp = array($exclude_array[$no]);
                } else {
                    $exclude_array_tmp = array();
                }
                if(isset($sub_array[$no])) {
                $sub_array_tmp = array($sub_array[$no]);
                    $sub_array_tmp = array($sub_array[$no]);
                } else {
                    $sub_array_tmp = array();
                }

                bindtextdomain('squirrelmail', SM_PATH . 'locale');
                textdomain ('squirrelmail');
                echo '<li>('. ($no+1). ') &quot;' .
                    asearch_get_query_display($color, $mailbox_array_tmp, $biop_array_tmp, $unop_array_tmp,
                    $where_array_tmp, $what_array_tmp, $exclude_array_tmp, $sub_array_tmp);
                bindtextdomain('avelsieve', SM_PATH . 'plugins/avelsieve/locale');
                textdomain ('avelsieve');
            
                if(isset($info['disabled_criteria_reasons'][$no])) {
                    echo '&quot; - ' . _("Reason:") . ' ' . $info['disabled_criteria_reasons'][$no];
                }
                echo '</li>';
            }
            echo '</ul>';
               
            /* Additional Notices or information */
            if(isset($info['notice'])) {
                foreach($info['notice'] as $notice) {
                    echo $notice . '</br>';
                }
            }
        }
        echo '</div>';
        echo avelsieve_html::section_end();
        echo '</table>';

        bindtextdomain('squirrelmail', SM_PATH . 'locale');
        textdomain ('squirrelmail');
    }
}

/**
 * Map the query data from an advanced search to an avelsieve filter.
 *
 * @param array $mailbox_array
 * @param array $biop_array
 * @param array $unop_array
 * @param array $where_array
 * @param array $what_array
 * @param array $exclude 
 * @param array $info Some additional information that can be passed back to
 *  the caller. For instance, if $info['features_disabled'] exists, then not
 *  all search criteria could be made into Sieve rules.
 * @return array A rule as an avelsieve rule structure, with the 'cond' array
 *  filled in, and possibly the 'condition' string filled in as well
 *  ('and'/'or').
 */
function asearch_to_avelsieve(&$mailbox_array, &$biop_array, &$unop_array, &$where_array, &$what_array, &$exclude, &$info) {
    global $sieve, $sieve_capabilities;
    if(!isset($sieve_capabilities)) {
        sqgetGlobalVar('sieve_capabilities', $sieve_capabilities, SQ_SESSION);
        if(!isset($sieve_capabilities)) {
            // Have to connect to timsieved to get the capabilities. Luckily
            // this will only happen once.
            avelsieve_initialize($sieve);
        }
    }
    $r = array();
    $r['cond'] = array();
    $info = array();

    foreach($where_array as $no=>$w) {
        if(!isset($idx)) {
            $idx = 0;
        }
        if($no == 0 || !isset($exclude[$no])) {
            switch($w) {
                /* ----------- Header match ---------- */
                case 'FROM':
                case 'SUBJECT':
                case 'TO':
                case 'CC':
                case 'BCC':
                    $r['cond'][$idx]['type'] = 'header';
                    $r['cond'][$idx]['header'] = ucfirst(strtolower($w));
                    $r['cond'][$idx]['matchtype'] = 'contains';
                    $r['cond'][$idx]['headermatch'] = $what_array[$no];
                    break;

                /* ----------- Header match - Specialized "any" Header ---------- */
                case 'HEADER':
                    $r['cond'][$idx]['type'] = 'header';
                    $r['cond'][$idx]['matchtype'] = 'contains';

                    preg_match('/^([^:]+):(.*)$/', $what_array[$no], $w_parts);

                    if (count($w_parts) == 3) {
                        /* This canonicalization will better have to be dealt
                         * with inside avelsieve itself */
                        $hdr = str_replace(':', '', ucfirst(strtolower($w_parts[1])));
                        if(($pos = strpos($hdr, '-')) !== false) {
                           $hdr[$pos+1] = strtoupper($hdr[$pos+1]);
                        }
                        $r['cond'][$idx]['header'] = $hdr;
                        $r['cond'][$idx]['headermatch'] = $w_parts[2];
                        unset($w_parts);
                    }
                    break;
                
                /* ----------- Header OR Body ---------- */
                case 'TEXT':
                    $r['cond'][$idx]['type'] = 'header';
                    $r['cond'][$idx]['header'] = 'toorcc';
                    $r['cond'][$idx]['matchtype'] = 'contains';
                    $r['cond'][$idx]['headermatch'] = $what_array[$no];

                    $idx++;
                    $r['cond'][$idx]['type'] = 'header';
                    $r['cond'][$idx]['header'] = 'From';
                    $r['cond'][$idx]['matchtype'] = 'contains';
                    $r['cond'][$idx]['headermatch'] = $what_array[$no];
                    
                    $idx++;
                    $r['cond'][$idx]['type'] = 'header';
                    $r['cond'][$idx]['header'] = 'Subject';
                    $r['cond'][$idx]['matchtype'] = 'contains';
                    $r['cond'][$idx]['headermatch'] = $what_array[$no];
                    
                    if(avelsieve_capability_exists('body')) {
                        $idx++;
                        $r['cond'][$idx]['type'] = 'body';
                        $r['cond'][$idx]['matchtype'] = 'contains';
                        $r['cond'][$idx]['bodymatch'] = $what_array[$no];
                        $r['condition'] = 'or';
                    } else {
                        $idx--;
                        $info['features_disabled'] = true; 
                        $info['disabled_criteria'][] = $no;
                        $info['disabled_criteria_reasons'][$no] = _("The Body extension is not supported in this server.");
                    }
                    $info['notice'][] = _("Note that Only From:, To:, Cc: and Subject: headers will be checked in the server filter.");
                    break;
                
                /* ----------- Size ---------- */
                case 'LARGER':
                case 'SMALLER':
                    $r['cond'][$idx]['type'] = 'size';
                    if($w == 'LARGER') {
                        $r['cond'][$idx]['sizerel'] = 'bigger';
                    } elseif($w == 'SMALLER') {
                        $r['cond'][$idx]['sizerel'] = 'smaller';
                    }
                    $r['cond'][$idx]['sizerel'] = '';
                    $r['cond'][$idx]['sizeamount'] = floor($what_array[$no] / 1024);
                    $r['cond'][$idx]['sizeunit'] = 'K';
                    break;


                /* ----------- Body ---------- */
                case 'BODY':
                    if(avelsieve_capability_exists('body')) {
                        $r['cond'][$idx]['type'] = 'body';
                        $r['cond'][$idx]['matchtype'] = 'contains';
                        $r['cond'][$idx]['bodymatch'] = $what_array[$no];
                    } else {
                        $idx--;
                        $info['features_disabled'] = true; 
                        $info['disabled_criteria'][] = $no;
                        $info['disabled_criteria_reasons'][$no] = _("The Body extension is not supported in this server.");
                    }
                    break;
                
                /* ----------- All ---------- */
                case 'ALL':
                    $r['cond'][$idx]['type'] = 'all';
                    break;
                
                /* ----------- Rest, unsupported + catch ---------- */
                case 'ANSWERED':
                case 'DELETED':
                case 'DRAFT':
                case 'FLAGGED':
                case 'KEYWORD':
                case 'NEW':
                case 'OLD':
                case 'RECENT':
                case 'SEEN':
                case 'UNANSWERED':
                case 'UNDELETED':
                case 'UNDRAFT':
                case 'UNFLAGGED':
                case 'UNKEYWORD':
                case 'UNSEEN':

                case 'BEFORE':
                case 'ON':
                case 'SENTBEFORE':
                case 'SENTON':
                case 'SENTSINCE':
                case 'SINCE':

                case 'UID':
                default:
                    /* Unsupported; stay at same index */
                    $info['features_disabled'] = true; 
                    $info['disabled_criteria'][] = $no;
                    $info['disabled_criteria_reasons'][$no] = _("These search expressions are not applicable during message delivery.");
                    $idx--;
                    break;

            }
        }
        $idx++;
    }
    if(sizeof($r['cond']) > 1 && isset($biop_array[1])) {
        switch($biop_array[1]){
            case 'ALL':
                $r['condition'] = 'and';
                break;
            case 'OR':
                $r['condition'] = 'or';
                break;
        }
    } elseif(sizeof($r['cond']) == 0) {
        unset($r['cond']);
    }
    return $r;
}

