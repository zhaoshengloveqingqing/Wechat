<?php
class BranchAction extends BaseAction{
    public function index(){
        $agent = $_SERVER['HTTP_USER_AGENT']; 
        if(!strpos($agent,"MicroMessenger")) {
                //echo '此功能只能在微信浏览器中使用';exit;
        }

        $token=$this->_get('token');
        if($token!=false){
                //商家信息
                $addr=M('member_card_contact')
                        ->where(array('token'=>$token))
                        ->order('sort asc')
                        ->select();

                $this->assign('branchlist',$addr);
        }else{
                $this->error('无此信息');
        }
        $this->display();

    }
    
    public function detail(){
        $agent = $_SERVER['HTTP_USER_AGENT']; 
        if(!strpos($agent,"MicroMessenger")) {
                //echo '此功能只能在微信浏览器中使用';exit;
        }

        $token=$this->_get('token');
        $branchId = $this->_get('id');
        if(!empty($token) && !empty($branchId)){
            // 分店信息
            $branches=M('member_card_contact')->where(array('token'=>$token, 'id'=>$branchId))->select();
            if(!empty($branches) && count($branches) > 0) {
                $branch = $branches[0];
                if(!empty($branch['picture'])) {
                    $img = M('raw_image')->find($branch['picture']);
                    $branch['piclink'] = $img['link'];
                }else {
                    $branch['piclink'] = '';
                }
                $branch['description'] = nl2br($branch['description']);
                
                $screenshotLink = "http://api.map.baidu.com/staticimage?".
                        "center=".$branch['longtitude'].",".$branch['latitude'].
                        "&markers=".$branch['longtitude'].",".$branch['latitude'].
                        "&width=270&height=200&zoom=15";
                $this->assign('screenshot', $screenshotLink);
                
                $navigationLink = "http://api.map.baidu.com/marker?location="
                        .$branch['latitude'].','.$branch['longtitude']
                        .'&title='.urlencode($branch['cname'])
                        .'&name='.urlencode($branch['cname'])
                        .'&content='.urlencode($branch['info'])
                        .'&output=html&src=lingzhtech';
                $this->assign('navigationLink', $navigationLink);
                
                $this->assign('branch',$branch);
                $this->display();
                exit;
            }
        }
        
        $this->error('无此分店信息');

    }
}

