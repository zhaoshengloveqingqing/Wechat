<?php

/**
 * 文本回复
 * */
require_once(COMMON_PATH.'/ServiceHelper.php');
class ImgAction extends UserAction {

    protected function _initialize() {
        parent::_initialize();
        $this->function = 'kefu';
        parent::checkOpenedFunction();
    }

    public function index() {        
        $uid = session('uid');
        $token = session('token');
        
        $db = D('img');       
        $where['uid'] = $uid;
        $where['token'] = $token;
        $where['function'] = 'kefu';
        $where['status'] = 1;
        
        $all_keywords = $db->where($where)->field('keyword')->distinct(true)->select();
        $count = count($all_keywords);
        $page = new Page($count, 50);
        $this->assign('page', $page->show());
        $info=$db->query("SELECT keyword, id,COUNT(*) AS num, title, click, updatetime FROM ( SELECT * FROM __TABLE__  WHERE uid ='".$uid."' AND token ='".$token."' AND function = 'kefu'  AND status = 1  ORDER BY sorts desc ) __TABLE__  group by keyword order by updatetime desc LIMIT ".$page->firstRow.",".$page->listRows);
        
        $this->assign('info', $info);
        
        $this->display();
    }

    public function add() {
        $this->display('edit');
    }

    public function edit() {

        $img_id = $this->_get('id', 'intval');
        $uid = session('uid');
        $token = session('token');
        $ismul =$this->_get('ismul','intval');    //判断是否单图文多图文
      
        $db = D('img');
        $getkwd['id']=$img_id;
        $keyword=$db->where($getkwd)->getField('keyword');     
        
        $where['uid'] = $uid;
        $where['token'] = $token;
        $where['function'] = 'kefu';
        $where['keyword'] = $keyword;
        $where['status'] = 1;        
        
        $img_info = $db->where($where)->order('sorts  desc')->select();
		$itemnum = count($img_info);
		for($i = 0; $i < $itemnum; $i++) {
		    if (empty($img_info[$i]['linktype'])) {
			    if (!empty($img_info[$i]['url'])) {
				    $img_info[$i]['linktype'] = 'normal';
				} else {
				    $img_info[$i]['linktype'] = 'info';
				}
			}
		}
        $this->assign('info', $img_info[0]);
        $this->assign('itemnum', $itemnum);
        $this->assign('info2', $img_info);
        $this->assign('ismul', $ismul);
        $data = array();
        foreach ($img_info as $key => $value) {
            $data['item_' . $key] = $value;
        }
        if($data)
        {   
            $this->assign('db_json', json_encode($data));}
        else {
		    $this->assign('db_json', null);
		}
        $this->assign('services', ServiceHelper::getServices($token));
        $this->display();        
    }

    public function del() {
        $img_id = $this->_get('id', 'intval');
        $uid = session('uid');
        $token = session('token');
        
        $db2=M('img');
        $getkwd['id']=$img_id;
        $kwd=$db2->where($getkwd)->getField('keyword');
        
        $data['status'] = 0;
        $DelID=$db2->where(array('uid' => $uid,'token' => $token,'function' => 'kefu','keyword' => $kwd))->getField('id',true);
        $ret=$db2->where(array('uid' => $uid,'token' => $token,'function' => 'kefu','keyword' => $kwd))->save($data);
        
        if ($ret >= 0) {            
            foreach ($DelID as $del_id) {
            
            M('Keyword')->where(array('pid' => $del_id, 'token' => $token, 'function' => 'kefu', 'module' => 'img'))->delete();
            
           }
           $this->success('操作成功', U(MODULE_NAME . '/index'));
            
        } else {
            $this->error('操作失败', U(MODULE_NAME . '/index'));
        }
    }


    public function upsave() {        
        $data = $_POST;                
        $token = session('token');
        $img = M('img');
        $imgret = 1;
        //如果有要删除的item id
        if($data['delids'])
        {
        foreach ($data['delids'] as $key => $value) 
            {
            $where2['id'] = $value;
            $data['status'] = 0;
            $ret_1 = $img->where($where2)->save($data);
            if ($ret_1 >= 0) {
                $ret_1 = M('Keyword')->where(array('pid' => $value, 'token' => $token, 'function' => 'kefu', 'module' => 'img'))->delete();
            }
            $imgret = $imgret && $ret_1;
            }
        }
        $firstkeyword =$data['items'][item_0]["keyword"];
        $add_data['token'] = session('token');
        $add_data['uid'] = session('uid');
        $add_data['uname'] = session('uname');
        //$add_data['showpic'] = 1;
        $add_data['function'] = $this->function;
        $add_data['detail_display_tmpl'] = 'img_content';
        $add_data['createtime'] = time();
        $add_data['updatetime'] = $add_data['createtime'];
        $add_data['status'] = 1;
        
        foreach ($data['items'] as $key => &$value) 
            {
                if (!$value['id']) 
                    {
                        $add_data['showpic'] =$value['showpic'];
                        $add_data['keyword'] = $firstkeyword;
                        $add_data['type'] = $value['type'];
                        $add_data['text'] = $value['text'];
                        $add_data['pic'] = $value['pic'];
                        $add_data['info'] = $value['info'];
                        $add_data['url'] = $value['url'];
                        $add_data['title'] = $value['title'];
                        $add_data['sorts'] = $value['sorts'];
                        $add_data['linktype'] = $value['linktype'];
                        //info和normal类型的linktype都不是系统模块连接
                        $add_data['service'] = in_array($add_data['linktype'], array('info','normal')) ? '' : $value['service'];

                        $temp = $img->add($add_data);
                        if ($temp) 
                            {
                                $value['id'] = strval($temp);
                             }
                        $imgret = $imgret && $temp;
                    }
                    else 
                        {    
                        
                            $where['id'] = $value['id'];
                            $value['keyword']=$firstkeyword;
                            $value['updatetime'] = time();
                            
                            $imgret = $imgret && $img->where($where)->save($value);
                        }
               }

        if ($imgret == true) {
            $kwds_db = M('keyword');
            $kwd_data['uid'] = session('uid');
            $kwd_data['token'] = session('token');
            $kwd_data['module'] = 'img';
            $kwd_data['function'] = $this->function;

            foreach ($data['items'] as $key => $val) {
                
                $kwds_db->where(array('pid' => $val['id'], 
                                        'token' => $kwd_data['token'], 
                                        'function' => $kwd_data['function'], 
                                        'module' => $kwd_data['module']))->delete();
                $kwd_data['type'] = $val['type'];
                $kwd_data['pid'] = $val['id'];
                $kwd_data['sorts'] = $val['sorts'];
                $keywords = explode(' ', $val['keyword']);
                foreach ($keywords as $vo) {
                    $kwd_data['keyword'] = $vo;
                    $kwds_db->add($kwd_data);                   
                }
            }

            $this->ajaxReturn('操作成功', 'OK', 1);
            
        } else {
            $this->ajaxReturn('操作失败', 'ERROR', 0);
            
        }
    }
}
?>
