<?php
/**
 * @author Niels A.D.
 * @package Ganon
 * @link http://code.google.com/p/ganon/
 * @license http://dev.perl.org/licenses/artistic.html Artistic License
 */

/**
 * Returns HTML DOM from string
 * @param string $str
 * @param bool $return_root Return root node or return parser object
 * @return HTML_Parser_HTML5|HTML_Node
 */
function str_get_dom($str, $return_root = true) {
	$a = new HTML_Parser_HTML5($str);
	return (($return_root) ? $a->root : $a);
}

/**
 * Returns HTML DOM from file/website
 * @param string $str
 * @param bool $return_root Return root node or return parser object
 * @return HTML_Parser_HTML5|HTML_Node
 */
function file_get_dom($file, $return_root = true) {
	$f = file_get_contents($file);
	return (($f === false) ? false : str_get_dom($f, $return_root));
}

/**
 * Format/beautify DOM
 * @param HTML_Node $root
 * @param array $options Extra formatting options {@link HTML_Formatter::$options}
 * @return bool
 */
function dom_format(&$root, $options = array()) {
	$formatter = new HTML_Formatter($options);
	return $formatter->format($root);
}

if (version_compare(PHP_VERSION, '5.0.0', '<')) {
	/**
	 * PHP alternative to str_split, for backwards compatibility
	 * @param string $string
	 * @return string
	 */
	function str_split($string) {
		$res = array();
		$size = strlen($string);
		for ($i = 0; $i < $size; $i++) {
			$res[] = $string[$i];
		}
		
		return $res;
	}
}

if (version_compare(PHP_VERSION, '5.2.0', '<')) {
	/**
	 * PHP alternative to array_fill_keys, for backwards compatibility
	 * @param array $keys
	 * @param mixed $value
	 * @return array
	 */
	function array_fill_keys($keys, $value) {
		$res = array();
		foreach($keys as $k) {
			$res[$k] = $value;
		}
		
		return $res;
	}
}

#!! <- Ignore when converting to single file
if (!defined('GANON_NO_INCLUDES')) {
	define('GANON_NO_INCLUDES', true);
	include_once('gan_tokenizer.php');
	include_once('gan_parser_html.php');
	include_once('gan_node_html.php');
	include_once('gan_selector_html.php');
	include_once('gan_formatter.php');
}
#!

?>