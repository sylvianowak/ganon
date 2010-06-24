<?php
/**
 * @author Niels A.D.
 * @package Ganon
 * @link http://code.google.com/p/ganon/
 * @license http://dev.perl.org/licenses/artistic.html Artistic License
 */
 
/**
 * Compress all whitespace in string (to a single space)
 * @param string $text
 * @return string
 */
function compress_whitespace($text) {
	return preg_replace('`\s+`', ' ', $text);
}

/**
 * Indents text
 * @param string $text
 * @param int $indent
 * @param string $indent_string
 * @return string
 */
function indent_text($text, $indent, $indent_string = '  ') {
	if ($indent && $indent_string) {
		return str_replace("\n", "\n".str_repeat($indent_string, $indent), $text);
	} else {
		return $text;
	}
}

/**
 * Class used to format/minify HTML nodes
 *
 * Used like:
 * <code>
 * <?php
 *   $formatter = new HTML_Formatter();
 *   $formatter->format($root);
 * ?>
 * </code>
 */
class HTML_Formatter {

	/**
	 * Determines which elements start on a new line and which function as block
	 * @var array('element' => array('new_line' => true, 'as_block' => true, 'format_inside' => true))
	 */
	var $block_elements = array(
		'p' =>			array('new_line' => true,  'as_block' => true,  'format_inside' => true),
		'h1' => 		array('new_line' => true,  'as_block' => true,  'format_inside' => true),
		'h2' =>  		array('new_line' => true,  'as_block' => true,  'format_inside' => true),
		'h3' =>  		array('new_line' => true,  'as_block' => true,  'format_inside' => true),
		'h4' =>  		array('new_line' => true,  'as_block' => true,  'format_inside' => true),
		'h5' =>  		array('new_line' => true,  'as_block' => true,  'format_inside' => true),
		'h6' =>  		array('new_line' => true,  'as_block' => true,  'format_inside' => true),

		'form' =>  		array('new_line' => true,  'as_block' => true,  'format_inside' => true),
		'fieldset' =>  	array('new_line' => true,  'as_block' => true,  'format_inside' => true),
		'legend' =>  	array('new_line' => true,  'as_block' => false, 'format_inside' => true),
		'dl' =>  		array('new_line' => true,  'as_block' => false, 'format_inside' => true),
		'dt' =>  		array('new_line' => true,  'as_block' => false, 'format_inside' => true),
		'dd' =>  		array('new_line' => true,  'as_block' => true,  'format_inside' => true),
		'ol' =>  		array('new_line' => true,  'as_block' => true,  'format_inside' => true),
		'ul' =>  		array('new_line' => true,  'as_block' => true,  'format_inside' => true),
		'li' =>  		array('new_line' => true,  'as_block' => false, 'format_inside' => true),

		'table' =>  	array('new_line' => true,  'as_block' => true,  'format_inside' => true),
		'tr' =>  		array('new_line' => true,  'as_block' => true,  'format_inside' => true),

		'dir' =>  		array('new_line' => true,  'as_block' => true,  'format_inside' => true),
		'menu' =>  		array('new_line' => true,  'as_block' => true,  'format_inside' => true),
		'address' =>  	array('new_line' => true,  'as_block' => true,  'format_inside' => true),
		'blockquote' => array('new_line' => true,  'as_block' => true,  'format_inside' => true),
		'center' =>  	array('new_line' => true,  'as_block' => true,  'format_inside' => true),
		'del' =>  		array('new_line' => true,  'as_block' => false, 'format_inside' => true),
		//'div' =>  	array('new_line' => false, 'as_block' => true,  'format_inside' => true),
		'hr' =>  		array('new_line' => true,  'as_block' => true,  'format_inside' => true),
		'ins' =>  		array('new_line' => true,  'as_block' => true,  'format_inside' => true),
		'noscript' =>  	array('new_line' => true,  'as_block' => true,  'format_inside' => true),
		'pre' =>  		array('new_line' => true,  'as_block' => true,  'format_inside' => false),
		'script' =>  	array('new_line' => true,  'as_block' => true,  'format_inside' => true),
		'style' =>  	array('new_line' => true,  'as_block' => true,  'format_inside' => true),

		'html' => 		array('new_line' => true,  'as_block' => true,  'format_inside' => true),
		'head' => 		array('new_line' => true,  'as_block' => true,  'format_inside' => true),
		'body' => 		array('new_line' => true,  'as_block' => true,  'format_inside' => true),
		'title' => 		array('new_line' => true,  'as_block' => false, 'format_inside' => false)
	);

	/**
	 * Determines which characters are considered whitespace
	 * @var array("\t" => true) True to recognize as new line
	 */
	var $whitespace = array(
		' ' => false,
		"\t" => false,
		"\x0B" => false,
		"\0" => false,
		"\n" => true,
		"\r" => true
	);

	/**
	 * String that is used to generate correct indenting
	 * @var string
	 */
	var $indent_string = ' ';

	/**
	 * String that is used to break lines
	 * @var string
	 */
	var $linebreak_string = "\n";

	/**
	 * Other formatting options
	 * @var array
	 */
	var $options = array(
		'img_alt' => '',
		'self_close_str' => null,
		'attribute_shorttag' => false,
		'sort_attributes' => false,
		'attributes_case' => CASE_LOWER,
		'minify_script' => true
	);

	/**
	 * Errors found during formatting
	 * @var array
	 */
	var $errors = array();


	/**
	 * Class constructor
	 * @param array $options {@link $options}
	 */
	function __construct($options = array()) {
		$this->options = array_merge($this->options, $options);
	}
	
	#php4 PHP4 class constructor compatibility
	#function HTML_Formatter($options = array()) {return $this->__construct($options);}
	#php4e

	/**
	 * Class magic invoke method, performs {@link format()}
	 * @access private
	 */
	function __invoke(&$node) {
		return $this->format($node);
	}

	/**
	 * Minifies HTML / removes unneeded whitespace
	 * @param HTML_Node $root
	 * @param bool $strip_comments
	 * @param bool $recursive
	 */
	static function minify_html(&$root, $strip_comments = true, $recursive = true) {
		if ($strip_comments) {
			foreach($root->select(':comment', false, $recursive, true) as $c) {
				$prev = $c->getSibling(-1);
				$next = $c->getSibling(1);
				$c->delete();
				if ($prev && $next && ($prev::NODE_TYPE === $root::NODE_TEXT) && ($next::NODE_TYPE === $root::NODE_TEXT)) {
					$prev->text .= $next->text;
					$next->delete();
				}
			}
		}
		foreach($root->select('(!pre + !xmp + !style + !script + !"?php" + !"~text~" + !"~comment~"):not-empty > "~text~"', false, $recursive, true) as $c) {
			$c->text = compress_whitespace($c->text);
		}
	}

	/**
	 * Minifies javascript using JSMin+
	 * @param HTML_Node $root
	 * @param string $indent_string
	 * @param bool $wrap_comment Wrap javascript in HTML comments (<!-- ~text~ //-->)
	 * @param bool $recursive
	 * @return bool|array Array of errors on failure, true on succes
	 */
	static function minify_javascript(&$root, $indent_string = ' ', $wrap_comment = true, $recursive = true) {
		include_once('third party/jsminplus.php');

		$errors = array();
		foreach($root->select('script:not-empty > "~text~"', false, $recursive, true) as $c) {
			try {
				$text = $c->text;
				while ($text) {
					$text = trim($text);
					//Remove comment/CDATA tags at begin and end
					if (substr($text, 0, 4) === '<!--') {
						$text = substr($text, 5);
						continue;
					} elseif (strtolower(substr($text, 0, 9)) === '<![cdata[') {
						$text = substr($text, 10);
						continue;
					}

					if (($end = substr($text, -3)) && (($end === '-->') || ($end === ']]>'))) {
						$text = substr($text, 0, -3);
						continue;
					}

					break;
				}

				if (trim($text)) {
					$text = JSMinPlus::minify($text);
					if ($wrap_comment) {
						$text = "<!--\n".$text."\n//-->";
					}
					if ($indent_string && ($wrap_comment || (strpos($text, "\n") !== false))) {
						$text = indent_text("\n".$text, $c->indent(), $indent_string);
					}
				}
				$c->text = $text;
			} catch (Exception $e) {
				$errors[] = array($e, $c->parent->dumpLocation());
			}
		}

		return (($errors) ? $errors : true);
	}

	/**
	 * Formats HTML
	 * @param HTML_Node $root
	 * @param bool $recursive
	 * @access private
	 */
	function format_html(&$root, $recursive = null) {
		if ($recursive === null) {
			$recursive = true;
			self::minify_html($root);
		} elseif (is_int($recursive)) {
			$recursive = (($recursive > 1) ? $recursive - 1 : false);
		}

		$root_tag = strtolower($root->tag);
		$in_block = isset($this->block_elements[$root_tag]) && $this->block_elements[$root_tag]['as_block'];
		$child_count = count($root->children);

		if (isset($this->options['attributes_case']) && $this->options['attributes_case']) {
			$root->attributes = array_change_key_case($root->attributes, $this->options['attributes_case']);
			$root->attributes_ns = null;
		}	
	
		if (isset($this->options['sort_attributes']) && $this->options['sort_attributes']) {
			if ($this->options['sort_attributes'] === 'reverse') {
				krsort($root->attributes);
			} else {
				ksort($root->attributes);
			}
		}

		if ($root::NODE_TYPE === $root::NODE_ELEMENT) {
			$root->setTag(strtolower($root->tag), true);
			if (($this->options['img_alt'] !== null) && ($root_tag === 'img') && (!isset($root->alt))) {
				$root->alt = $this->options['img_alt'];
			}
		}
		if ($this->options['self_close_str'] !== null) {
			$root->self_close_str = $this->options['self_close_str'];
		}
		if ($this->options['attribute_shorttag'] !== null) {
			$root->attribute_shorttag = $this->options['attribute_shorttag'];
		}

		$prev = null;
		$n_tag = '';
		$prev_tag = '';
		$as_block = false;
		$prev_asblock = false;
		for($i = 0; $i < $child_count; $i++) {
			$n =& $root->children[$i];
			$indent = $n->indent();

			if ($n::NODE_TYPE !== $root::NODE_TEXT) {
				$n_tag = strtolower($n->tag);
				$new_line = isset($this->block_elements[$n_tag]) && $this->block_elements[$n_tag]['new_line'];
				$as_block = isset($this->block_elements[$n_tag]) && $this->block_elements[$n_tag]['as_block'];
				$format_inside = ((!isset($this->block_elements[$n_tag])) || $this->block_elements[$n_tag]['format_inside']);

				if ($prev && ($prev::NODE_TYPE === $root::NODE_TEXT) && $prev->text && ($char = $prev->text[strlen($prev->text) - 1]) && isset($this->whitespace[$char])) {
					if ($this->whitespace[$char]) {
						$prev->text .= str_repeat($this->indent_string, $indent);
					} else {
						$prev->text = substr_replace($prev->text, $this->linebreak_string.str_repeat($this->indent_string, $indent), -1, 1);
					}
				} elseif (($new_line || $prev_asblock || ($in_block && ($i === 0)))){
					if ($prev && ($prev::NODE_TYPE === $root::NODE_TEXT)) {
						$prev->text .= $this->linebreak_string.str_repeat($this->indent_string, $indent);
					} else {
						$root->addText($this->linebreak_string.str_repeat($this->indent_string, $indent), $i);
						++$child_count;
					}
				}

				if ($format_inside) {
					$last = end($n->children);
					$last_tag = ($last) ? strtolower($last->tag) : '';
					$last_asblock = ($last_tag && isset($this->block_elements[$last_tag]) && $this->block_elements[$last_tag]['as_block']);

					if (($n->childCount(true) > 0) || (trim($n->getPlainText()))) {
						if ($last && ($last::NODE_TYPE === $root::NODE_TEXT) && $last->text && ($char = $last->text[strlen($last->text) - 1]) && isset($this->whitespace[$char])) {
							if ($as_block || ($last->index() > 0) || isset($this->whitespace[$last->text[0]])) {
								if ($this->whitespace[$char]) {
									$last->text .= str_repeat($this->indent_string, $indent);
								} else {
									$last->text = substr_replace($last->text, $this->linebreak_string.str_repeat($this->indent_string, $indent), -1, 1);
								}
							}
						} elseif (($as_block || $last_asblock || ($in_block && ($i === 0))) && $last) {
							if ($last && ($last::NODE_TYPE === $root::NODE_TEXT)) {
								$last->text .= $this->linebreak_string.str_repeat($this->indent_string, $indent);
							} else {
								$n->addText($this->linebreak_string.str_repeat($this->indent_string, $indent));
							}
						}
					} elseif (!trim($n->getInnerText())) {
						$n->clear();
					}

					if ($recursive) {
						$this->format_html($n, $recursive);
					}
				}

			} elseif (trim($n->text) && ((($i - 1 < $child_count) && ($char = $n->text[0]) && isset($this->whitespace[$char])) || ($in_block && ($i === 0)))) {
				if (isset($this->whitespace[$char])) {
					if ($this->whitespace[$char]) {
						$n->text = str_repeat($this->indent_string, $indent).$n->text;
					} else {
						$n->text = substr_replace($n->text, $this->linebreak_string.str_repeat($this->indent_string, $indent), 0, 1);
					}
				} else {
					$n->text = $this->linebreak_string.str_repeat($this->indent_string, $indent).$n->text;
				}
			}

			$prev = $n;
			$prev_tag = $n_tag;
			$prev_asblock = $as_block;
		}
		
		return true;
	}

	/**
	 * Formats HTML/Javascript
	 * @param HTML_Node $root
	 * @see format_html()
	 */
	function format(&$node) {
		$this->errors = array();
		if ($this->options['minify_script']) {
			$a = self::minify_javascript($node, $this->indent_string, true, true);
			if (is_array($a)) {
				foreach($a as $error) {
					$this->errors[] = $error[0]->getMessage().' >>> '.$error[1];
				}
			}
		}
		return $this->format_html($node);
	}
}

?>