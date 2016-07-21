<?php
/**
 * User-friendly interface to SIEVE server-side mail filtering.
 * Plugin for Squirrelmail 1.4+
 *
 * Licensed under the GNU GPL. For full terms see the file COPYING that came
 * with the Squirrelmail distribution.
 *
 * This file contains functions for the per-message commands that appear while
 * viewing a message.
 *
 * @version $Id: message_commands.inc.php 1020 2009-05-13 14:10:13Z avel $
 * @author Alexandros Vellis <avel@users.sourceforge.net>
 * @copyright 2004-2007 The SquirrelMail Project Team, Alexandros Vellis
 * @package plugins
 * @subpackage avelsieve
 */

/** Includes */
include_once(SM_PATH . 'functions/identity.php');

/**
 * Display available filtering commands for current message.
 */
function avelsieve_commands_menu_do() {
    global $passed_id, $passed_ent_id, $color, $mailbox,
           $message, $compose_new_win, $javascript_on;
        
    bindtextdomain('avelsieve', SM_PATH . 'plugins/avelsieve/locale');
    textdomain ('avelsieve');
    
    $output = array();

    $filtercmds = array(
        'auto' => array(
            'algorithm' => 'auto',
            'desc' => _("Automatically")
        ),
        'sender' => array(
            'algorithm' => 'address',
            'desc' => _("Sender")
        ),
        'from' => array(
            'algorithm' => 'address',
            'desc' => _("From")
        ),
        'to' => array(
            'algorithm' => 'address',
            'desc' => _("To")
        ),
        'subject' => array(
            'algorithm' => 'header',
            'desc' => _("Subject")
        ) 
        /*
        'priority' => array(
            'algorithm' => 'header',
            'desc' => _("Priority")
        )
        */
    );
    
    $hdr = &$message->rfc822_header;

    /* Have identities handy to check for our email addresses in automatic
     * algorithm mode */
    $idents = get_identities();
    $myemails = array();
    foreach($idents as $identity) {
        $myemails[] = strtolower($identity['email_address']);
    }

    foreach($filtercmds as $c => $i) {
        $url = '../plugins/avelsieve/edit.php?addnew=1&amp;type=1';
        switch($i['algorithm']) {
        case 'address':
            if(isset($hdr->$c) && !empty($hdr->$c)) {
                if(is_array($hdr->$c)) {
                    for($j=0; $j<sizeof($hdr->$c); $j++) {
                        $url .= '&amp;cond['.$j.'][type]=address';
                        $url .= '&amp;cond['.$j.'][address]='.ucfirst($c);
                        $url .= '&amp;cond['.$j.'][matchtype]=contains';
                        $url .= '&amp;cond['.$j.'][addressmatch]='.urlencode( $hdr->{$c}[$j]->mailbox.'@'.$hdr->{$c}[$j]->host);
                    }
                } else {
                    $j=0;
                    $url .= '&amp;cond['.$j.'][type]=address';
                    $url .= '&amp;cond['.$j.'][address]='.ucfirst($c);
                    $url .= '&amp;cond['.$j.'][matchtype]=contains';
                    $url .= '&amp;cond['.$j.'][addressmatch]='.urlencode( $hdr->{$c}->mailbox.'@'.$hdr->{$c}->host);
                }
            } else {
                unset($url);
            }
            break;

        case 'header':
            if(isset($hdr->$c) && !empty($hdr->$c)) {
                $j=0;
                $url .= '&amp;cond['.$j.'][type]=header';
                $url .= '&amp;cond['.$j.'][header]='.ucfirst($c);
                $url .= '&amp;cond['.$j.'][matchtype]=contains';
                $url .= '&amp;cond['.$j.'][headermatch]='.rawurlencode(decodeHeader($hdr->$c, false, false));
                /* TODO: Probably use $utfdecode = true instead of false in the
                 * above function call of decodeHeader() (second argument). */
            }
            break;

        case 'auto':
            if(isset($hdr->mlist['id']) && isset($hdr->mlist['id']['href'])) {
                /* List-Id: (href) */
                $url .= '&amp;cond[0][type]=header';
                $url .= '&amp;cond[0][header]=List-Id'.
                        '&amp;cond[0][matchtype]=contains'.
                        '&amp;cond[0][headermatch]='.rawurlencode( $hdr->mlist['id']['href'] );

            } elseif(isset($hdr->mlist['id']) && isset($hdr->mlist['id']['mailto'])) {
                /* List-Id: (mailto) */
                $url .= '&amp;cond[0][type]=header';
                $url .= '&amp;cond[0][header]=List-Id'.
                        '&amp;cond[0][matchtype]=contains'.
                        '&amp;cond[0][headermatch]='.rawurlencode( $hdr->mlist['id']['mailto'] );

            } elseif(isset($hdr->sender) && !empty($hdr->sender)) {
                /* Sender: */
                $url .= '&amp;cond[0][type]=address';
                $url .= '&amp;cond[0][address]=Sender'.
                        '&amp;cond[0][matchtype]=contains'.
                        '&amp;cond[0][addressmatch]='.rawurlencode($hdr->sender->mailbox.'@'.$hdr->sender->host);
            
            } else {
                $j = 0;
                /* Special check for To: Header */
                /* FIXME - probably this is not such a good idea. */
                if(isset($hdr->to) && !empty($hdr->to)) {
                    /* To:, not including one of my identities*/
                    for($k=0; $k<sizeof($hdr->to); $k++) {
                        $tempurl = '';
                        if(!in_array($hdr->to[$k]->mailbox.'@'.$hdr->to[$k]->host,$myemails)) {
                            $tempurl .=
                                '&amp;cond['.$j.'][type]=address'.
                                '&amp;cond['.$j.'][address]=toorcc'.
                                '&amp;cond['.$j.'][matchtype]=contains'.
                                '&amp;cond['.$j.'][addressmatch]='.rawurlencode($hdr->to[$k]->mailbox.'@'.$hdr->to[$k]->host);
                            $j++;
                        }
                    }
                }
                if($j > 0) {
                        $url .= $tempurl;
                } else {
                    /* The above method failed, continue with one of these: */

                    if(isset($hdr->from) && !empty($hdr->from)) {
                        /* From: */
                        for($k=0; $k<sizeof($hdr->from); $k++) {
                            $url .= '&amp;cond[0][type]=address'.
                                '&amp;cond[0][address]=From'.
                                '&amp;cond[0][matchtype]=contains'.
                                '&amp;cond[0][addressmatch]='.rawurlencode($hdr->from[$k]->mailbox.'@'.$hdr->from[$k]->host);
                        }
                    
                    } elseif(isset($hdr->subject) && !empty($hdr->subject)) {
                        /* Subject */
                        $url .= '&amp;cond[0][type]=header'.
                                '&amp;cond[0][header]=Subject'.
                                '&amp;cond[0][matchtype]=contains'.
                                '&amp;cond[0][headermatch]='.rawurlencode($hdr->subject);
                    }
                }
            }
            break;
        }
        if(isset($url)) {
            if(!$compose_new_win) {
                /* For non-popup page we need this to come back here. */
                $url .= '&amp;passed_id='.$passed_id.'&amp;mailbox='.urlencode($mailbox).
                    (isset($passed_ent_id)?'&amp;passed_ent_id='.$passed_ent_id:'');
            }
            
               if ($compose_new_win == '1') {
                $url .= '&amp;popup=1';
            }
            if ($compose_new_win == '1') {
                if($javascript_on) {
                       $output[] = "<a href=\"javascript:void(0)\" onclick=\"comp_in_new('$url')\">".$i['desc'].'</a>';
                } else {
                       $output[] = '<a href="'.$url.'" target="_blank">'.$i['desc'].'</a>';
                }
            } else {
                   $output[] = '<a href="'.$url.'">'.$i['desc'].'</a>';
               }
        }
        unset($url);
    }

          
    if (count($output) > 0) {
        echo '<tr>';
        echo html_tag('td', '<b>' . _("Create Filter") . ':&nbsp;&nbsp;</b>',
                      'right', '', 'valign="middle" width="20%"') . "\n";
        echo html_tag('td', '<small>' . implode('&nbsp;|&nbsp;', $output) . '</small>',
                      'left', $color[0], 'valign="middle" width="80%"') . "\n";
        echo '</tr>';
    }
    
    bindtextdomain('squirrelmail', SM_PATH . 'locale');
    textdomain ('squirrelmail');

}

