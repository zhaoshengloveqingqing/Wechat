<?php

include "qrhelper.php";    

class QRCodeGenerator
{

	var $error_correction_level='Q';  //H>Q>M>L

	var $png_dir = '/customer_imgs/';

	var $relative_url = '/qrcode/';

	var $dest_url = '';

	/*function build($text, $token, $size)
	{
		$matrixPointSize = min(max((int)$size, 1), 10);
		QRcode::png($text, $filename, $error_correction_level, $matrixPointSize, 2);
	}*/

	public function build($text, $prefix, $token, $args = array())
	{
		$matrixPointSize = 5;
		$targetFolder = $this->png_dir.$token.'/d2/';
		if (!is_dir($_SERVER['DOCUMENT_ROOT'].$this->png_dir)) {
			mkdir($_SERVER['DOCUMENT_ROOT'].$this->png_dir);
		}
		if (!is_dir($_SERVER['DOCUMENT_ROOT'].$this->png_dir.$token)) {
			mkdir($_SERVER['DOCUMENT_ROOT'].$this->png_dir.$token);
		}
		if (!is_dir($_SERVER['DOCUMENT_ROOT'].$this->png_dir.$token.'/d2/')) {
			mkdir($_SERVER['DOCUMENT_ROOT'].$this->png_dir.$token.'/d2/');
		}
		$filename = 'qr_'.$prefix.'_'.$matrixPointSize.'_'.md5($text.'|'.$this->error_correction_level.'|'.$matrixPointSize.'|'.$token).'.png';
		$this->dest_url = $targetFolder.$filename;
		Log::write('path:'.$this->dest_url);
		if($args) {
			if(!isset($args['out_file']))
				$args['out_file'] = $_SERVER['DOCUMENT_ROOT'].$targetFolder.$filename;
			create_qr($text, $args);
		}
		else {
			QRcode::png($text, $_SERVER['DOCUMENT_ROOT'].$targetFolder.$filename, $this->error_correction_level, $matrixPointSize, 2);
		}
	}


	public function getUrl()
	{
		return $this->dest_url;
	}
}
?>
