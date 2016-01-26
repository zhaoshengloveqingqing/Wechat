<?php
class OemAction extends BaseAction{
    //关注回复
    public function index(){
        $customer = '';
        if(isset($_GET['o']))
        {
            $customer = $_GET['o'];
        }
        
        switch($customer)
        {
            case 'xuyi':
                $this->display('xuyi');
                break;
            default:
                header('Location: '.C('site_url'));
                break;
        }
    }
}