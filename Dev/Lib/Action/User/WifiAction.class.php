<?php
/**
 * 获取Wifi服务设置
**/
class WifiAction extends UserAction
{

    protected function _initialize()
    {
        parent::_initialize();
        $this->function = 'wifi';
        parent::checkOpenedFunction();
    }

    public function index()
    {
        $uid    = session('uid');
        $token  = session('token');

        $db = D('wifi_ap');
        $wifi_where['token']    = $token;
        $wifi_where['status']   = 1;
        $ap = $db->where($wifi_where)->find();
        if ($ap != false) 
        {
            $this->assign('ap',$ap);
        }
        $this->display();
    }

    public function weixin()
    {
        $uid    = session('uid');
        $token  = session('token');

        $db = D('wifi_ap');
        $wifi_where['token']    = $token;
        $wifi_where['status']   = 1;
        $ap = $db->where($wifi_where)->find();
        if ($ap != false) 
        {
            $this->assign('ap',$ap);
            $resp_where['uid']       = $uid;
            $resp_where['token']     = $token;
            $resp_where['function']  = $this->function;
            $resp_where['status']    = 1;
            if ($ap['resp_type'] == 1) 
            {
                $text = M('text')->where($resp_where)->find(); 
                if ($text) 
                {
                    $this->assign('text',$text);
                }
            }
            else
            {
                $img = M('img')->where($resp_where)->find(); 
                if ($img) 
                {
                    $this->assign('img',$img);
                }
            }
        }
        $this->display();
    }



    public function setApInfo() 
    {
        $uid    = session('uid');
        $token  = session('token');

        $wifi_db = D('wifi_ap');
        $wifi_where['token']    = $token;
        $wifi_where['status']   = 1;
        $ap = $wifi_db->where($wifi_where)->find();

        $brand = trim($_POST['brand']);

        $wifi_data = array();
        if (strcasecmp($brand,'witown') == 0) 
        {
        	$wifi_data['merchant_id']   = $_POST['merchant_id'];
        	$wifi_data['merchant_name'] = $_POST['merchant_name'];
        	$wifi_data['type'] 			= 1;
        }
        else if (strcasecmp($brand,'secnet') == 0) 
        {
        	$wifi_data['type'] 			= 2;
        } 
        else
        {
        	$this->ajaxReturn("更新失败", "ERROR", 0);
        }


        $ret = false;
        if ($ap == false) 
        {
            $wifi_data['token']    = $token;
            $wifi_data['status']   = 1;
            $ret = $wifi_db->add($wifi_data);
        }
        else 
        {
            $ret = $wifi_db->where($wifi_where)->save($wifi_data);
        }

        if ($ret)
        {
            $this->ajaxReturn("更新成功", "OK", 1);
        } 
        else  
        {
            $this->ajaxReturn("更新失败", "ERROR", 0);
        }
    }

    //update or insert
    public function setResponse() 
    {
        $uid    = session('uid');
        $token  = session('token');

        $wifi_db = M('wifi_ap');
        $wifi_where['token']    = $token;
        $wifi_where['status']   = 1;
        $ap = $wifi_db->where($wifi_where)->find();

        if (empty($ap))
        {
            $this->error('请先设置“路由器设置”,配置后记得保存。',U(MODULE_NAME.'/index'));
        }

        $resp_type = $this->_post('resp_type','intval',1);

        $where['token']     = $token;
        $where['uid']       = $uid;
        $where['function']  = $this->function;
        $where['status']    = 1;

        if ($resp_type == 1) 
        {
            $text_db = M('text');
            $text = $text_db->where($where)->find();

            $data['keyword']    = $_POST['keyword'];
            $data['text']       = $_POST['textContent'];  //文章摘要
            $data['updatetime'] = time();
            $data['type']       = $_POST['type'];

            if ($text == false) 
            {
                // 添加新回复 
                $data['token']      = $token;
                $data['uid']        = $uid;
                $data['uname']      = session('uname');
                $data['status']     = 1;
                $data['function']   = $this->function;
                $data['createtime'] = $data['updatetime'];
                $text_id = $text_db->add($data);
                if ($text_id) 
                {
                    $kwds_db = M('keyword');
                    $kwds_db->where(array('token'=>$token ,'function'=>$this->function))->delete();
                    $kwd_data['uid']    = $uid;
                    $kwd_data['token']  = $token;
                    $kwd_data['type']   = $_POST['type'];
                    $kwd_data['module'] = 'text';
                    $kwd_data['function'] = $this->function;
                    $kwd_data['pid'] = $text_id;
                    $keywords = explode(' ',  $_POST['keyword']);
                    foreach($keywords as $vo) 
                    {
                        $kwd_data['keyword'] = $vo;    
                        $kwds_db->add($kwd_data);
                    }
                    $wifi_db->where($wifi_where)->save(array('resp_type'=>1));
                    $this->success('操作成功',U(MODULE_NAME.'/index'));
                } 
                else 
                {
                    $this->error('添加失败',U(MODULE_NAME.'/index'));
                }
            } 
            else 
            {

                $ret = $text_db->where($where)->save($data);
                if ($ret !== false) 
                {
                    $kwds_db = M('keyword');
                    
                    $kwds_db->where(array('token'=>session('token') ,'function'=>$this->function))->delete();
                    $kwd_data['uid']        = session('uid');
                    $kwd_data['token']      = session('token');
                    $kwd_data['module']     = 'text';
                    $kwd_data['type']       = $_POST['type'];
                    $kwd_data['function']   = $this->function;
                    $kwd_data['pid']        = $text['id'];
                    $keywords = explode(' ',  $_POST['keyword']);
                    foreach($keywords as $vo) 
                    {
                        $kwd_data['keyword'] = $vo;
                        $kwds_db->add($kwd_data);
                    }
                    $wifi_db->where($wifi_where)->save(array('resp_type'=>1));
                    $this->success('操作成功',U(MODULE_NAME.'/index'));
                } 
                else 
                {
                    $this->error('保存失败',U(MODULE_NAME.'/index'));
                }
            }
        }
        else if ($resp_type == 2 || $resp_type == 3) 
        {
            $data['keyword'] = $_POST['keyword'];
            $data['type']   = $_POST['type'];
            $data['text']   = $_POST['imgContent'];
            $data['pic']    = $_POST['pic'];
            $data['info']   = $_POST['info'];
            $data['title']  = $_POST['title'];
            $data['updatetime'] = time();

            $img_db = M('img');
            $img = $img_db->where($where)->find();
            if ($img) 
            {
                $ret = $img_db->where($where)->data($data)->save();
                if ($ret !== false) 
                {
                    $kwds_db = M('keyword');
                    $kwds_db->where(array('token'=>$token, 'function'=>$this->function))->delete();
     
                    $kwd_data['uid']        = $uid;
                    $kwd_data['token']      = $token;
                    $kwd_data['module']     = 'img';
                    $kwd_data['type']       = $_POST['type'];
                    $kwd_data['function']   = $this->function;
                    $kwd_data['pid']        = $img['id'];
                    $keywords = explode(' ',  $_POST['keyword']);
                    foreach($keywords as $vo) 
                    {
                        $kwd_data['keyword'] = $vo;
                        $kwds_db->add($kwd_data);
                    }
                    $wifi_db->where($wifi_where)->save(array('resp_type'=>$resp_type));
                    $this->success('操作成功',U(MODULE_NAME.'/index'));
                } 
                else 
                {
                    $this->error('保存失败',U(MODULE_NAME.'/index'));
                }
            }
            else
            {
                $data['token']      = $token;
                $data['uid']        = $uid;
                $data['showpic']    = 1; 
                $data['function']   = $this->function;
                $data['createtime'] = $data['updatetime'];
                $data['status']     = 1;

                $img_id = $img_db->add($data);
                if ($img_id) 
                {
                    $kwds_db = M('keyword');
                    $kwds_db->where(array('token'=>session('token') ,'function'=>$this->function))->delete();
                    
                    $kwd_data['uid']    = $uid;
                    $kwd_data['token']  = $token;
                    $kwd_data['type']   = $_POST['type'];
                    $kwd_data['module'] = 'img';
                    $kwd_data['function'] = $this->function;
                    $kwd_data['pid']    = $img_id;
                    $keywords = explode(' ',  $_POST['keyword']);
                    foreach($keywords as $vo) 
                    {
                        $kwd_data['keyword'] = $vo;    
                        $kwds_db->add($kwd_data);
                    }
                    $wifi_db->where($wifi_where)->save(array('resp_type'=>2));
                    $this->success('操作成功',U(MODULE_NAME.'/index'));
                } 
                else 
                {
                    $this->error('添加失败',U(MODULE_NAME.'/index'));
                }
            }
        }
    } 
}
?>
