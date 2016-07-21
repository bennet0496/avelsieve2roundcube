<?php
/**
 * User-friendly interface to SIEVE server-side mail filtering.
 * Plugin for Squirrelmail 1.4+
 *
 * Licensed under the GNU GPL. For full terms see the file COPYING that came
 * with the Squirrelmail distribution.
 *
 * @version $Id: DO_Sieve.class.php 1054 2009-05-28 13:53:23Z avel $
 * @author Alexandros Vellis <avel@users.sourceforge.net>
 * @copyright 2004-2007 Alexandros Vellis
 * @package plugins
 * @subpackage avelsieve
 */

/** Includes */
include_once(SM_PATH . 'plugins/avelsieve/include/support.inc.php');
include_once(SM_PATH . 'plugins/avelsieve/config/config.php');
    
class DO_Sieve {
    var $capabilities;
    var $rules;
    var $sieve;
    
    /*
     * Condition kinds
     * From avelsieve 1.9.8, a condition is one of these kinds:
     * - 'message' => a condition that has to do with the mail message being processed
     * - 'datetime' => a condition that has to do with the current date / time
     * - 'all' => a condition that always returns true. Equivalent to sieve's true test
     *
     * In the future, the following conditions can be implemented:
     * - 'variable' => a condition that has to do with a test on a Sieve variable
     * - 'environment' => a condition that has to do with the mail environment
     *
     * @var array
     * @access public
     */
    public $condition_kinds;


    /*
    function init()
    function listscripts()
    function load()
    function save()
    function delete()
    */

    /**
     * Constructor
     */
    function DO_Sieve() {
        if(AVELSIEVE_DEBUG == 1) {
            $this->sieveDebug = true;
        } else {
            $this->sieveDebug = false;
        }

        $this->condition_kinds = array(
            'message' => array(
                'desc' => _("Message"),
            ),
            'datetime' => array(
                'desc' => _("Current Date / Time"),
                'capability' => 'date',
            ),
            'all' => array(
                'desc' => _("Always"),
            ),
        );
        return;
    }


    /**
    * Check if avelsieve capability exists.
    *
    * avelsieve capability is defined as SIEVE capability, NOT'd with
    * $disable_avelsieve_capabilities from configuration file.
    *
    * $disable_avelsieve_capabilities specifies capabilities to disable. If you
    * would like to force avelsieve not to display certain features, even though
    * there _is_ a capability for them by Cyrus/timsieved, you should specify
    * these here. For instance, if you would like to disable the notify extension,
    * even though timsieved advertises it, you should add 'notify' in this array:
    * $force_disable_avelsieve_capability = array("notify");. This will still
    * leave the defined feature on, and if the user can upload her own scripts
    * then she can use that feature; this option just disables the GUI of it.
    *
    * @param $cap capability to check for
    * @return boolean true if capability exists, false if it does not exist
    */
    function capability_exists($cap) {
        global $disable_avelsieve_capabilities;
        if(empty($this->capabilities)) {
            /* Abnormal start of a backend. We need to call init() explicitly
             * in order to get capabilities. */
            $this->init();
        }

        if(AVELSIEVE_DEBUG >= 3) return true;

        if(array_key_exists($cap, $this->capabilities) && !in_array($cap, $disable_avelsieve_capabilities)) {
            return true;
        }
        return false;
    }
    
    /**
     * Gets the applicable condition kinds according to current capabilities
     *
     * @return array
     */
    protected function _get_active_condition_kinds() {
        $out = array();
        foreach($this->condition_kinds as $k=>$v) {
            if(AVELSIEVE_DEBUG == 3) {
                $out[$k] = $v['desc'];
                continue;
            }

            if(!isset($v['capability'])) {
                $out[$k] = $v['desc'];
                continue;
            }
            if($this->capability_exists($v['capability'])) {
                $out[$k] = $v['desc'];
                continue;
            }
        }
        return $out;
    }
    
    /**
    * Encode script from user's charset to UTF-8.
    *
    * @param string $script
    * @return string
    */
    function encode_script($script) {
        global $languages, $squirrelmail_language, $default_charset;
    
        /* change $default_charset to user's charset */
        set_my_charset();
    
        if(strtolower($default_charset) == 'utf-8') {
            // No need to convert.
            return $script;
        
        } elseif(function_exists('mb_convert_encoding') && function_exists('sqimap_mb_convert_encoding')) {
            // sqimap_mb_convert_encoding() returns '' if mb_convert_encoding() doesn't exist!
            $utf8_s = sqimap_mb_convert_encoding($script, 'UTF-8', $default_charset, $default_charset);
            if(empty($utf8_s)) {
                return $script;
            } else {
                return $utf8_s;
            }
    
        } elseif(function_exists('mb_convert_encoding')) {
            // Squirrelmail 1.4.0 ?
    
            if ( stristr($default_charset, 'iso-8859-') ||
            stristr($default_charset, 'utf-8') || 
            stristr($default_charset, 'iso-2022-jp') ) {
                return mb_convert_encoding($script, "UTF-8", $default_charset);
            }
    
        } elseif(function_exists('recode_string')) {
            return recode_string("$default_charset..UTF-8", $script);
    
        } elseif(function_exists('iconv')) {
            return iconv($default_charset, 'UTF-8', $script);
        }
    
        return $script;
    }
    
    
    /**
    * Decode script from UTF8 to user's charset.
    *
    * @param string $script
    * @return string
    */
    function decode_script($script) {
    
        global $languages, $squirrelmail_language, $default_charset;
    
        /* change $default_charset to user's charset (THANKS Tomas) */
        set_my_charset();
    
        if(strtolower($default_charset) == 'utf-8') {
            // No need to convert.
            return $script;
        
        } elseif(function_exists('mb_convert_encoding') && function_exists('sqimap_mb_convert_encoding')) {
            // sqimap_mb_convert_encoding() returns '' if mb_convert_encoding() doesn't exist!
            $un_utf8_s = sqimap_mb_convert_encoding($script, $default_charset, "UTF-8", $default_charset);
            if(empty($un_utf8_s)) {
                return $script;
            } else {
                return $un_utf8_s;
            }
    
        } elseif(function_exists('mb_convert_encoding')) {
            /* Squirrelmail 1.4.0 ? */
    
            if ( stristr($default_charset, 'iso-8859-') ||
            stristr($default_charset, 'utf-8') || 
            stristr($default_charset, 'iso-2022-jp') ) {
                return mb_convert_encoding($script, $default_charset, "UTF-8");
            }
    
        } elseif(function_exists('recode_string')) {
            return recode_string("UTF-8..$default_charset", $script);
    
        } elseif(function_exists('iconv')) {
            return iconv('UTF-8', $default_charset, $script);
        }
        return $script;
    }
    
}

/* Include the appropriate backend class. */
global $avelsieve_backend;
switch($avelsieve_backend) {
    case 'ManageSieve':
    case 'NetSieve':
    case 'File':
        include_once(SM_PATH . 'plugins/avelsieve/include/DO_Sieve_'.$avelsieve_backend.'.class.php');
        break;
    default:
        die('You have specified an invalid backend in config/config.php. Please
        use a supported value for $avelsieve_backend.');
        break;
}

