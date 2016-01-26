<?php 
/**
* 测试用
*/
class TestAction extends BaseAction
{
	public function index()
	{
		if(IS_POST){
			$data = array('test'=>123);
			$url = $_POST['url'];

			echo '<br>post:<br>';
			print_r($this->post_put_msg($url,true,$data));
			echo '<br>put:<br>';
			print_r($this->post_put_msg($url,false,$data));
			echo '<br>get:<br>';
			print_r($this->get_delete($url,true));
			echo '<br>delete:<br>';
			print_r($this->get_delete($url,false));
		}
		else{
			$this->display();
		}
	}
	
	protected function post_put_msg($url,$is_post,$data)
	{
		$ch = curl_init ();
		// print_r($ch);
		curl_setopt ( $ch, CURLOPT_URL, $url );
		if($is_post)
		{
			curl_setopt ( $ch, CURLOPT_POST, 1 );
		}
		else
		{
		    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT"); /* !!! */  
		    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-type: application/json',"Accept: application/json"));
		    $data = json_encode($data);
		}
		curl_setopt ( $ch, CURLOPT_HEADER, 0 );
		curl_setopt ( $ch, CURLOPT_RETURNTRANSFER, 1 );
		curl_setopt ( $ch, CURLOPT_POSTFIELDS, $data );
		$return = curl_exec ( $ch );
		curl_close ( $ch );
		return $return;
	}

	protected function get_delete($url,$is_get)
	{
		$ch = curl_init ();
		// print_r($ch);
		curl_setopt ( $ch, CURLOPT_URL, $url );
		if($is_get)
		{
			curl_setopt ( $ch, CURLOPT_GET, 1 );
		}
		else
		{
			curl_setopt($ch, CURLOPT_CUSTOMREQUEST,"DELETE");
		}
		curl_setopt ( $ch, CURLOPT_HEADER, 0 );
		curl_setopt ( $ch, CURLOPT_RETURNTRANSFER, 1 );
		$return = curl_exec ( $ch );
		curl_close ( $ch );
		return $return;
	}


}
 ?>