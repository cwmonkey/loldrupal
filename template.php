<?php

function loldrupal_image_style($variables) {
  $style_name = $variables['style_name'];
  $path = $variables['path'];
  
  // theme_image() can only honor the $getsize parameter with local file paths.
  // The derivative image is not created until it has been requested so the file
  // may not yet exist, in this case we just fallback to the URL.
  $style_path = image_style_path($style_name, $path);
  if (!file_exists($style_path)) {
    $style_path = image_style_url($style_name, $path);
  }
  $variables['path'] = $style_path;
  if (

  is_file($style_path)) {
    if (list($width, $height, $type, $attributes) = @getimagesize($style_path)) {
      $variables['width'] = $width;
      $variables['height'] = $height;
    }
  }
  
  return theme('image', $variables);
}

/* Template helpers */

// pretty print arrays
function _l($someArray, $prepend = '') {
    echo "<hr />";
    if ( !is_array($someArray) && !is_object($someArray) ) {
      echo '<div class="data"><span class="value">' . htmlspecialchars($someArray) . '</span></div>';
    }

    $someArray = (array)$someArray;
    $iterator = new RecursiveIteratorIterator(new RecursiveArrayIterator($someArray), RecursiveIteratorIterator::SELF_FIRST);
    foreach ($iterator as $k => $v) {
        //$indent = str_repeat('&nbsp;', 10 * $iterator->getDepth());
        // Not at end: show key only
        if ($iterator->hasChildren()) {
            //echo "$k :<br>";
        // At end: show key, value and path
        } else {
            for ($p = array(), $i = 0, $z = $iterator->getDepth(); $i <= $z; $i++) {
                $b = '[';
                $e = ']';
                $c = $iterator->getSubIterator($i)->current();
                if ( is_object($c) ){
                  $b = '{';
                  $e = '}';
                }
                $p[] = $b . '\'' . $iterator->getSubIterator($i)->key() . '\'' . $e;
            }
            $path = $prepend . implode('', $p);
            echo '<div class="data"><span class="key">' . $path . ': <span class="value">' . htmlspecialchars($v) . '</span></div>';
        }
    }
}

function loldrupal_preprocess_node(&$variables) {
	unset($variables['field_image']);
	unset($variables['field_tags']);
	unset($variables['body']);
	unset($variables['title']);
	unset($variables['tags']);

	$variables['v'] = new lolDrupalView($variables);
}

/*class lolDrupalView {
	public $variables;

	public function __construct($variables) {
		$this->variables = $variables;
	}

	public $eaches = array();
	public function __call($n, $args = null) {
		$separated = preg_replace('%(?<!^)\p{Lu}%usD', '_$0', $n);
		$lower = mb_strtolower($separated, 'utf-8');
		$parts = explode('_', $lower);
		$check_exists = false;
		$each_value = false;
		if ( $parts[0] == 'get' ) {
			array_shift($parts);
		} elseif ( $parts[0] == 'exists' ) {
			$check_exists = array_shift($parts);
		} elseif ( $parts[0] == 'each' ) {
			if ( isset($this->eaches[$n]) ) return $this->_seach(&$this->eaches[$n]);
			$each_value = array_shift($parts);
		}
		$use_node = false;
		if ( $parts[0] == 'node' ) $use_node = array_shift($parts);
		$name = implode('_', $parts);
		$field_name = 'field_' . $name;
		$var_found = false;
		$retval = null;

		if ( !$use_node ) {
			if ( method_exists($this, 'get_' . $name) ) {
				if ( $check_exists ) return true;
				$var_found = true;
				$retval = call_user_func_array(array($this, 'get_' . $name), $args);
			} elseif ( is_array($this->variables) ) {
				if ( isset($this->variables[$name]) ) {
					if ( $check_exists ) return true;
					$var_found = true;
					$retval = $this->variables[$name];
				} elseif ( isset($this->variables[$field_name]) ) {
					if ( $check_exists ) return true;
					$var_found = true;
					$retval = $this->variables[$field_name];
				}
			} elseif ( is_object($this->variables) ) {
				if ( isset($this->variables->$name) ) {
					if ( $check_exists ) return true;
					$var_found = true;
					$retval = $this->variables->$name;
				} elseif ( isset($this->variables->$field_name) ) {
					if ( $check_exists ) return true;
					$var_found = true;
					$retval = $this->variables->$field_name;
				}
			}
		}

		if ( !$var_found ) {
			if ( method_exists($this, 'get_' . $field_name) ) {
				if ( $check_exists ) return true;
				$var_found = true;
				$retval = call_user_func_array(array($this, 'get_' . $field_name), $args);
			} elseif ( isset($this->variables['node']->$name) ) {
				if ( $check_exists ) return true;
				$retval = $this->_snode($this->variables['node'], $name);
			} elseif ( isset($this->variables['node']->$field_name) ) {
				if ( $check_exists ) return true;
				$retval = $this->_snode($this->variables['node'], $field_name);
			}
		}

		if ( $check_exists ) return false;

		if ( $each_value ) {
			$this->eaches[$n] = &$retval;
			return $this->_seach(&$this->eaches[$n]);
		}

		return new lolDrupalValue($retval);
	}

	public function _snode($var, $prop, $type = 'node') {
		$retval = field_get_items($type, $var, $prop);
    	if ( is_array($retval) ) reset($retval);
    	return $retval;
	}
}

class lolDrupalValue extends lolDrupalView {
	public $value;

	public function __construct($value) {
		$this->value = $value;
	}

	public function __toString() {
		if ( is_array($this->value) && isset($this->value[0]) ) {
			return $this->_sprint($this->value[0]);
		} else {
			return $this->_sprint($this->value);
		}
	}

	public function get_img($image_style) {

	}

	public function _sprint($var) {
		if ( is_string($var) ) {
			return $var;
		} elseif ( isset($var['safe_value']) ) {
			return $var['safe_value'];
		} elseif ( isset($var['value']) ) {
			return $var['value'];
		}
		return '';
	}

	public function each() {
		return $this->_seach(&$this->value);
	}

	public function _seach(&$var) {
		if ( !is_array($var) ) return null;

		list($k, $ea) = each($var);

		if ( !$ea ) {
			reset($var);
			return $ea;
		}

		if ( isset($ea['node']) ) {
			return array($k, $ea['node']);
		}

		if ( isset($ea['_staxonomy_term']) ) {
			return array($k, $ea['_staxonomy_term']);
		}

		$taxonomy_term = null;
		if ( isset($ea['taxonomy_term']) ) {
			$taxonomy_term = $ea['taxonomy_term'];
		} elseif ( isset($ea['tid']) ) {
			$taxonomy_term = taxonomy_term_load($ea['tid']);
		}

		if ( $taxonomy_term ) {
			$ea['_staxonomy_term'] = new lolDrupalTaxonomy($taxonomy_term);
			return array($k, $ea['_staxonomy_term']);
		}

		if ( is_array($ea) && count($ea) < 3 && isset($ea['nid']) ) {
			$ea = node_load($ea['nid']);
		}

		return array($k, $ea);
	}
}

class lolDrupalTaxonomy extends lolDrupalValue {
	public $taxonomy;

	public function __construct($taxonomy) {
		$this->taxonomy = $taxonomy;
	}

	public function get_url() {
		if ( isset($this->taxonomy->_surl) ) {
			$retval = $this->taxonomy->_surl;
		} else {
			$uri = entity_uri('taxonomy_term', $this->taxonomy);
			$this->taxonomy->_surl = url($uri['path']);
			$retval = $this->taxonomy->_surl;
		}

		return $retval;
	}
}*/

include('lolView.class.php');