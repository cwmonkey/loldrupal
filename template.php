<?php

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
	$variables['v'] = new lolDrupalView($variables);
}

class lolDrupalView {
	public $variables;

	public function __construct($variables) {
		$this->variables = $variables;
	}

	public $eaches = array();
	public function __call($n, $args = null) {
		$separated = preg_replace('%(?<!^)\p{Lu}%usD', '_$0', $n);
		$lower = mb_strtolower($separated, 'utf-8');
		$parts = explode('_', $lower);
		$print_value = false;
		$check_exists = false;
		$each_value = false;
		if ( $parts[0] == 'get' ) {
			array_shift($parts);
		} elseif ( $parts[0] == 'print' ) {
			$print_value = array_shift($parts);
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
			if ( isset($this->variables[$name]) ) {
				if ( $check_exists ) return true;
				$var_found = true;
				$retval = $this->variables[$name];
			} elseif ( isset($this->variables[$field_name]) ) {
				if ( $check_exists ) return true;
				$var_found = true;
				$retval = $this->variables[$field_name];
			}
		}

		if ( !$var_found ) {
			if ( isset($this->variables[$name]) ) {
				if ( $check_exists ) return true;
				$retval = $this->_snode($this->variables['node'], $name);
			} elseif ( isset($this->variables[$field_name]) ) {
				if ( $check_exists ) return true;
				$retval = $this->_snode($this->variables['node'], $field_name);
			}
		}

		if ( $check_exists ) return false;

		if ( $each_value ) {
			$this->eaches[$n] = &$retval;
			return $this->_seach(&$this->eaches[$n]);
		}

		if ( $print_value ) {
			if ( is_array($retval) ) {
				foreach ( $retval as $key => $value ) {
					return $this->_sprint($value);
				}
			} else {
				return $this->_sprint($retval);
			}
		} else {
			return $retval;
		}
	}

	public function _sprint($var) {
		if ( is_string($var) ) {
			return print $var;
		} elseif ( isset($var['safe_value']) ) {
			return print $var['safe_value'];
		} elseif ( isset($var['value']) ) {
			return print $var['value'];
		}
		return '';
	}

	public function _snode($var, $prop, $type = 'node') {
		$retval = field_get_items($type, $var, $prop);
    	if ( is_array($retval) ) reset($retval);
    	return $retval;
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

class lolDrupalTaxonomy extends lolDrupalView {
	public $taxonomy;

	public function __construct($taxonomy) {
		$this->taxonomy = $taxonomy;
	}

	public function __call($n, $args = null) {
		$separated = preg_replace('%(?<!^)\p{Lu}%usD', '_$0', $n);
		$lower = mb_strtolower($separated, 'utf-8');
		$parts = explode('_', $lower);
		$print_value = false;
		$check_exists = false;
		if ( $parts[0] == 'get' ) {
			array_shift($parts);
		} elseif ( $parts[0] == 'exists' ) {
			$check_exists = array_shift($parts);
		} elseif ( $parts[0] == 'print' ) {
			$print_value = array_shift($parts);
		}
		$name = implode('_', $parts);
		$retval = null;
		$var_found = false;

		if ( $name == 'url' ) {
			if ( $check_exists ) return true;
			if ( isset($this->taxonomy->_surl) ) {
				$retval = $this->taxonomy->_surl;
			} else {
				$uri = entity_uri('taxonomy_term', $this->taxonomy);
				$this->taxonomy->_surl = url($uri['path']);
				$retval = $this->taxonomy->_surl;
			}
		} else {
			if ( isset($this->taxonomy->$name) ) {
				if ( $check_exists ) return true;
				$var_found = true;
				$retval = $this->taxonomy->$name;
			}
		}

		if ( $print_value ) {
			if ( is_array($retval) ) {
				foreach ( $retval as $key => $value ) {
					return $this->_sprint($value);
				}
			} else {
				return $this->_sprint($retval);
			}
		} else {
			return $retval;
		}
	}
}