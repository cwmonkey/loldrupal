<?php

class lolView {
	public $vars;
	public $lolVars = array();
	public $className = 'lolView';

	public function __construct($vars) {
		$this->vars = $vars;
	}

	public function __call($name, $args) {
		list($action, $names) = $this->lolNames($name);

		$action = ucfirst($action);
		$method = 'lolAction' . $action;

		$args = array($args);
		array_unshift($args, $name);
		array_unshift($args, $names);
		if ( method_exists($this, $method) ) {
			return call_user_func_array(array($this, $method), $args);
		}

		return null;
	}

	public function lolActionGet($keys, $name, $args = null) {
		if ( isset($this->lolVars[$name]) ) return $this->lolVars[$name];

		foreach ( $keys as $key ) {
			if ( is_object($this->vars) ) {
				if ( isset($this->vars->$key) ) return $this->lolVars[$name] = new $this->className($this->vars->$key);
			} elseif ( is_array($this->vars) ) {
				if ( isset($this->vars[$key]) ) return $this->lolVars[$name] = new $this->className($this->vars[$key]);
			}
		}

		return null;
	}

	public function lolActionExists($keys, $name, $args = null) {
		if ( isset($this->lolVars[$name]) ) return true;

		foreach ( $keys as $key ) {
			if ( is_object($this->vars) ) {
				if ( isset($this->vars->$key) ) return true;
			} elseif ( is_array($this->vars) ) {
				if ( isset($this->vars[$key]) ) return true;
			}
		}

		return false;
	}

	public function lolNames($name) {
		$separated = preg_replace('%(?<!^)\p{Lu}%usD', '_$0', $name);
		$lower = mb_strtolower($separated, 'utf-8');
		$parts = explode('_', $lower);
		$action = array_shift($parts);
		$new_name = implode('_', $parts);

		return array($action, array($new_name));
	}

	public function __toString() {
		$retval = $this->lolString($this->vars);
		return $retval ? $retval : '';
	}

	public function lolString($val) {
		if ( is_array($val) && isset($val[0]) ) {
			return $this->lolString($val[0]);
		} elseif ( is_array($val) && isset($val['safe_value']) ) {
			return $val['safe_value'];
		} else {
			return $val;
		}
	}
}

class lolDrupalView extends lolView {
	public $className = 'lolDrupalView';

	public function lolActionGet($keys, $name, $args = null) {
		$retval = parent::lolActionGet($keys, $name, $args = null);

		if ( $retval === null && is_array($this->vars) && isset($this->vars['node']) ) {
			foreach ( $keys as $key ) {
				if ( isset($this->vars['node']->$key) ) {
					if ( is_string($this->vars['node']->$key) ) {
						$var = $this->vars['node']->$key;
					} else {
						$var = $this->_snode($this->vars['node'], $key);
					}

					return $this->lolVars[$name] = new $this->className($var);
				}
			}
		}

		return $retval;
	}

	public function getImgTag($style = null) {
		return $this->_simage($this->vars, $style);
	}

	public function _simage($var, $style = null) {
		if ( isset($var['file'])  ) {
			$image = (array)$var['file'];
		} elseif ( isset($var['uri'])  ) {
			$image = $var;
		} elseif ( isset($var['type']) && $var['type'] == 'image') {
			$image = $var;
		} elseif ( isset($var[0]) && isset($var[0]['file']) ) {
			$image = (array)$var[0]['file'];
		} elseif ( isset($var[0]) && isset($var[0]['fid']) && isset($var[0]['uri']) ) {
			$image = $var[0];
		} elseif ( isset($var[0]) && isset($var[0]['fid']) ) {
			$image = file_load($var[0]['fid']);
			$image = (array)$image;
		} elseif ( isset($var[0]) ) {
			$image = $var[0];
		} else {
			return '';
		}

		if ( $style ) {
			if ( is_array($style) && isset($style[0]['settings']) ) {
				$settings = unserialize($style[0]['settings']);
				$style = $settings['image_style'];
			}

			if ( !isset($image['path']) ) {
				$image['path'] = $image['uri'];
			}

			$image['style_name'] = $style;
			$image['getsize'] = TRUE;

			if ( isset($var[0]['file']) && $alt = _snode($var[0]['file'], 'field_media_alt', 'file') ) {
				$image['alt'] = _s($alt);
			}

			if ( !$style ) {
				return theme('image', $image);
			}

			return theme('image_style', $image);
		} else {
			if ( !isset($image['path']) ) {
				$image['path'] = file_create_url($image['uri']);
			}

			return theme('image', $image);
		}
	}

	public function each() {
		return $this->_seach(&$this->vars);
	}

	public function _seach(&$var) {
		if ( !is_array($var) ) return null;

		list($k, $ea) = each($var);

		if ( !$ea ) {
			reset($var);
			return $ea;
		}

		if ( isset($ea['node']) ) {
			return array($k, new $this->className($ea['node']));
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
			$uri = entity_uri('taxonomy_term', $taxonomy_term);
			$taxonomy_term->url = url($uri['path']);
			$ea['_staxonomy_term'] = new $this->className($taxonomy_term);
			return array($k, $ea['_staxonomy_term']);
		}

		if ( is_array($ea) && count($ea) < 3 && isset($ea['nid']) ) {
			$ea = node_load($ea['nid']);
		}

		return array($k, new $this->className($ea));
	}

	public function lolNames($name) {
		list($action, $names) = parent::lolNames($name);
		$names[] = 'field_' . $names[0];

		return array($action, $names);
	}

	public function _snode($var, $prop, $type = 'node') {
		$retval = field_get_items($type, $var, $prop);
    	if ( is_array($retval) ) reset($retval);
    	return $retval;
	}
}