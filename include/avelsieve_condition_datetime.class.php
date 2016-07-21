<?php
/**
 * Licensed under the GNU GPL. For full terms see the file COPYING that came
 * with the Squirrelmail distribution.
 *
 *
 * @version $Id: avelsieve_condition_datetime.class.php 1042 2009-05-27 12:37:11Z avel $
 * @author Alexandros Vellis <avel@users.sourceforge.net>
 * @copyright 2009 Alexandros Vellis
 * @package plugins
 * @subpackage avelsieve
 */

/**
 * Condition for 'date / time' feature.
 *
 * This class actually accomodates for both of the two available date/time tests,
 * according to RFC 5260.
 *
 * (Paragraph 4) date test
 *    In UI, this is positioned as follows:
 *    rule['cond']['kind'] = 'message'
 *    rule['cond']['type'] = 'datetime'
 *    rule['cond']['header'] = 'date', 'received' etc. (from method datetime_header_ui)
 *    rule['cond'][...] -> rest of datetime options (so-called "common UI")
 *
 * (Paragraph 5) currentdate test
 *    In UI, this is positioned as follows:
 *    rule['cond']['kind'] = 'datetime'
 *    rule['cond'][...] -> rest of datetime options (so-called "common UI")
 *
 */
class avelsieve_condition_datetime extends avelsieve_condition {
    /**
     * The "ui_tree" variable describes how the user interface is structured.
     * Each "varname" array key represents an HTML input widget.
     */
    public $ui;

    /*
     * @var string Which test to use / build UI for? 'currentdate' or 'date'?
     */
    public $test;

    /**
     * Constructor, sets up localized variables of the structures that define
     * the various date/time options (properties $this->ui)
     *
     * @param object $s
     * @param array $rule
     * @param integer $n
     * @param string $test Which test to use / build UI for? 'currentdate' or 'date'
     * @return void
     */
    function __construct(&$s, $rule, $n, $test = 'currentdate') {
        parent::__construct($s, $rule, $n);

        if($test == 'currentdate') {
            $this->test = 'currentdate';
        } else {
            $this->test = 'date';
        }

        /* TODO - add more headers in here or make this extensible. */
        $this->date_headers = array(
            'date' => _("Date"),
            'received' => _("Received")
        );

        $this->tpl_date_metrics = $tpl_date_metrics = array(
            'year' => _("Year"),
            'month' => _("Month"),
            'day' => _("Day"),
            'weekday' => _("Weekday"),
            'hour' => _("Hour"),
            'minute' => _("Minute"),
            'second' => _("Second"),
        );

        $tpl_weekdays = array(
            '0' => _("Sunday"),
            '1' => _("Monday"),
            '2' => _("Tuesday"),
            '3' => _("Wednesday"),
            '4' => _("Thursday"),
            '5' => _("Friday"),
            '6' => _("Saturday"),
        );

        $this->tpl_date_condition = $tpl_date_condition = array(
            'is' => _("Is"),
            'le' => _("Before (&lt;=)"),
            'ge' =>  _("After (=&gt;)"),
            'lt' => _("Before (&lt;)"),
            'gt' =>  _("After (&gt;)"),
        );
        $tpl_cond_2 = $tpl_date_condition;
        // This could be separate to allow for more complex conditions, like
        // regex matches etc.
        // 'matches' => _("Matches"),

        $tpl_months = array(
            '01' => _("January"),
            '02' => _("February"),
            '03' => _("March"),
            '04' => _("April"),
            '05' => _("May"),
            '06' => _("June"),
            '07' => _("July"),
            '08' => _("August"),
            '09' => _("September"),
            '10' => _("October"),
            '11' => _("November"),
            '12' => _("December"),
        );
        
        $this->ui['datetype'] = array(
            'name' => 'datetype',
            'input' => 'select',
            'values' => array(
                'occurence' => _("Occurence"),
                'specific_date' => _("Specific Date"),
                'specific_time' => _("Specific Time"),
                // 'specific_date_time' => _("Specific Date and Time"),
            ),
            'children' => array(
                'occurence' => 'occurence_metric',
                'specific_date' => 'specific_date_conditional',
                'specific_time' => 'specific_time_conditional',
                // 'specific_date_time' => 'specific_date_time_conditional',
            ),
        );

        // Specific Date
        $this->ui['specific_date_conditional'] = array(
            'input' => 'select',
            'values' => $tpl_date_condition,
            'children' => array()
        );
        foreach($tpl_date_condition as $key => $val) {
            $this->ui['specific_date_conditional']['children'][$key] = 'specific_date_picker';
        }
        $this->ui['specific_date_picker'] = array(
            'input' => 'datepicker',
            'input_options' => 'date',
            'terminal' => true,
        );

        // Specific Time
        $this->ui['specific_time_conditional'] = array(
            'input' => 'select',
            'values' => $tpl_date_condition,
            'children' => array()
        );
        foreach($tpl_date_condition as $key => $val) {
            $this->ui['specific_time_conditional']['children'][$key] = 'specific_time_picker';
        }
        $this->ui['specific_time_picker'] = array(
            'input' => 'datepicker',
            'input_options' => 'time',
            'terminal' => true,
        );
        
        // Specific Date and Time
        /*
        $this->ui['specific_date_time_conditional'] = array(
            'input' => 'select',
            'values' => $tpl_date_condition,
            'children' => array()
        );
        foreach($tpl_date_condition as $key => $val) {
            $this->ui['specific_date_time_conditional']['children'][$key] = 'specific_date_time_picker';
        }
        $this->ui['specific_date_time_picker'] = array(
            'input' => 'datepicker',
            'input_options' => 'datetime',
            'terminal' => true,
        );
         */

        // Occurences
        $this->ui['occurence_metric'] = array(
            'input' => 'select',
            'values' => $tpl_date_metrics,
            'children' => array(),
        );
        foreach($tpl_date_metrics as $k => $v) {
            $this->ui['occurence_metric']['children'][$k] = $k.'_occurence_conditional';
            $this->ui[$k.'_occurence_conditional'] = array(
                'input' => 'select',
                'values' => $tpl_cond_2,
                'children' => array()
            );
            foreach($tpl_cond_2 as $k2 => $v2) {
                $this->ui[$k.'_occurence_conditional']['children'][$k2] = 'occurence_'.$k;
            }
            
            $this->ui['occurence_'.$k] = array(
                'input' => 'text',
                'terminal' => true,
                'children' => array()
            );
        }
        
        $this->ui['occurence_month']['input'] = 'select';
        $this->ui['occurence_month']['values'] = $tpl_months;
        
        $this->ui['occurence_day']['input'] = 'select';
        $this->ui['occurence_day']['values'] = $this->_rangePadded(1, 31);
        
        $this->ui['occurence_weekday']['input'] = 'select';
        $this->ui['occurence_weekday']['values'] = $tpl_weekdays;
        
        $this->ui['occurence_hour']['input'] = 'select';
        $this->ui['occurence_hour']['values'] = $this->_rangePadded(0, 23);
        
        $this->ui['occurence_minute']['input'] = 'select';
        $this->ui['occurence_minute']['values'] = $this->_rangePadded(0, 59);
        
        $this->ui['occurence_second']['input'] = 'select';
        $this->ui['occurence_second']['values'] = $this->_rangePadded(0, 60);
    }
    
    public function datetime_header_ui() {
        $out = ' ' . sprintf ( _("of header %s"), avelsieve_html::generic_listbox('cond['.$this->n.'][header]',
            $this->date_headers, (isset($this->data['header']) ? $this->data['header'] : '') ) ) . ' ';
        /* Index extension placeholder */
        return $out;
    }

    /**
     * @return string
     */
    public function datetime_common_ui() {
        $out = $this->ui_tree_output();
        return $out;
    }

    /**
     *
     * @param $varname string   Name of input element from which to start off
     * @param $varvalue string  Value of this input element.
     * @return string
     */
    public function ui_tree_output($varname = '', $varvalue = '') {
        if(!empty($varname) && !empty($varvalue)) {
            $k = $this->_getChildOf($varname, $varvalue);
        } else {
            $k = 'datetype';
        }
        $out = '';
        if(!empty($k)) {
            $out .= $this->_printWidgetHtml($k);
        }

        return $out;
    }
    
    private function _printWidgetHtml($k, $selected = '') {
        $u = &$this->ui[$k];

        $out = '<span id="datetime_condition_'.$k.'_'.$this->n.'">';

        switch($u['input']) {
        case 'select':
            $out .= '<select name="cond['.$this->n.']['.$k.']" id="datetime_input_'.$k.'_'.$this->n.'" ';
            if(!isset($u['terminal'])) {
                $out .= 'onchange="AVELSIEVE.edit.datetimeGetChildren(\''.$k.'\', \''.$this->n.'\'); ';
            }
            $out .= '">';
            $out .= '<option value=""></option>';
            foreach($u['values'] as $key=>$val) {
                $out .= '<option value="'.$key.'"';
                if(isset($this->data[$k]) && $this->data[$k] == $key) {
                    $out .= ' selected=""';
                }
                $out .= '>'.$val.'</option>';
            }
            $out .= '</select>';
            break;

        case 'datepicker': 
            // Note: there is an issue with autocomplete in firefox occasionally overwriting the datepicker
            // javascript widget. That's why we disable autocomplete according to:
            // https://developer.mozilla.org/en/How_to_Turn_Off_form_Autocompletion
            $out .= '<input class="avelsieve_datepicker_'.$u['input_options'].'" type="text" autocomplete="off"'.
                ' name="cond['.$this->n.']['.$k.']" id="datetime_input_'.$k.'_'.$this->n.'" '.
                ' value="'. (isset($this->data[$k]) ? htmlspecialchars($this->data[$k]) : '').'" />';
            break;

        case 'text': 
            $out .= '<input type="text" name="cond['.$this->n.']['.$k.']" id="datetime_input_'.$k.'_'.$this->n.'" '.
                ' value="'. (isset($this->data[$k]) ? htmlspecialchars($this->data[$k]) : '').'" />';
            break;

        default:
            $out .= ' nothing ';
            break;
        }

        $out .= '</span>';
        $out .= '<span id="datetime_condition_after_'.$k.'_'.$this->n.'">';
        // Print the inner UI if we are showing a rule that already has data in it.
        if(isset($this->data[$k]) && !empty($this->data[$k])) {
            $out .= $this->ui_tree_output($k, $this->data[$k]); 
        }
        $out .= '</span>';
        return $out;
    }

    function _getChildOf($varname, $varvalue) {
        if(isset($this->ui[$varname]['children'])) {
            foreach($this->ui[$varname]['children'] as $child => $widget) {
                if($varvalue == $child) {
                    return $widget;
                }
            }
        }
        return false;
    }

    /**
     * Like range(), except that it pads array <i>keys</i> to the same string length
     * by adding leading zeros.
     *
     * @param int $start
     * @param int $end
     * @param int $step
     * @return array
     */
    private function _rangePadded($start, $end, $step = 1) {
        $aNormal = range($start, $end, $step);
        $length = strlen( $end );
        
        $aPadded = $aNormal;
        foreach($aPadded as &$val) {
            if($difference = $length - strlen($val)) {
                if($difference == 1) $pad = '0';
                if($difference == 2) $pad = '00';
                if($difference == 3) $pad = '000';
                $val = $pad.$val;
            }
        }
        $out = array_combine($aPadded, $aNormal);
        return $out;
    }

    /**
     * Generate Sieve code and human-readable texts.
     *
     * @return array ($out, $text, $terse) 
     */
    public function generate_sieve() {
        $c = &$this->data;

        $out = $text = $terse = '';
        $out .= ' ' . $this->test . ' ';
        if($this->test == 'currentdate') {
            $text .= _("Current date / time:") . ' ';
            $terse .= '<em>' . _("Current date:") . '</em> ';
        } elseif($this->test == 'date') {
            $text .= _("message header");
            $terse .= '<em>' . _("Message header") . '</em>';
        }


        if(isset($c['originalzone']) && $c['originalzone']) {
            $out .= ':originalzone ';
        } elseif(isset($c['zone'])) {
            $out .= ':zone '.$c['zone'].' ';
        }

        if($c['datetype'] == 'specific_date') {
            $cmp = &$c['specific_date_conditional'];

        } elseif($c['datetype'] == 'specific_time') {
            $cmp = &$c['specific_time_conditional'];

        } elseif($c['datetype'] == 'occurence') {
            $cmp = &$c[$c['occurence_metric'].'_occurence_conditional'];
        }

        // The human-readable texts for the comparators are deferred because we'll
        // print them out after showing on what message header they apply to, if
        // applicable.
        switch($cmp) {
        case 'is':
        case 'on':
        default:
            $out .= ':is';
            $textDeferred = _("is %s");
            $terseDeferred = ' ' . _("is") . ' ';
            break;
        case 'le':
            $out .= ':value "le"';
            $textDeferred = _("is before than %s (inclusive)");
            $terseDeferred = ' '.$this->tpl_date_condition[$cmp].' ';
            break;
        case 'ge':
            $out .= ':value "ge"';
            $textDeferred = _("is after than %s (inclusive)");
            $terseDeferred = ' '.$this->tpl_date_condition[$cmp].' ';
            break;
        case 'lt':
            $out .= ':value "lt"';
            $textDeferred = _("is before than %s");
            $terseDeferred = ' '.$this->tpl_date_condition[$cmp].' ';
            break;
        case 'gt':
            $out .= ':value "gt"';
            $textDeferred = _("is after than %s");
            $terseDeferred = ' '.$this->tpl_date_condition[$cmp].' ';
            break;
        }

        $out .= ' ';
        $text .= ' ';
        $terse .= ' ';

        /* --- header-name --- (only for date test, not for currentdate). Of course,
         * for date test, this is required. */
        if(!empty($c['header'])) {
            $out .= '"'.strtolower($c['header']).'" ';
            $text .= ' '. sprintf( _("&quot;%s&quot;"), $this->date_headers[$c['header']] );
            $terse .=  ' '.sprintf( _("&quot;<tt>%s:</tt>&quot;"), $this->date_headers[$c['header']] ) . '<br/>';
        }

        /* --- date-part + key-list --- */
        if($c['datetype'] == 'specific_date') {
            // From 'specific date' UI
            $out .= '"date" "'.$c['specific_date_picker'].'"';
            $text .= sprintf($textDeferred, htmlspecialchars($c['specific_date_picker']));
            $terse .= $terseDeferred . htmlspecialchars($c['specific_date_picker']);
            
        } elseif($c['datetype'] == 'specific_time') {
            // From 'specific time' UI
            $out .= '"time" "'.$c['specific_time_picker'].':00"';
            $text .= sprintf($textDeferred, htmlspecialchars($c['specific_time_picker']));
            $terse .= $terseDeferred . htmlspecialchars($c['specific_time_picker']);

        } elseif($c['datetype'] == 'occurence') {
            // From 'occurence' UI

            $out .= '"'.$c['occurence_metric'].'" ';
            $text .= $this->tpl_date_metrics[$c['occurence_metric']] . ' ';
            $terse .= $this->tpl_date_metrics[$c['occurence_metric']] . ' ';

            if(isset($c['occurence_'.$c['occurence_metric']])) {
                $out .= '"'.$c['occurence_'.$c['occurence_metric']].'" ';
            } else {
                //key occurence_'.$c['occurence_metric'] is empty? 
                $out .= '""';
            }
            if(isset($this->ui['occurence_'.$c['occurence_metric']]['values'][$c['occurence_'.$c['occurence_metric']]])) {
                $humanReadableValue = $this->ui['occurence_'.$c['occurence_metric']]['values'][$c['occurence_'.$c['occurence_metric']]];
            } else {
                $humanReadableValue = $c['occurence_'.$c['occurence_metric']];
            }

            $text .= sprintf($textDeferred, $humanReadableValue);
            $terse .= $terseDeferred . $humanReadableValue;
        }

        return array($out, $text, $terse);
    }

}

