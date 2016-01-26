<?php
class UcAction extends Action{
	
	function r() {
		header('location:http://user.pinet.co/api/login?'.http_build_query($_GET));
	}
	
	function o() {
		$args = $_GET;
		$type = $args['uc_oauth_type'];
		$app_id = $args['uc_app_id'];
		unset($args['uc_oauth_type']);
		unset($args['uc_app_id']);
		header("location:http://user.pinet.co/oauth/session/$type/$app_id?".http_build_query($args));
	}
}