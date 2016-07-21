<?php
/**
 * Licensed under the GNU GPL. For full terms see the file COPYING that came
 * with the Squirrelmail distribution.
 *
 *
 * @version $Id: avelsieve_condition.class.php 1027 2009-05-22 09:57:07Z avel $
 * @author Alexandros Vellis <avel@users.sourceforge.net>
 * @copyright 2009 Alexandros Vellis
 * @package plugins
 * @subpackage avelsieve
 */

/**
 * Root class for SIEVE conditions.
 */
class avelsieve_condition {

    /**
     * @var object DO_Sieve handler
     */
    protected $s;

    /**
     * @var array The avelsieve rule to which this condition is a part of
     */
    protected $rule;

    /**
     * @var string Numeric index in conditions list
     */
    protected $n;

    /**
     * @var array The actual condition structure
     */
    protected $data;

    /**
     * Constructor
     *
     * @param $s object
     * @param $rule array
     * @param $n integer
     * @return void
     */
    function __construct(&$s, $rule, $n) {
        $this->s = $s;
        $this->rule = $rule;
        $this->n = $n;
        if(isset($this->rule['cond']) && isset($this->rule['cond'][$n])) {
            $this->data = $this->rule['cond'][$n];
        } else {
            $this->data = array();
        }
    }
}

