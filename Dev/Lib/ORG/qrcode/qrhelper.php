<?php
	require_once(dirname(__FILE__).'/qrlib.php');

	define('DEFAULT_PIXEL_SIZE', 8);
	define('DEFAULT_MARGIN_SIZE', 2);
	define('DEFAULT_INNER_IMAGE_SIZE', 10);
	define('DEFAULT_ERROR_CORRECTION_LEVEL', QR_ECLEVEL_H);

	function get_default($arr, $key, $default = null) {
		if($arr) {
			if(isset($arr[$key])) {
				return $arr[$key];
			}
		}
		return $default;
	}

	function image_type($img) {
		$path_parts = pathinfo($img);
		return $path_parts['extension'];
	}

	function resize_image($file, $out_file, $width, $height = 0) {
		if (extension_loaded('imagick')) {
			$img = new Imagick($file);
			$img->thumbnailImage($width, $height);
			$img->writeImage($out_file);
		}
		if (extension_loaded('gd')) {
			$ext = image_type($file);
			if($ext == 'jpg' || $ext == 'jpeg')
				$src_img=imagecreatefromjpeg($file);
			else
				$src_img=imagecreatefrompng($file);

			$old_x=imageSX($src_img);
			$old_y=imageSY($src_img);
		
			if($height == 0) {
				$height = $old_y * $width / $old_x;
			}

			$dst_img=ImageCreateTrueColor($width,$height);
			imagecopyresampled($dst_img,$src_img,0,0,0,0,$width,$height,$old_x,$old_y);
			$ext = image_type($out_file);
			if($ext == 'jpg' || $ext == 'jpeg')
				imagejpeg($dst_img, $out_file);
			else
				imagepng($dst_img,$out_file);
			imagedestroy($dst_img);
			imagedestroy($src_img);
		}
		return null;
	}

	function image_size($file) {
		if(file_exists($file)) {
			if (extension_loaded('imagick')) {
				$img = new Imagick($file);
				return array(
					$img->getImageWidth(),
					$img->getImageHeight()
				);
			}
			if (extension_loaded('gd')) {
				$ext = image_type($file);
				if($ext == 'jpg' || $ext == 'jpeg')
					$img = imagecreatefromjpeg($file);
				else
					$img = imagecreatefrompng($file);
				return array(imageSX($img), imageSY($img));
			}
		
		}
		return array(0, 0);
	}

	function composite_image($origin, $comp, $out_file) {
		if(file_exists($origin) && file_exists($comp)) {
			$os = image_size($origin);
			$cs = image_size($comp);
	
			
			$w = $os[0];
			$h = $os[1];

			$cw = $cs[0];
			$ch = $cs[1];

			$x = ($w - $cw) / 2;
			$y = ($h - $ch) / 2;

			if (extension_loaded('imagick')) {
				$i = new Imagick($origin);
				$c = new Imagick($comp);
				$i->compositeImage($c,Imagick::COMPOSITE_DEFAULT, $x, $y);
				$i->writeImage($out_file);
				return true;
			}
			if (extension_loaded('gd')) {
				$ext = image_type($origin); // Reading the origin image

				if($ext == 'jpg' || $ext == 'jpeg')
					$im = imagecreatefromjpeg($origin);
				else
					$im = imagecreatefrompng($origin);

				$ext = image_type($comp); // Reading the composite image

				if($ext == 'jpg' || $ext == 'jpeg')
					$overlay = imagecreatefromjpeg($comp);
				else
					$overlay = imagecreatefrompng($comp);

				imagecopymerge($im, $overlay, $x, $y, 0, 0, $cw, $ch, 100);

				$ext = image_type($out_file);
				if($ext == 'jpg' || $ext == 'jpeg')
					imagejpeg($im, $out_file);
				else
					imagepng($im,$out_file);

				imagedestroy($overlay);
				imagedestroy($im);
			}
		}
		return false;
	}

	function create_qr($message, $args = array()) {
		if($args) {
			$out_file = get_default($args, 'out_file', sys_get_temp_dir().'/create_qr_out.png');
			$pixel_size = get_default($args, 'pixel', DEFAULT_PIXEL_SIZE);
			$margin_size = get_default($args, 'margin', DEFAULT_MARGIN_SIZE);
			$comp_file = get_default($args, 'comp');
			$ec_level = get_default($args, 'level', DEFAULT_ERROR_CORRECTION_LEVEL);

			if($comp_file) {
				// We need to resize the compisite file to its right size
				$inner_size = get_default($args, 'inner', DEFAULT_INNER_IMAGE_SIZE);
                $microtime = microtime(true);
				$tmp_inner_file = sys_get_temp_dir().'/'.$microtime.'qr_inner.png';
				$tmp_file = sys_get_temp_dir().'/'.$microtime.'qr.png';

				// Resize the composite file first
				resize_image($comp_file, $tmp_inner_file, $inner_size * $pixel_size);

				// Create the qr code into the temp file 
				QRcode::png($message, $tmp_file, $ec_level, $pixel_size, $margin_size);

				// Composite them together
				composite_image($tmp_file, $tmp_inner_file, $out_file);
			}
			else {
				QRcode::png($message, $out_file, $ec_level, $pixel_size, $margin_size);
			}
			return file_get_contents($out_file);
		}
		else {
			// We do not have any settings, using the default settings
			return QRcode::png($message, false, DEFAULT_ERROR_CORRECTION_LEVEL, DEFAULT_PIXEL_SIZE, DEFAULT_MARGIN_SIZE);
		}
	}
	/*

	if(isset($_GET['default'])) {
		create_qr('http://www.pinet.co');
		exit;
	}

	header('Content-type: image/png');

	if(isset($_GET['resize'])) {
		resize_image('/tmp/logo_pw.png', '/tmp/tmp_out.png', 50);

		echo file_get_contents('/tmp/tmp_out.png');
		exit;
	}

	if(isset($_GET['no'])) {
		echo create_qr('http://www.pinet.co', array(
			'pixel' => 15,
			'margin'=> 1
		));
		exit;
	}


	$result_file = '/tmp/composite_out.png';
	echo create_qr('http://www.baidu.com', array(
		'pixel' => 5,
		'margin'=> 1,
		'inner' => 11,
		'comp' => '/tmp/logo_pw.png',
		'out_file' => $result_file
	));
	 */
