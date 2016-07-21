<?php
/**
 * Dumpr - Dump any resource with syntax highlighting, indenting and variable type information to the screen in a very intuitive format
 *
 * Licensed under the terms of the GNU Lesser General Public License:
 *      http://www.opensource.org/licenses/lgpl-license.php
 *
 * @package plugins
 * @subpackage avelsieve
 * @author    Jari Berg Jensen &lt;jari@razormotion.com&gt;
 *           http://www.razormotion.com/software/dumpr/
 * @license   LGPL
 * @version  1.7
 * Modified  2005/03/31
 * 
 * Changes since Revision 1.0
 *     Revision 1.0
 *         - Initial release
 *     Revision 1.1
 *         - Added syntax highlighting
 *     Revision 1.2
 *         - Improved the regular expressions
 *     Revision 1.3
 *         - Fixed a few syntax highlighting bugs
 *     Revision 1.4
 *         - Fixed minor bugs
 *     Revision 1.5
 *         - Expanded numbers (ie. int(10) => int(2) 10, float(128.64) => float(6) 128.64 etc.)
 *         - Removed the extra line paddings in the output
 *     Revision 1.6
 *         - Added a simple regular expression to pretty the output (html source)
 *     Revision 1.7
 *        - Added syntax highlighting for types (ie. type of (mysql...), type of (stream) etc.)
 *        - Added syntax highlighting for resource(..)
 */

/**
 * Dump any resource with syntax highlighting, indenting and variable type information
 * @param mixed $data
 * @param boolean $return
 */
function dumpr($data, $return = false) {
    ob_start();
    var_dump($data);
    $c = ob_get_contents();
    ob_end_clean();

    $c = preg_replace("/\r\n|\r/", "\n", $c);
    $c = str_replace("]=>\n", '] = ', $c);
    $c = preg_replace('/= {2,}/', '= ', $c);
    $c = preg_replace("/\[\"(.*?)\"\] = /i", "[$1] = ", $c);
    $c = preg_replace('/  /', "    ", $c);
    $c = preg_replace("/\"\"(.*?)\"/i", "\"$1\"", $c);

    //$c = htmlspecialchars($c, ENT_NOQUOTES);

    // Expand numbers (ie. int(10) => int(2) 10, float(128.64) => float(6) 128.64 etc.)
    $c = preg_replace("/(int|float)\(([0-9\.]+)\)/ie", "'$1('.strlen('$2').') <span class=\"number\">$2</span>'", $c);
    
    // Syntax Highlighting of Strings. This seems cryptic, but it will also allow non-terminated strings to get parsed.
    $c = preg_replace("/(\[[\w ]+\] = string\([0-9]+\) )\"(.*?)/sim", "$1<span class=\"string\">\"", $c);
    $c = preg_replace("/(\"\n{1,})( {0,}\})/sim", "$1</span>$2", $c);
    $c = preg_replace("/(\"\n{1,})( {0,}\[)/sim", "$1</span>$2", $c);
    $c = preg_replace("/(string\([0-9]+\) )\"(.*?)\"\n/sim", "$1<span class=\"string\">\"$2\"</span>\n", $c);
    
    $regex = array(
        // Numberrs
        'numbers' => array('/(^|] = )(array|float|int|string|resource|object\(.*\)|\&amp;object\(.*\))\(([0-9\.]+)\)/i', '$1$2(<span class="number">$3</span>)'),
        // Keywords
        'null' => array('/(^|] = )(null)/i', '$1<span class="keyword">$2</span>'),
        'bool' => array('/(bool)\((true|false)\)/i', '$1(<span class="keyword">$2</span>)'),
        // Types
        'types' => array('/(of type )\((.*)\)/i', '$1(<span class="type">$2</span>)'),
        // Objects
        'object' => array('/(object|\&amp;object)\(([\w]+)\)/i', '$1(<span class="object">$2</span>)'),
        // Function
        'function' => array('/(^|] = )(array|string|int|float|bool|resource|object|\&amp;object)\(/i', '$1<span class="function">$2</span>('),
    );
    
    foreach ($regex as $x) {
        $c = preg_replace($x[0], $x[1], $c);
    }
    
    $style = '
    /* outside div - it will float and match the screen */
    .dumpr {
        margin: 2px;
        padding: 2px;
        background-color: #fbfbfb;
        float: left;
        clear: both;
    }
    /* font size and family */
    .dumpr pre {
        color: #000000;
        font-size: 9pt;
        font-family: "Courier New",Courier,Monaco,monospace;
        margin: 0px;
        padding-top: 5px;
        padding-bottom: 7px;
        padding-left: 9px;
        padding-right: 9px;
    }
    /* inside div */
    .dumpr div {
        background-color: #fcfcfc;
        border: 1px solid #d9d9d9;
        float: left;
        clear: both;
    }
    /* syntax highlighting */
    .dumpr span.string {color: #c40000;}
    .dumpr span.number {color: #ff0000;}
    .dumpr span.keyword {color: #007200;}
    .dumpr span.function {color: #0000c4;}
    .dumpr span.object {color: #ac00ac;}
    .dumpr span.type {color: #0072c4;}
    ';
    
    $style = preg_replace("/ {2,}/", "", $style);
    $style = preg_replace("/\t|\r\n|\r|\n/", "", $style);
    $style = preg_replace("/\/\*.*?\*\//i", '', $style);
    $style = str_replace('}', '} ', $style);
    $style = str_replace(' {', '{', $style);
    $style = trim($style);

    $c = trim($c);
    $c = preg_replace("/\n<\/span>/", "</span>\n", $c);
    
    $out = "\n<!-- Dumpr Begin -->\n".
        "<style type=\"text/css\">".$style."</style>\n".
        "<div class=\"dumpr\"><div><pre>\n$c\n</pre></div></div><div style=\"clear:both;\">&nbsp;</div>".
        "\n<!-- Dumpr End -->\n";
    if($return) {
        return $out;
    } else {
        echo $out;
    }
}

