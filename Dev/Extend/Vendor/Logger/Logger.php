<?php
require_once(dirname(__FILE__).'/fb.php');

class Logger {
	function __construct() {
		$this->fb = new fb();
	}

	function _write_log($level, $args) {
		$count = count($args);

		if($count == 0) {
			return;
		}

		$msg = $args[0];
		if($count == 1) {
			$this->_log_format($level, $msg);
		}
		else {
			$arr = $args;
			$obj = $arr[1];

			if(gettype($obj) === 'object' ||
				gettype($obj) === 'array') {
				$this->_log_format($level, $msg, $obj, array_splice($arr, 2));
			}
			else {
				$this->_log_format($level, $msg, null, array_splice($arr, 1));
			}
		}
	}

	function _log_format($level, $format, $obj = null, $args = null) {
		if($args == null)
			$str = $format;
		else
			$str = vsprintf($format, $args);

		switch(strtolower($level)) {
		case 'debug':
		case 'trace':
			$this->fb->trace($str);
			break;
		case 'log':
			$this->fb->log($obj, $str);
			break;
		case 'info':
			$this->fb->info($obj, $str);
			break;
		case 'warn':
			$this->fb->warn($obj, $str);
			if(defined(ENVIRONMENT) && ENVIRONMENT != 'production')
				trigger_error($str);
			break;
		case 'error':
			$this->fb->error($obj, $str);
			if(defined(ENVIRONMENT) && ENVIRONMENT != 'production')
				trigger_error($str);
			break;
		}
	}
	function log() {
		$this->_write_log('log', func_get_args());
	}

	function trace() {
		$this->_write_log('trace', func_get_args());
	}

	function info() {
		$this->_write_log('info', func_get_args());
	}

	function warn() {
		$this->_write_log('warn', func_get_args());
	}

	function error() {
		$this->_write_log('error', func_get_args());
	}
}
