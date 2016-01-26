<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

class TemplateDataLoader {
    private $uid;
    private $uname;
    private $token;
    private $tmpl_token;
    
    function __construct($uid, $uname, $token) {
        $this->uid = $uid;
        $this->uname = $uname;
        $this->token = $token;
        $this->tmpl_token = C('TMPL_TOKEN');
    }
    
    public function loadTemplateData($funcName) {
        switch ($funcName) 
        {
            case 'kefu':
                $this->setCustomerServiceDefault();
                break;
            case 'shouye':
                $this->setMicroWebDefault();
                break;
            case 'shangcheng':
                $this->setShopDefault();
                break;
            case 'dingdan':
                $this->setOrderingDefault();
                break;
            case 'panorama':
                $this->setPanoramaDefault();

            default:
                break;
        }
    }
    
    private function setPanoramaDefault()
    {
        $where['token']     = $this->tmpl_token;
        $where['status']    = 1;

        $galleryDb = M('gallery3d');
        $count = $galleryDb->where(array('token' => $this->token))->count();
        if ($count > 0) 
        {
            return;
        }
        
        $tmplGallerys = $galleryDb->where($where)->select();
      
        if($tmplGallerys !== FALSE && $tmplGallerys != NULL) {
            
            /*
            * Copy a gallery in each loop
            */
            
            foreach($tmplGallerys as $gallery) {
                $tmplGalleryId = $gallery['id'];
                
                $galleryData = $gallery;
                unset($galleryData['id']);
                $galleryData['token'] = $this->token;
                $myGalleryId = M('gallery3d')->add($galleryData);
               
                if($myGalleryId !== FALSE) {
                    
                    // get panoramas in template gallery
                    $tmplPanoramas = M('panorama')->where(array('galleryid'=>$tmplGalleryId, 'token'=>$this->tmpl_token, 'status'=>'1'))->select();
                    if($tmplPanoramas !== FALSE && $tmplPanoramas != NULL) {
                        /*
                         * copy one panorama in each loop
                         */
                        foreach($tmplPanoramas as $panorama) {
                            $tmplPanoId = $panorama['id'];
                            
                            $panoramaData = $panorama;
                            unset($panoramaData['id']);
                            $panoramaData['token'] = $this->token;
                            $panoramaData['galleryid'] = $myGalleryId;
                            $myPanoId = M('panorama')->add($panoramaData);
                            
                            if($myPanoId !== FALSE) {
                                // get slices in template panorama
                                $tmplSlices = M('panorama_slices')->where(array('panorama_id'=>$tmplPanoId, 'status'=>'1'))->select();
                                
                                if($tmplSlices !== FALSE && $tmplSlices != NULL) {
                                    /*
                                     * copy one slice in each loop
                                     */
                                    foreach($tmplSlices as $slice) {
                                        $templSliceId = $slice['id'];
                                        
                                        $sliceData = $slice;
                                        unset($sliceData['id']);
                                        $sliceData['panorama_id'] = $myPanoId;
                                        $mySliceId = M('panorama_slices')->add($sliceData);
                                        if($mySliceId == FALSE) {
                                            return;
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }
            // 添加图文消息
            $where['token']     = $this->tmpl_token;
            $where['function']  = 'panorama';
            $where['status']    = 1;

            //复制首页图文
            $imgDb = M('img');
            $count = $imgDb->where(array('token' => $this->token, 'function'=>'panorama'))->count();
            if ($count > 0) 
            {
                //the customer has already  this set.
                return;
            }

            $img = $imgDb->where($where)->find() ;
            if ($img !== FALSE && $img != NULL) 
            {
                
                unset($img['id']);
                $img['uid'] = $this->uid;
                $img['uname'] = $this->uname;
                $img['token'] = $this->token;
                $img['url'] = str_replace($this->tmpl_token, $this->token, $img['url']);
                $imgId = $imgDb->add($img);
                if($imgId !== FALSE) {

                    $kwds_db = M('keyword');
                    $kwd_data['uid'] = $this->uid;  
                    $kwd_data['token'] = $this->token;
                    $kwd_data['module'] = 'img';
                    $kwd_data['type'] = 1; 
                    $kwd_data['function'] = 'panorama';
                    $kwd_data['pid'] = $imgId;
                    $kwd_data['keyword'] = $img['keyword'];    
                    $kwds_db->add($kwd_data);
                }
            }
            else
            {
                return;
            }
        }
        
    }
    
    private function setOrderingDefault() 
    {

        $where['token']     = $this->tmpl_token;
        $where['status']    = 1;

        //复制栏目信息
        $host_db            = M('host');
        $host_list_db       = M('host_list_add');

        $count = $host_db->where(array('token' => $this->token))->count();
        if ($count > 0) 
        {
            //the customer has already  this set.
            return;
        }
        $hosts  = $host_db->where($where)->select();
        if ($hosts) 
        {
            foreach ($hosts as $key => $h) 
            {
                $host_lists  = $host_list_db->where(array('hid'=> $h['id']))->select();

                unset($h['id']);
                $h['token']     = $this->token;
                $new_hid    = $host_db->add($h);

                $kwds_db = M('keyword');
                $kwd_data['uid'] = $this->uid;  
                $kwd_data['token'] = $this->token;
                $kwd_data['module'] = 'merchant';
                $kwd_data['type'] = 1; 
                $kwd_data['function'] = 'dingdan';
                $kwd_data['pid'] = $new_hid;
                $kwd_data['keyword'] = $h['keyword'];    
                $kwds_db->add($kwd_data);

                foreach ($host_lists as $key => $p) 
                {
                    unset($p['id']);
                    $p['hid']       = $new_hid;
                    $p['token']     = $this->token;
                    $host_list_db->add($p);
                }
            }
        }

        
    }


    private function setShopDefault()
    {
        $where['token']     = $this->tmpl_token;
        $where['status']    = 1;

        //复制商城信息
        $shop_db = M('b2c_shop');
        $count = $shop_db->where(array('token' => $this->token))->count();
        if ($count > 0) 
        {
            //the customer has already  this set.
            return;
        }


        $shop = $shop_db->where($where)->find() ;
        if ($shop) 
        {
            unset($shop['shop_id']);
            $shop['token'] = $this->token;
            $shop['update_time'] = time();
            $shop_id = $shop_db->add($shop);

            $kwds_db = M('keyword');
            $kwd_data['uid'] = $this->uid;  
            $kwd_data['token'] = $this->token;
            $kwd_data['module'] = 'portal';
            $kwd_data['type'] = 1; 
            $kwd_data['function'] = 'shangcheng';
            $kwd_data['pid'] = $shop_id;
            $kwd_data['keyword'] = $shop['keyword'];    
            $kwds_db->add($kwd_data);
        }
        else
        {
            return;
        }

        //复制栏目信息
        $category_db    = M('b2c_category');
        $product_db     = M('b2c_product');

        $categories  = $category_db->where($where)->select();

        foreach ($categories as $key => $c) 
        {
            $products  = $product_db->where(array('category_id'=> $c['category_id']))->select();

            unset($c['category_id']);
            $c['token']     = $this->token;
            $c['update_time'] = time();
            $new_cate_id    = $category_db->add($c);
            foreach ($products as $key => $p) 
            {
                unset($p['product_id']);
                $p['category_id']   = $new_cate_id;
                $p['token']         = $this->token;
                $p['update_time']   = time();
                $product_db->add($p);
            }
        }
    }

    private function setMicroWebDefault()
    {
        $where['token']     = $this->tmpl_token;
        $where['function']  = 'shouye';
        $where['status']    = 1;

        //复制首页图文
        $home_db = M('img');
        $count = $home_db->where(array('token' => $this->token, 'function'=>'shouye'))->count();
        if ($count > 0) 
        {
            //the customer has already  this set.
            return;
        }

        $home = $home_db->where($where)->find() ;
        
        if ($home) 
        {
            unset($home['id']);
            $home['uid'] = $this->uid;
            $home['uname'] = $this->uname;
            $home['token'] = $this->token;
            $home['url'] = str_replace($this->tmpl_token, $this->token, $home['url']);
            $homd_db = $home_db->add($home);

            $kwds_db = M('keyword');
            $kwd_data['uid'] = $this->uid;  
            $kwd_data['token'] = $this->token;
            $kwd_data['module'] = 'img';
            $kwd_data['type'] = 1; 
            $kwd_data['function'] = 'shouye';
            $kwd_data['pid'] = $homd_db;
            $kwd_data['keyword'] = $home['keyword'];    
            $kwds_db->add($kwd_data);
        }
        else
        {
            return;
        }
        

        //复制网站模板
        //数据库相应字段默认值

        //复制首页模板设置
        $web_db = M('vweb_setting');
        $setting = $web_db->where($where)->find();
        $setting['token'] = $this->token;
        unset($setting['web_id']);
        $setting_id = $web_db->add($setting);

        //复制flash
        $flash_db = M('Flash');
        $flashes = $flash_db->where($where)->select();

        foreach ($flashes as $key => $t) 
        {
            unset($t['id']);
            $t['token'] = $this->token;
            $flash_id = $flash_db->add($t);
        }

        //复制flash
        $classify_db = M('classify');
        $classifies = $classify_db->where($where)->select();

        $article_db = M('article');

        foreach ($classifies as $key => $t) 
        {
            
            $articles = $article_db->where(array('token'=>$this->tmpl_token, 'c_id'=>$t['id']))->select();
            unset($t['id']);
            $t['token'] = $this->token;
            $classify_id = $classify_db->add($t);
            
            if(is_array($articles)) {
                foreach ($articles as $key => $a) 
                {
                    unset($a['id']);
                    $a['uid'] = $this->uid;
                    $a['token'] = $this->token;
                    $a['c_id'] = $classify_id;
                    $article_db->add($a);
                }
            }

            
        }

        //复制flash
        
        
    }


    private function setCustomerServiceDefault()
    {
        //复制默认回复
        $follow_reply_db = M('areply');

        $count = $follow_reply_db->where(array('token' => $this->token))->count();
        if ($count > 0) 
        {
            //the customer has already  this set.
            return;
        }

        $follow_reply = $follow_reply_db->where(array('token' => $this->tmpl_token))->find();
        if ($follow_reply['ctype'] == 'text' && !empty($follow_reply['keyword'])) 
        {
            $text = M('text')->where(array('id' => $follow_reply['cid'] ))->find();
            unset($text['id']);
            unset($text['click']);
            $text['uid'] = $this->uid;
            unset($text['uname']);
            $text['token'] = $this->token;
            $text['updatetime'] = time();
            $text_id = M('text')->data($text)->add();

            unset($follow_reply['id']);
            $follow_reply['token'] = $this->token;
            $follow_reply['uid'] = $this->uid;
            $follow_reply['cid'] = $text_id;
            $reply_id = $follow_reply_db->data($follow_reply)->add();

            $kwds_db = M('keyword');
            $kwd_data['uid'] = $this->uid;  
            $kwd_data['token'] = $this->token;
            $kwd_data['module'] = 'text';
            $kwd_data['type'] = 1; 
            $kwd_data['function'] = 'kefu';
            $kwd_data['pid'] = $text_id;
            $kwd_data['keyword'] = '9367a975f19a06750b67f719f4f08ceb';    
            $kwds_db->add($kwd_data);
        }

        //复制文本消息
        $text_db = M('text');
        $texts = $text_db->where(array('status'=>1, 'token' => $this->tmpl_token, 'function' => 'kefu' , 'keyword' => array('neq', '9367a975f19a06750b67f719f4f08ceb')))->select();

        foreach ($texts as $key => $t) 
        {
            unset($t['id']);
            $t['token'] = $this->token;
            $t['uid']   = $this->uid;
            $t['uname']   = $this->uname;
            $t['updatetime'] = time();
            $text_id = $text_db->add($t);
            if ($text_id) 
            {
                $keywords = explode(' ',  $t['keyword']);
                $kwds_db = M('keyword');
                $kwd_data['uid']    = $this->uid;
                $kwd_data['token']  = $this->token;
                $kwd_data['module'] = 'text';
                $kwd_data['type']   =   $t['type'];
                $kwd_data['function'] = 'kefu';
                $kwd_data['pid'] = $text_id;
                foreach($keywords as $vo) 
                {
                    $kwd_data['keyword'] = $vo  ;    
                    $kwds_db->add($kwd_data);
                }
            }
        }


        //复制图文消息
        $img_db = M('img');
        $imgs = $img_db->where(array('status'=>1,'token' => $this->tmpl_token, 'function' => 'kefu'))->select();
        foreach ($imgs as $key => $t) 
        {
            unset($t['id']);
            $t['token'] = $this->token;
            $t['uid']   = $this->uid;
            $t['uname']   = $this->uname;
            $t['updatetime'] = time();
            $t['url'] = str_replace($this->tmpl_token, $this->token, $t['url']);
            $img_id = $img_db->add($t);
            if ($img_id) 
            {
                $keywords = explode(' ',  $t['keyword']);
                $kwds_db = M('keyword');
                $kwd_data['uid']    = $this->uid;
                $kwd_data['token']  = $this->token;
                $kwd_data['module'] = 'img';
                $kwd_data['type']   =   $t['type'];
                $kwd_data['function'] = 'kefu';
                $kwd_data['pid'] = $img_id;
                foreach($keywords as $vo) 
                {
                    $kwd_data['keyword'] = $vo  ;    
                    $kwds_db->add($kwd_data);
                }
            }
        }

        //复制匹配不上回复
        $other_db = M('other');
        $others = $other_db->where(array('token' => $this->tmpl_token))->select();
        foreach ($others as $key => $o) 
        {
            unset($o['id']);
            $o['token'] = $this->token;
            $other_db->add($o);
        }
    }
}

