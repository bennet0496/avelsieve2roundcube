<?php
/**
 * User-friendly interface to SIEVE server-side mail filtering.
 * Plugin for Squirrelmail 1.4+
 *
 * Licensed under the GNU GPL. For full terms see the file COPYING that came
 * with the Squirrelmail distribution.
 *
 * Also view plugins/README.plugins for more information.
 *
 * @version $Id: setup.php 1055 2009-05-29 07:58:18Z avel $
 * @author Alexandros Vellis <avel@users.sourceforge.net>
 * @copyright 2004-2009 The SquirrelMail Project Team, Alexandros Vellis
 * @package plugins
 * @subpackage avelsieve
 */
   
/** Include Configuration */
include_once(SM_PATH . 'plugins/avelsieve/config/config.php');

/**
 * Register Plugin
 * @return void
 */
function squirrelmail_plugin_init_avelsieve() {
    global $squirrelmail_plugin_hooks;
    $squirrelmail_plugin_hooks['optpage_register_block']['avelsieve'] = 'avelsieve_optpage_register_block';
    $squirrelmail_plugin_hooks['menuline']['avelsieve'] = 'avelsieve_menuline';
    $squirrelmail_plugin_hooks['read_body_header']['avelsieve'] = 'avelsieve_commands_menu';
    $squirrelmail_plugin_hooks['search_after_form']['avelsieve'] = 'avelsieve_search_integration';
    $squirrelmail_plugin_hooks['configtest']['avelsieve'] = 'avelsieve_configtest';
    
    $squirrelmail_plugin_hooks['right_main_after_header']['avelsieve'] = 'avelsieve_right_main';
    
    $squirrelmail_plugin_hooks['javascript_libs_register']['avelsieve'] = 'avelsieve_register_jslibs';

    $squirrelmail_plugin_hooks['generic_header']['avelsieve'] = 'avelsieve_generic_header';

    $squirrelmail_plugin_hooks['special_mailbox']['avelsieve'] = 'junkmail_markspecial';
    $squirrelmail_plugin_hooks['folders_bottom']['avelsieve'] = 'junkmail_folders';
    
    $squirrelmail_plugin_hooks['template_construct_page_header.tpl']['avelsieve'] =  'avelsieve_menuline_devel';
}

/**
 * Register options block page
 * @return void
 */
function avelsieve_optpage_register_block() {
    global $optpage_blocks, $avelsieve_enable_rules;
    if (defined('SM_PATH')) {
        bindtextdomain ('avelsieve', SM_PATH . 'plugins/avelsieve/locale');
    } else {
        bindtextdomain ('avelsieve', '../plugins/avelsieve/locale');
    }
    textdomain ('avelsieve');

    $optpage_blocks[] = array(
        'name' => _("Message Filters"),
        'url'  => '../plugins/avelsieve/table.php',
        'desc' => _("Server-Side mail filtering enables you to add criteria in order to automatically forward, delete or place a given message into a folder."),
        'js'   => false
    );

    if(in_array(11, $avelsieve_enable_rules)) {
          $optpage_blocks[] = array(
            'name' => _("Junk Mail Options"),
            'url'  => '../plugins/avelsieve/edit.php?type=11',
            'desc' => _("The Junk Mail Filter gathers all unwanted SPAM / Junk messages in your Junk folder."),
            'js'   => false
        );
    }

    if (defined('SM_PATH')) {
        bindtextdomain('squirrelmail', SM_PATH . 'locale');
    } else {
        bindtextdomain ('squirrelmail', '../locale');
    }
    textdomain('squirrelmail');
}

/**
 * Display menuline link (Squirrelmail DEVEL 1.5 version)
 * @return void
 */
function avelsieve_menuline_devel() {
    global $avelsieveheaderlink;
    if($avelsieveheaderlink) {
        global $oTemplate, $nbsp;

        bindtextdomain('avelsieve', SM_PATH . 'plugins/avelsieve/locale');
        textdomain ('avelsieve');
        $output = makeInternalLink('plugins/avelsieve/table.php', _("Filters")) . '&nbsp;&nbsp;';
        
        bindtextdomain('squirrelmail', SM_PATH . 'locale');
        textdomain ('squirrelmail');

        return array('menuline' => $output);
    }
}
   
/**
 * Display menuline link
 * @return void
 */
function avelsieve_menuline() {
    global $avelsieveheaderlink;

    if($avelsieveheaderlink) {
        bindtextdomain('avelsieve', SM_PATH . 'plugins/avelsieve/locale');
        textdomain ('avelsieve');
        
        displayInternalLink('plugins/avelsieve/table.php',_("Filters"));
        echo "&nbsp;&nbsp;\n";

        bindtextdomain('squirrelmail', SM_PATH . 'locale');
        textdomain ('squirrelmail');
    }
}    

/**
 * While showing a message, display filter commands.
 * @return void
 * @see avelsieve_commands_menu_do()
 */
function avelsieve_commands_menu() {
    include_once(SM_PATH . 'plugins/avelsieve/include/message_commands.inc.php');
    avelsieve_commands_menu_do();
}

/**
 * Integration with Advanced Search.
 * @return void
 * @see avelsieve_search_integration_do()
 */
function avelsieve_search_integration() {
    global $squirrelmail_plugin_hooks, $SQM_INTERNAL_VERSION, $version;

    if(($SQM_INTERNAL_VERSION[0] == 1 && $SQM_INTERNAL_VERSION[1] >= 5)) {
        include_once(SM_PATH . 'plugins/avelsieve/include/search_integration.inc.php');
        avelsieve_search_integration_do();
    }
}

/**
 * Function to insert stuff in HTML head section, mainly javascripts.
 *
 * Used at the moment in avelsieve's edit.php.
 */
function avelsieve_generic_header() {
    global $PHP_SELF, $base_uri, $javascript_on;
    if(!$javascript_on) return;

    if(stristr(basename($PHP_SELF), 'edit.php')) {
        // Edit page (edit.php)
        $js = array('prototype.js', 'avelsieve_common.js', 'avelsieve_edit.js', 'prototype-base-extensions.js', 'prototype-date-extensions.js', 'datepicker.js');
        echo "\n".'<link rel="stylesheet" type="text/css" href="'.$base_uri.'plugins/avelsieve/styles/datepicker.css"></link>' . "\n";

    } elseif(stristr(basename($PHP_SELF), 'table.php')) {
        // Table Page (table.php)
        $js = array('prototype.js', 'avelsieve_common.js', 'avelsieve_table.js');
        echo '<style type="text/css">'.avelsieve_css_styles().'</style>';
    }
    if(isset($js)) {
        foreach($js as $j) {
            echo "\n".'<script language="JavaScript" type="text/javascript" src="'.$base_uri.'plugins/avelsieve/javascripts/'.$j.'"></script>';
        }
    }
}

/**
 * Junk Mail functionality: This marks a junk folder as "special" to Squirrelmail.
 *
 * @param string $box
 * @return mixed Return true if this is the special Junk folder.
 */
function junkmail_markspecial($box) {
    global $avelsieve_enable_rules, $delimiter;
    if(!in_array(11,$avelsieve_enable_rules)) return;

    if($box == 'Junk' || $box == 'INBOX.Junk') {
        return true;
    }
    $parts = preg_split('/'.str_replace('.', '\.',$delimiter).'/', $box);
    if(sizeof($parts) > 1 && ($parts[0] == 'Junk' || $parts[1] == 'Junk')) {
        return true;
    }
}

/**
 * Call functions that can be called in Message listing (src/right_main.php).
 *
 * 1) Junk Mail functionality: Link to options from Junk folder.
 * 2) Vacation Rule reminder, from INBOX folder.
 *
 * @see avelsieve_right_main_do()
 */
function avelsieve_right_main() {
    include_once(SM_PATH . 'plugins/avelsieve/include/right_main.inc.php');
    avelsieve_right_main_do();
}

/**
 * Junk Mail functionality: Link to options, from Folders Page (folders.php).
 */
function junkmail_folders() {
    global $avelsieve_enable_rules, $mailbox;
    if(!in_array(11,$avelsieve_enable_rules)) return;
    
    include_once(SM_PATH . 'plugins/avelsieve/include/junkmail.inc.php');
    junkmail_folders_do();
}

/**
 * Configuration Test
 * @return boolean
 */
function avelsieve_configtest() {
    include_once(SM_PATH . 'plugins/avelsieve/include/configtest.inc.php');
    return avelsieve_configtest_do();
}

/**
 * Register the main avelsieve scripts with the javascript_libs plugin.
 */
function avelsieve_register_jslibs() {
    global $plugins;
    if(in_array('javascript_libs', $plugins)) {
        javascript_libs_register('plugins/avelsieve/table.php', array('prototype-1.6.0.3/prototype.js', 'scriptaculous-1.8.1/effects.js'));
        javascript_libs_register('plugins/avelsieve/edit.php', array('prototype-1.6.0.3/prototype.js', 'scriptaculous-1.8.1/effects.js'));
    }
}

/**
 * Return information about plugin.
 * @return array
 */
function avelsieve_info() {
   return array(
       'english_name' => 'Avelsieve - Sieve Mail Filters',
       'version' => '1.9.9',
       'summary' => 'An easy user interface for creating Sieve scripts on a Sieve-compliant (RFC 5028) server.',
       'details' => 'Avelsieve - Sieve Mail Filters for Squirrelmail - allows editing of Sieve mail filtering scripts on a Sieve-compliant (RFC 5028) server.'
   );
}

/**
 * Return plugin version.
 * @return string
 */
function avelsieve_version() {
   $info = avelsieve_info();
   return $info['version'];
}

