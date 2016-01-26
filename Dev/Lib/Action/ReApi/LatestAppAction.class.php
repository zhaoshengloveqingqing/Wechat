<?php 
/**
* 
*/
class LatestAppAction extends RestApiAction
{
	
	function __construct()
	{
		parent::checkAuth('Booking-order');
	}

	/**
	*url: type/android
	*     type/apple
	**/
	protected function read()
	{
		$input = $this->get;
		$ret = $input == true && $input['type'];
		if($ret)
		{
			switch ($input['type']) {
			case 'android':
				$result = array("id"=>3,"version"=>"1.0","url"=>"http://www.lingzhtech.com/app/v1.0/app.apk");
				$this->success("操作成功",$result);
				break;

			case 'apple':
				$result = array("version"=>"1","url"=>"");
				$this->success("操作成功",$result);
				break;
			
			default:
				$this->error("no version on this");
				break;
			}
		}
		$this->error("no version on this");
		
	}

}
 ?>