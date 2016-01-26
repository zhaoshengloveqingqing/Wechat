<?php
class CarAction extends UserAction
{
    protected function _initialize()
    {
        $this->function = 'car';
        $this->token = $_SESSION['token']; 

        parent::_initialize();
        parent::checkOpenedFunction();
    }
    
    public function index() {
        $brand_count   = M('car_brand')->where(array('token'=>$this->token, 'status'=>1))->count();
        $Page   = new Page($brand_count,50);
        $show   = $Page->show();
        $brands   = M('car_brand')->where(array('token'=>$this->token, 'status'=>1))
                ->order('sequence asc, id desc')->limit($Page->firstRow.','.$Page->listRows)->select();
        $this->assign('brands', $brands);
        $this->assign('page', $show);
        $this->display();
    }
    public function brand_del($id) {
        $ok = M('car_brand')->where(array('id'=>$id, 'token'=>$this->token, 'status'=>1))->setField('status', 0);
        if($ok == false) {
            $this->error('删除品牌失败！');
        }else {
            $this->success('删除品牌成功！');
        }
    }
    public function brand_edit($id) {
        if(IS_POST) {
            $brandId = $_POST['id'];
            $_POST['update_time'] = time();
            $brand = M('car_brand')->where(array('id'=>$brandId, 'status'=>1, 'token'=>$this->token))->find();
            if(!empty($brand)){
                M('car_brand')->where(array('id'=>$brandId, 'status'=>1, 'token'=>$this->token))->save($_POST);
                //更新关键词表
                M('keyword')->where(array('id'=>$brand['kwd_id']))->setField('keyword', $_POST['keyword']);
                $this->success('品牌信息更新成功！');
            }else {
                $this->error('更新失败！请重试！');
            }
        }else{
            $brand = M('car_brand')->where(array('id'=>$id, 'status'=>1, 'token'=>$this->token))->find();
            $this->assign('brand', $brand);
            $this->display('brand_add');
        }
    }
    public function brand_add() {
        if(IS_POST) {
            $_POST['token'] = $this->token;
            $_POST['status'] = 0; // 初始为不可用
            $_POST['create_time'] = time();
            $_POST['update_time'] = time();
            $brandId = M('car_brand')->add($_POST);
            
            if($brandId!== FALSE) {
                $kwds_db = M('keyword');
                $kwd_data['uid'] = session('uid');
                $kwd_data['token'] = $this->token;
                $kwd_data['type'] = 1;
                $kwd_data['module'] = 'Car';
                $kwd_data['function'] = $this->function;
                $kwd_data['pid'] = $brandId;
                $kwd_data['keyword'] = $_POST['keyword']; 
                $kwd_data['status'] = 1;
                
                $kwdId = $kwds_db->add($kwd_data);
                
                if($kwdId !== false) {
                    $updateData['kwd_id'] = $kwdId;
                    $updateData['status'] = 1; // 设置该品牌为可用
                    M('car_brand')->where(array('id'=>$brandId))->save($updateData);
                    
                    $this->success('品牌添加成功！', U('Car/index'));
                }else {
                    $this->error('品牌添加失败！请刷新重试！');
                }
            }else {
                $this->error('品牌添加失败！请刷新重试！');
            }
        }else {
            $this->display();
        }
    }
    
    public function series(){
        if(isset($_GET['brand'])) {
            $brand_id = intval($_GET['brand']);
        }
        
        $where = array('tp_car_series.token'=>$this->token, 'tp_car_series.status'=>1, 'tp_car_brand.status'=>1);
        if(!empty($brand_id)) {
            $where['brand_id'] = $brand_id;
        }
        
        $series_count = M('car_series')->join('inner join tp_car_brand on tp_car_series.brand_id = tp_car_brand.id')
                ->where($where)
                ->count();
        $Page   = new Page($series_count,50);
        $show   = $Page->show();
        
        $series   = M('car_series')->join('inner join tp_car_brand on tp_car_series.brand_id = tp_car_brand.id')
                ->where($where)
                ->order('tp_car_series.sequence asc, tp_car_series.id desc')
                ->limit($Page->firstRow.','.$Page->listRows)
                ->field('tp_car_series.*, tp_car_brand.name brand_name')
                ->select();
        
        $brands = M('car_brand')->where(array('status'=>1, 'token'=>$this->token))->field('id, name')->select();
        
        $this->assign('brands', $brands);
        $this->assign('page', $show);
        $this->assign('series', $series);
        $this->display();
    }
    
    public function series_add() {
        if(IS_POST) {
            $data = $_POST;
            $data['token'] = $this->token;
            $data['status'] = 1; // 初始为不可用
            $data['create_time'] = time();
            $data['update_time'] = time();
            
            $seriesId = M('car_series')->add($data);
            if($seriesId !== FALSE) {
                $this->success('添加车系成功！', U('Car/series'));
            }else {
                $this->error('服务器忙！请稍后重试！');
            }
        }else {
            $brands = M('car_brand')->where(array('token'=>$this->token, 'status'=>1))->field('id, name')->select();
            $this->assign('brands', $brands);
            $this->display();
        }
    }
    
    public function series_edit() {
        if(IS_POST) {
            if(!isset($_POST['id'])) {
                $this->error('非法操作！');
            }
            
            $seriesId = intval($_POST['id']);
            $cnt = M('car_series')->where(array('token'=>$this->token, 'status'=>1, 'id'=>$seriesId))->count();
            if(!empty($cnt)) {
                $_POST['update_time'] = time();
                $ok = M('car_series')->where(array('token'=>$this->token, 'status'=>1, 'id'=>$seriesId))->save($_POST);
                if($ok !== FALSE) {
                    $this->success('车系更新成功！');
                }else {
                    $this->error('更新失败！请刷新重试！');
                }
            }
        }else {
            if(!isset($_GET['id'])) {
                $this->error('非法操作！');
            }
            $seriesId = intval($_GET['id']);
            if($seriesId <= 0) {
                $this->error('非法操作！');
            }
            
            $series = M('car_series')->where(array('token'=>$this->token, 'status'=>1, 'id'=>$seriesId))->find();
            if(empty($series) || count($series) < 0) {
                $this->error('非法操作！');
            }
            
            $brands = M('car_brand')->where(array('token'=>$this->token, 'status'=>1))->field('id, name')->select();
            $this->assign('brands', $brands);
            
            $this->assign('series', $series);
            $this->display('series_add');
        }
    }
    
    public function series_del($id) {
        $seriesId  = intval($id);
        if(empty($seriesId) || $seriesId <=0 ) {
            $this->error('非法操作！');
        }
        
        $ok = M('car_series')->where(array('token'=>$this->token, 'status'=>1, 'id'=>$seriesId))->setField('status', 0);
        if($ok !== FALSE) {
            $this->success('删除车系成功！');
        }else{
            $this->error('服务器忙！请稍后重试！');
        }
    }
    
    public function model() {
        if(isset($_GET['brand'])) {
            $brand_id = intval($_GET['brand']);
        }
        
        if(isset($_GET['series'])) {
            $series_id = intval($_GET['series']);
        }
        
        if(!empty($brand_id) && !empty($series_id)) {
            $where['tp_car_models.car_series'] = $series_id;
            $where['tp_car_models.car_brand'] = $brand_id;
        }
        
        $where['tp_car_models.status'] = 1;
        $where['s.status'] = 1;
        $where['tp_car_models.token'] = $this->token;
        $where['b.status'] = 1;
        $model_count = M('car_models')->
                join('inner join tp_car_series s on tp_car_models.car_series=s.id inner join tp_car_brand b on tp_car_models.car_brand=b.id')
                ->where($where)->count();
        $Page   = new Page($model_count, 50);
        $show   = $Page->show();
        
        $models = M('car_models')->join('inner join tp_car_series s on tp_car_models.car_series=s.id inner join tp_car_brand b on tp_car_models.car_brand=b.id')
                ->where($where)
                ->limit($Page->firstRow.','.$Page->listRows)
                ->order('tp_car_models.sequence asc, tp_car_models.id asc')
                ->field('tp_car_models.*, b.name brand_name, s.name series_name')
                ->select();
        
        $this->assign('cs', $this->constructBrandSeriesCS());
        $this->assign('page', $show);
        $this->assign('models', $models);
        $this->display();
    }
    
    public function model_add() {
        if(IS_POST){
            $data = $_POST;
            // check brand and series
            $brandId = intval($data['car_brand']);
            $seriesId = intval($data['car_series']);
            
            $cnt = M('car_series')->where(array('token'=>$this->token, 'status'=>1, 'id'=>$seriesId, 'brand_id'=>$brandId))->count();
            if($cnt == FALSE || $cnt <= 0) {
                $this->error('请选择车牌和车系！');
            }
            
            $data['create_time'] = time();
            $data['update_time'] = time();
            $data['status'] = 1;
            $data['token'] = $this->token;
            
            $data['pic_id_list'] = serialize($data['pic_id_list_input']);
            
            $modelId = M('car_models')->add($data);
            if($modelId == FALSE) {
                Log::record('Fail to add car_model: '.$this->token.' '.$brandId.' '.$seriesId.' '.$data['name']);
                $this->error('服务器忙，请稍后重试！');
            }
            
            $this->success('车型添加成功！', U('Car/model'));
            
        }else{
            $this->assign('car_box_options', C('car_box_options'));
            $this->assign('cs', $this->constructBrandSeriesCS());
            $this->display();
        }
    }
    
    private function constructBrandSeriesCS() {
        $active_series = M('car_series')->join('inner join tp_car_brand b on tp_car_series.brand_id=b.id')
                ->where(array('b.status'=>1, 'tp_car_series.status'=>1, 'b.token'=>$this->token))
                ->field('b.name brand_name, b.id brand_id, tp_car_series.id series_id, tp_car_series.name series_name')
                ->select();

        // construct brand&series cascading select control content
        $brand2series = array();
        // ui上仅显示存在的
        foreach($active_series as $s) {
            $brandId = $s['brand_id'];
            // series
            if(!array_key_exists($brandId, $brand2series)) {
                $brand2series[$brandId] = array();
            }
            array_push($brand2series[$brandId], $s);
        }

        // cascading select
        $cs = array();
        array_push($cs, '请选择-0$请选择-0');
        foreach($brand2series as $b2s) {
            // series under a single brand
            $seriesOfBrand = array();
            foreach($b2s as $slist) {
                array_push($seriesOfBrand, $slist['series_name'].'-'.$slist['series_id']);
            }

            array_push($cs, $slist['brand_name'].'-'.$slist['brand_id'].'$'.join(',', $seriesOfBrand));
        }
        
        return join('#', $cs);
    }
    
    public function model_edit() {
         if(IS_POST) {
            if(!isset($_POST['id'])) {
                $this->error('非法操作！');
            }
            
            $modelId = intval($_POST['id']);
            $cnt = M('car_models')->where(array('token'=>$this->token, 'status'=>1, 'id'=>$modelId))->count();
            if(!empty($cnt)) {
                $_POST['update_time'] = time();
                $_POST['pic_id_list'] = serialize($_POST['pic_id_list_input']);
                $ok = M('car_models')->where(array('token'=>$this->token, 'status'=>1, 'id'=>$modelId))->save($_POST);
                if($ok !== FALSE) {
                    $this->success('车型更新成功！');
                }else {
                    $this->error('更新失败！请刷新重试！');
                }
            }
        }else {
            if(!isset($_GET['id'])) {
                $this->error('非法操作！');
            }
            $modelId = intval($_GET['id']);
            if($modelId <= 0) {
                $this->error('非法操作！');
            }
            
            $model = M('car_models')->where(array('token'=>$this->token, 'status'=>1, 'id'=>$modelId))->find();
            if(empty($model) || count($model) <= 0) {
                $this->error('非法操作！');
            }
            
            // get img id list
            $pics = array();
            $pic_id_list =  unserialize($model['pic_id_list']);
            $model['pic_id_list_input'] = $pic_id_list;
            if(!empty($pic_id_list) && count($pic_id_list) > 0) {
                $pics = M('raw_image')->where(array('token'=>$this->token, array('in', $pic_id_list)))->select();
            }
            
            $this->assign('model', $model);
            $this->assign('pics', $pics);
            $this->assign('car_box_options', C('car_box_options'));
            $this->assign('cs', $this->constructBrandSeriesCS());
            $this->display('model_add');
        }
    }
    
    public function model_del($id) {
        $modelId  = intval($id);
        if(empty($modelId) || $modelId <=0 ) {
            $this->error('非法操作！');
        }
        
        $ok = M('car_models')->where(array('token'=>$this->token, 'status'=>1, 'id'=>$modelId))->setField('status', 0);
        if($ok !== FALSE) {
            $this->success('删除车型成功！');
        }else{
            $this->error('服务器忙！请稍后重试！');
        }
    }
    
    public function sales() {
        $sales = M('car_sales')->where(array('token'=>$this->token, 'status'=>1))->order('sequence asc, id asc')->select();
        foreach($sales as &$sale) {
            $saleType = array();
            if($sale['pre_sale'] == 1) {
                array_push($saleType, '售前');
            }
            if($sale['post_sale'] == 1) {
                array_push($saleType, '售后');
            }
            $sale['type'] = join('/', $saleType);
        }
        
        $this->assign('sales', $sales);
        $this->display();
    }
    
    public function sales_add() {
        if(IS_POST) {
            $data = $_POST;
            $data['create_time'] = time();
            $data['update_time'] = time();
            $data['status'] = 1;
            $data['token'] = $this->token;
            
            $preSale= intval($_POST['pre_sale']);
            if(empty($preSale)) {
                $data['pre_sale'] = 0;
            }else{
                $data['pre_sale'] = 1;
            }
            
            $postSale= intval($_POST['post_sale']);
            if(empty($postSale)) {
                $data['post_sale'] = 0;
            }else{
                $data['post_sale'] = 1;
            }
            
            $id = M('car_sales')->add($data);
            if($id == FALSE) {
                $this->error('服务器忙！请稍后重试！');
            }else{
                $this->success('销售添加成功！', U('Car/sales'));
            }
            
        }else{
            $this->display();
        }
    }
    
    public function sales_edit() {
        if(IS_POST) {
            $sale_id = intval($_POST['id']);
            if(empty($sale_id)) {
                $this->error('非法请求!');
            }
            
            
            $where = array('token'=>$this->token, 'id'=>$sale_id, 'status'=>1);
            $sale = M('car_sales')->where($where)->find();
            if(empty($sale) || count($sale) <= 0) {
                $this->error('非法请求!');
            }
            
            $data = $_POST;
            $preSale= intval($_POST['pre_sale']);
            if(empty($preSale)) {
                $data['pre_sale'] = 0;
            }else{
                $data['pre_sale'] = 1;
            }
            
            $postSale= intval($_POST['post_sale']);
            if(empty($postSale)) {
                $data['post_sale'] = 0;
            }else{
                $data['post_sale'] = 1;
            }
            
            $data['update_time'] = time();
            
            $ok = M('car_sales')->where($where)->save($data);
            if($ok == FALSE) {
                $this->error('保存失败！');
            }else{
                $this->success('保存成功！');
            }
        }else{
            $sale_id = intval($_GET['id']);
            if(empty($sale_id)) {
                $this->error('非法请求!');
            }

            $sale = M('car_sales')->where(array('token'=>$this->token, 'id'=>$sale_id, 'status'=>1))->find();
            if(empty($sale) || count($sale) <= 0) {
                $this->error('非法请求!');
            }

            $this->assign('sale', $sale);
            $this->display('sales_add');
        }
    }

    public function sales_del($id) {
        $saleId  = intval($id);
        if(empty($saleId) || $saleId <=0 ) {
            $this->error('非法操作！');
        }
        
        $ok = M('car_sales')->where(array('token'=>$this->token, 'status'=>1, 'id'=>$saleId))->setField('status', 0);
        if($ok !== FALSE) {
            $this->success('删除销售成功！');
        }else{
            $this->error('服务器忙！请稍后重试！');
        }
    }
    
    
    // reserve_drive
    public function rdrive() {
        $where['token'] = $this->token;
        $where['status'] = 1;
        $rdrives = M('car_rdrive')->where($where)->select();
        $this->assign('rdrives', $rdrives);
        $this->display();
    }
    public function rdrive_del($id) {
        $rdriveId  = intval($id);
        if(empty($rdriveId) || $rdriveId <=0 ) {
            $this->error('非法操作！');
        }
        
        $ok = M('car_rdrive')->where(array('token'=>$this->token, 'status'=>1, 'id'=>$rdriveId))->setField('status', 0);
        if($ok !== FALSE) {
            $this->success('删除成功！');
        }else{
            $this->error('服务器忙！请稍后重试！');
        }
    }
    public function rdrive_edit() {
        if(IS_POST) {
            $rdriveId = intval($_POST['id']);
            if(empty($rdriveId) || $rdriveId <= 0) {
                $this->error('非法操作！');
            }
            
            $data = $_POST;
            // constraints
            $settingType = intval($data['setting_type']);
            if($settingType == 1) {
                // time constrain
                $data['start_time'] = strtotime($data['starttime']);
                $data['end_time'] = strtotime($data['endtime']);
                $data['upperbound'] = NULL;
            }else{
                $data['start_time'] = NULL;
                $data['end_time'] = NULL;
            }
             // default columns
            $default_show_cols = array();
            foreach($_POST as $pk => $pv) {
                if(strpos($pk, 'default_show_') == 0 && $pv == 1) {
                    $colname = substr($pk, strlen('default_show_'));
                    array_push($default_show_cols, $colname);
                }
            }
            $data['default_col_show'] = serialize($default_show_cols); //join("|", $default_show_cols);
            
            // single text columns
            $text_cols = array();
            $text_text = $_POST['text_text'];
            $text_placeholder = $_POST['text_placeholder'];
            for($i = 0; $i<count($text_text); $i ++) {
                if(!empty($text_text[$i])) {
                    array_push($text_cols, array($text_text[$i], $text_placeholder[$i]));
                    //array_push($text_cols, $text_text[$i].'$$'.$text_placeholder[$i]);
                }
            }
            $data['text_cols'] = serialize($text_cols);//join("###", $text_cols);
            
            // select columns
            $select_cols = array();
            $select_text = $_POST['select_text'];
            $select_placeholder = $_POST['select_placeholder'];
            for($i=0; $i<count($select_text); $i ++) {
                if(!empty($select_text[$i])) {
                    array_push($select_cols, array($select_text[$i], $select_placeholder[$i]));
                    //array_push($select_cols, $select_text[$i].'$$'.$select_placeholder[$i]);
                }
            }
            $data['select_cols'] = serialize($select_cols);//join("###", $select_cols);
            
            $data['update_time'] = time();
            
            $rdrive = M('car_rdrive')->where(array('id'=>$rdriveId, 'status'=>1, 'token'=>$this->token))->find();
            if(!empty($rdrive)){
                M('car_rdrive')->where(array('id'=>$rdriveId, 'status'=>1, 'token'=>$this->token))->save($data);
                //更新关键词表
                M('keyword')->where(array('id'=>$rdrive['kwd_id']))->setField('keyword', $_POST['keyword']);
                $this->success('更新成功！');
            }else {
                $this->error('更新失败！请重试！');
            }
        }
        else {
            $rdriveId = intval($_GET['id']);
            if(empty($rdriveId) || $rdriveId <= 0) {
                $this->error('非法操作！');
            }
            
            $where['id'] = $rdriveId;
            $where['token'] = $this->token;
            $where['status'] = 1;
            $rdrive = M('car_rdrive')->where($where)->find();
            if(empty($rdrive)) {
                $this->error('非法操作！');
            }
            
            if(!empty($rdrive['default_col_show'])) {
                $this->assign('default_col_show', unserialize($rdrive['default_col_show']));
            }
            $text_cols = unserialize($rdrive['text_cols']);
            if(empty($text_cols)){
                $this->assign('text_cols', array(array()));
            }else{
                $this->assign('text_cols', $text_cols);
            }
            $select_cols = unserialize($rdrive['select_cols']);
            if(empty($select_cols)) {
                $this->assign('select_cols', array(array()));
            }else{
                $this->assign('select_cols', $select_cols);
            }
            
            $this->assign('rdrive', $rdrive);
            $this->display('rdrive_add');
        }
    }
    
    public function rdrive_add() {
        if(IS_POST) {
            $data = $_POST;
            $data['token'] = $this->token;
            
            // constraints
            $settingType = intval($data['setting_type']);
            if($settingType == 1) {
                // time constrain
                $data['start_time'] = strtotime($data['starttime']);
                $data['end_time'] = strtotime($data['endtime']);
                $data['upperbound'] = NULL;
            }else{
                $data['start_time'] = NULL;
                $data['end_time'] = NULL;
            }
            
            // audit
            $data['status'] = 0; // 初始为不可用
            $data['create_time'] = time();
            $data['update_time'] = time();
            
            // default columns
            $default_show_cols = array();
            foreach($_POST as $pk => $pv) {
                if(strpos($pk, 'default_show_') == 0 && $pv == 1) {
                    $colname = substr($pk, strlen('default_show_'));
                    array_push($default_show_cols, $colname);
                }
            }
            $data['default_col_show'] =serialize($default_show_cols);
            
            // single text columns
            $text_cols = array();
            $text_text = $_POST['text_text'];
            $text_placeholder = $_POST['text_placeholder'];
            for($i = 0; $i<count($text_text); $i ++) {
                if(!empty($text_text[$i])) {
                    array_push($text_cols, array($text_text[$i], $text_placeholder[$i]));   
                }
            }
            $data['text_cols'] = serialize($text_cols);
            
            // select columns
            $select_cols = array();
            $select_text = $_POST['select_text'];
            $select_placeholder = $_POST['select_placeholder'];
            for($i=0; $i<count($select_text); $i ++) {
                if(!empty($select_text[$i])) {
                     array_push($select_cols, array($select_text[$i], $select_placeholder[$i]));
                }
            }
            $data['select_cols'] = serialize($select_cols);
            
            
            $rdriveId = M('car_rdrive')->add($data);
            
            if($rdriveId!== FALSE) {
                $kwds_db = M('keyword');
                $kwd_data['uid'] = session('uid');
                $kwd_data['token'] = $this->token;
                $kwd_data['type'] = 1;
                $kwd_data['module'] = 'drive';
                $kwd_data['function'] =  $this->function;
                $kwd_data['pid'] = $rdriveId;
                $kwd_data['keyword'] = $_POST['keyword']; 
                $kwd_data['status'] = 1;
                
                $kwdId = $kwds_db->add($kwd_data);
                
                if($kwdId !== false) {
                    $updateData['kwd_id'] = $kwdId;
                    $updateData['status'] = 1; // 设置该品牌为可用
                    M('car_rdrive')->where(array('id'=>$rdriveId))->save($updateData);
                    
                    $this->success('预约试驾项目添加成功！', U('Car/rdrive'));
                }else {
                    $this->error('添加失败！请刷新重试！');
                }
            }else {
                $this->error('添加失败！请刷新重试！');
            }
        }
        else{
            $this->assign('text_cols', array(''));
            $this->assign('select_cols', array(''));
            
            $this->assign('set',$merchant);
            $this->display();  
        }
    }
    
    public function rdrive_reserves() {
        // 试驾项目
        $rdrive_id        = intval($_GET['id']);
        
        if(empty($rdrive_id)) {
            $this->error('非法请求！');
        }
        
        // 判断是否使修改订单状态的调用
        $mode = $_GET['mode'];
        if($mode == 'edit') {
            // 获取更多状态修改的详细参数
            $status = intval($_GET['status']);
            $reserve_id = intval($_GET['reserve_id']);
            if($status < 1 || $status > 4) {
                $this->error('非法请求！');
            }
            
            $updateData['status'] = $status;
            $updateData['update_time'] = time();
            $ok = M('car_rdrive_order')
                    ->where(array('token'=>$this->token, 'id'=>$reserve_id, 'rdrive_id'=>$rdrive_id))
                    ->save($updateData);
            if($ok === FALSE) {
                $this->error('服务器忙！请稍后重试！');
            }
        }
        
        $statistics = M('car_rdrive_order')->query('SELECT status, count(*) count FROM tp_car_rdrive_order where token="'.
                $this->token.'" and rdrive_id='.$rdrive_id.' order by submit_time desc');
        if($statistics === FALSE) {
            $this->error('服务器忙！请稍后重试！');
        }
        
        $overview['unprocessed'] = 0;
        $overview['approved'] = 0;
        $overview['cancelByUser'] = 0;
        $overview['failed'] = 0;
        $overview['unprocessed'] = 0;
        $total_count = 0;
        foreach($statistics as $stats) {
            switch($stats['status']) {
                case 1:
                    $overview['unprocessed'] =  $stats['count'];
                    break;
                case 2:
                    $overview['approved'] =  $stats['count'];
                    break;
                case 3:
                    $overview['cancelByUser'] =  $stats['count'];
                    break;
                case 4:
                    $overview['failed'] =  $stats['count'];
                    break;
                default:
                    break;
            }
            
            $total_count += $stats['count'];
        }
        $overview['total_count'] = $total_count;
        $this->assign('overview', $overview);
        
        $Page       = new Page($total_count,50);
        $show       = $Page->show();

        //$reserves = M('car_rdrive_order')->where(array('token'=>$this->token,'rdrive_id'=>$rdrive_id))->order('id desc')->limit($Page->firstRow.','.$Page->listRows)->select(); 
        // get brand, series and models name
        $reserves = M('car_rdrive_order')
                ->join('inner join tp_car_brand b on b.id=tp_car_rdrive_order.brand')
                ->join('inner join tp_car_series s on s.id=tp_car_rdrive_order.series')
                ->join('inner join tp_car_models m on m.id=tp_car_rdrive_order.model')
                ->where(array('tp_car_rdrive_order.token'=>$this->token,'tp_car_rdrive_order.rdrive_id'=>$rdrive_id))
                ->field('tp_car_rdrive_order.*, b.name brand_name, s.name series_name,s.short_name series_short_name, m.name model_name')
                ->order('tp_car_rdrive_order.id desc')->limit($Page->firstRow.','.$Page->listRows)->select();
        $this->assign('page',$show);        
        $this->assign('reserves',$reserves);

        $rdrive = M('car_rdrive')->where(array('token'=>$this->token,'id'=>$rdrive_id, 'status' => 1))->find();
        
        
        if(!empty($rdrive['default_col_show'])) 
        {
            $this->assign('default_col_show', unserialize($rdrive['default_col_show']));
        }

        $text_cols = unserialize($rdrive['text_cols']);
        if (!empty($text_cols)) {
            $tmpTextCols = array();
            foreach($text_cols as $text_col ) {
                if(!empty($text_col[0])) {
                    array_push($tmpTextCols, $text_col[0]);
                }
            }
            // 单行文本列名列表
            $this->assign('text_cols', $tmpTextCols);
        }

        $select_cols = unserialize($rdrive['select_cols']);
        if (!empty($select_cols))
        {
            $tmpSelCols = array();
            foreach($select_cols as $sel_col ) {
                if(!empty($sel_col[0])) {
                    array_push($tmpSelCols, $sel_col[0]);
                }
            }
            // 单行文本列名列表
            $this->assign('select_cols', $tmpSelCols);
        }

        $this->display();
    }
    
    
    // reserve_drive
    public function rmaintain() {
        $where['token'] = $this->token;
        $where['status'] = 1;
        $rdrives = M('car_rmaintain')->where($where)->select();
        $this->assign('rdrives', $rdrives);
        $this->display();
    }
    public function rmaintain_del($id) {
        $rdriveId  = intval($id);
        if(empty($rdriveId) || $rdriveId <=0 ) {
            $this->error('非法操作！');
        }
        
        $ok = M('car_rmaintain')->where(array('token'=>$this->token, 'status'=>1, 'id'=>$rdriveId))->setField('status', 0);
        if($ok !== FALSE) {
            $this->success('删除成功！');
        }else{
            $this->error('服务器忙！请稍后重试！');
        }
    }
    public function rmaintain_edit() {
        if(IS_POST) {
            $rdriveId = intval($_POST['id']);
            if(empty($rdriveId) || $rdriveId <= 0) {
                $this->error('非法操作！');
            }
            
            $data = $_POST;
            // constraints
            $settingType = intval($data['setting_type']);
            if($settingType == 1) {
                // time constrain
                $data['start_time'] = strtotime($data['starttime']);
                $data['end_time'] = strtotime($data['endtime']);
                $data['upperbound'] = NULL;
            }else{
                $data['start_time'] = NULL;
                $data['end_time'] = NULL;
            }
             // default columns
            $default_show_cols = array();
            foreach($_POST as $pk => $pv) {
                if(strpos($pk, 'default_show_') == 0 && $pv == 1) {
                    $colname = substr($pk, strlen('default_show_'));
                    array_push($default_show_cols, $colname);
                }
            }
            $data['default_col_show'] = serialize($default_show_cols); //join("|", $default_show_cols);
            
            // single text columns
            $text_cols = array();
            $text_text = $_POST['text_text'];
            $text_placeholder = $_POST['text_placeholder'];
            for($i = 0; $i<count($text_text); $i ++) {
                if(!empty($text_text[$i])) {
                    array_push($text_cols, array($text_text[$i], $text_placeholder[$i]));
                    //array_push($text_cols, $text_text[$i].'$$'.$text_placeholder[$i]);
                }
            }
            $data['text_cols'] = serialize($text_cols);//join("###", $text_cols);
            
            // select columns
            $select_cols = array();
            $select_text = $_POST['select_text'];
            $select_placeholder = $_POST['select_placeholder'];
            for($i=0; $i<count($select_text); $i ++) {
                if(!empty($select_text[$i])) {
                    array_push($select_cols, array($select_text[$i], $select_placeholder[$i]));
                    //array_push($select_cols, $select_text[$i].'$$'.$select_placeholder[$i]);
                }
            }
            $data['select_cols'] = serialize($select_cols);//join("###", $select_cols);
            
            $data['update_time'] = time();
            
            $rdrive = M('car_rmaintain')->where(array('id'=>$rdriveId, 'status'=>1, 'token'=>$this->token))->find();
            if(!empty($rdrive)){
                M('car_rmaintain')->where(array('id'=>$rdriveId, 'status'=>1, 'token'=>$this->token))->save($data);
                //更新关键词表
                M('keyword')->where(array('id'=>$rdrive['kwd_id']))->setField('keyword', $_POST['keyword']);
                $this->success('更新成功！');
            }else {
                $this->error('更新失败！请重试！');
            }
        }
        else {
            $rdriveId = intval($_GET['id']);
            if(empty($rdriveId) || $rdriveId <= 0) {
                $this->error('非法操作！');
            }
            
            $where['id'] = $rdriveId;
            $where['token'] = $this->token;
            $where['status'] = 1;
            $rdrive = M('car_rmaintain')->where($where)->find();
            if(empty($rdrive)) {
                $this->error('非法操作！');
            }
            
            if(!empty($rdrive['default_col_show'])) {
                $this->assign('default_col_show', unserialize($rdrive['default_col_show']));
            }
            $text_cols = unserialize($rdrive['text_cols']);
            if(empty($text_cols)){
                $this->assign('text_cols', array(array()));
            }else{
                $this->assign('text_cols', $text_cols);
            }
            $select_cols = unserialize($rdrive['select_cols']);
            if(empty($select_cols)) {
                $this->assign('select_cols', array(array()));
            }else{
                $this->assign('select_cols', $select_cols);
            }
            
            $this->assign('rdrive', $rdrive);
            $this->display('rmaintain_add');
        }
    }
    
    
    public function rmaintain_add() {
        if(IS_POST) {
            $data = $_POST;
            $data['token'] = $this->token;
            
            // constraints
            $settingType = intval($data['setting_type']);
            if($settingType == 1) {
                // time constrain
                $data['start_time'] = strtotime($data['starttime']);
                $data['end_time'] = strtotime($data['endtime']);
                $data['upperbound'] = NULL;
            }else{
                $data['start_time'] = NULL;
                $data['end_time'] = NULL;
            }
            
            // audit
            $data['status'] = 0; // 初始为不可用
            $data['create_time'] = time();
            $data['update_time'] = time();
            
            // default columns
            $default_show_cols = array();
            foreach($_POST as $pk => $pv) {
                if(strpos($pk, 'default_show_') == 0 && $pv == 1) {
                    $colname = substr($pk, strlen('default_show_'));
                    array_push($default_show_cols, $colname);
                }
            }
            $data['default_col_show'] =serialize($default_show_cols);
            
            // single text columns
            $text_cols = array();
            $text_text = $_POST['text_text'];
            $text_placeholder = $_POST['text_placeholder'];
            for($i = 0; $i<count($text_text); $i ++) {
                if(!empty($text_text[$i])) {
                    array_push($text_cols, array($text_text[$i], $text_placeholder[$i]));   
                }
            }
            $data['text_cols'] = serialize($text_cols);
            
            // select columns
            $select_cols = array();
            $select_text = $_POST['select_text'];
            $select_placeholder = $_POST['select_placeholder'];
            for($i=0; $i<count($select_text); $i ++) {
                if(!empty($select_text[$i])) {
                     array_push($select_cols, array($select_text[$i], $select_placeholder[$i]));
                }
            }
            $data['select_cols'] = serialize($select_cols);
            
            
            $rdriveId = M('car_rmaintain')->add($data);
            
            if($rdriveId!== FALSE) {
                $kwds_db = M('keyword');
                $kwd_data['uid'] = session('uid');
                $kwd_data['token'] = $this->token;
                $kwd_data['type'] = 1;
                $kwd_data['module'] = 'maintain';
                $kwd_data['function'] = $this->function;
                $kwd_data['pid'] = $rdriveId;
                $kwd_data['keyword'] = $_POST['keyword']; 
                $kwd_data['status'] = 1;
                
                $kwdId = $kwds_db->add($kwd_data);
                
                if($kwdId !== false) {
                    $updateData['kwd_id'] = $kwdId;
                    $updateData['status'] = 1; // 设置该品牌为可用
                    M('car_rmaintain')->where(array('id'=>$rdriveId))->save($updateData);
                    
                    $this->success('预约项目添加成功！', U('Car/rmaintain'));
                }else {
                    $this->error('添加失败！请刷新重试！');
                }
            }else {
                $this->error('添加失败！请刷新重试！');
            }
        }
        else{
            $this->assign('text_cols', array(''));
            $this->assign('select_cols', array(''));
            
            $this->assign('set',$merchant);
            $this->display();  
        }
    }
    
    public function rmaintain_reserves() {
        // 试驾项目
        $rdrive_id        = intval($_GET['id']);
        
        if(empty($rdrive_id)) {
            $this->error('非法请求！');
        }
        
        // 判断是否使修改订单状态的调用
        $mode = $_GET['mode'];
        if($mode == 'edit') {
            // 获取更多状态修改的详细参数
            $status = intval($_GET['status']);
            $reserve_id = intval($_GET['reserve_id']);
            if($status < 1 || $status > 4) {
                $this->error('非法请求！');
            }
            
            $updateData['status'] = $status;
            $updateData['update_time'] = time();
            $ok = M('car_rmaintain_order')
                    ->where(array('token'=>$this->token, 'id'=>$reserve_id, 'rdrive_id'=>$rdrive_id))
                    ->save($updateData);
            if($ok === FALSE) {
                $this->error('服务器忙！请稍后重试！');
            }
        }
        
        $statistics = M('car_rmaintain_order')->query('SELECT status, count(*) count FROM tp_car_rmaintain_order where token="'.
                $this->token.'" and rdrive_id='.$rdrive_id.' group by status');
       
        if($statistics === FALSE) {
            $this->error('服务器忙！请稍后重试！');
        }
        
        $overview['unprocessed'] = 0;
        $overview['approved'] = 0;
        $overview['cancelByUser'] = 0;
        $overview['failed'] = 0;
        $overview['unprocessed'] = 0;
        $total_count = 0;
        foreach($statistics as $stats) {
            switch($stats['status']) {
                case 1:
                    $overview['unprocessed'] =  $stats['count'];
                    break;
                case 2:
                    $overview['approved'] =  $stats['count'];
                    break;
                case 3:
                    $overview['cancelByUser'] =  $stats['count'];
                    break;
                case 4:
                    $overview['failed'] =  $stats['count'];
                    break;
                default:
                    break;
            }
            
            $total_count += $stats['count'];
        }
        $overview['total_count'] = $total_count;
        $this->assign('overview', $overview);
        
        $Page       = new Page($total_count,50);
        $show       = $Page->show();

        $reserves = M('car_rmaintain_order')->where(array('token'=>$this->token,'rdrive_id'=>$rdrive_id))->order('id desc')->limit($Page->firstRow.','.$Page->listRows)->select(); 
       
        $this->assign('page',$show);        
        $this->assign('reserves',$reserves);

        $rdrive = M('car_rmaintain')->where(array('token'=>$this->token,'id'=>$rdrive_id, 'status' => 1))->find();
        
        
        if(!empty($rdrive['default_col_show'])) 
        {
            $this->assign('default_col_show', unserialize($rdrive['default_col_show']));
        }

        $text_cols = unserialize($rdrive['text_cols']);
        if (!empty($text_cols)) {
            $tmpTextCols = array();
            foreach($text_cols as $text_col ) {
                if(!empty($text_col[0])) {
                    array_push($tmpTextCols, $text_col[0]);
                }
            }
            // 单行文本列名列表
            $this->assign('text_cols', $tmpTextCols);
        }

        $select_cols = unserialize($rdrive['select_cols']);
        if (!empty($select_cols))
        {
            $tmpSelCols = array();
            foreach($select_cols as $sel_col ) {
                if(!empty($sel_col[0])) {
                    array_push($tmpSelCols, $sel_col[0]);
                }
            }
            // 单行文本列名列表
            $this->assign('select_cols', $tmpSelCols);
        }

        $this->display();
    }
    
    public function tools() {
        $tools = C('car_tools');
        $car_tools = M('car_tools')->where(array('token'=>$this->token))->find();
        $closed_tools = array();
        if(!empty($car_tools)) {
            $closed_tools = unserialize($car_tools['closed_tools']);
        }
        
        foreach($tools as $key => &$tool) {
            if(in_array($key, $closed_tools)) {
                $tool['status'] = 0;
            }else{
                $tool['status'] = 1;
            }
        }
        
        $this->assign('tools', $tools);
        $this->display();
    }
    public function tool_change() {
        if(IS_POST) {
            $tools = C('car_tools');
            $appId = intval($_POST['appid']);
            $status = ($_POST['status'] == 'true');
            if(array_key_exists($appId, $tools)) {
                $car_tools = M('car_tools')->where(array('token'=>$this->token))->find();
                if(empty($car_tools)) {
                    $data['token'] = $this->token;
                    if($status == 0) {
                        $data['closed_tools'] = serialize(array($appId));
                    }
                    M('car_tools')->add($data);
                }else{
                    $closedTools = unserialize($car_tools['closed_tools']);
                    $index = array_search($appId, $closedTools);
                    if($index === FALSE && $status == 0)  {
                        array_push($closedTools, $appId);
                        $car_tools['closed_tools'] = serialize($closedTools);
                        M('car_tools')->save($car_tools);
                        
                    }else if($index !== FALSE && $status == 1){
                        unset($closedTools[$index]);
                        $car_tools['closed_tools'] = serialize($closedTools);
                         M('car_tools')->save($car_tools);
                    }
                    
                }
                echo 'success';
                exit;
            }
        }
    }
    
    public function care() {
        if(IS_POST) {
            $id = intval($_POST['id']);
            $care_record = M('car_care')->where(array('token'=>$this->token, 'id'=>$id))->find();
            
            if(empty($care_record)) {
                // add
                $data = $_POST;
                $data['token'] = $this->token;
                $data['create_time'] = time();
                $data['update_time'] = time();
                $careId = M('car_care')->add($data);
                if($careId !== FALSE) {
                    $kwds_db = M('keyword');
                    $kwd_data['uid'] = session('uid');
                    $kwd_data['token'] = $this->token;
                    $kwd_data['type'] = 1;
                    $kwd_data['module'] = 'care';
                    $kwd_data['function'] = $this->function;
                    $kwd_data['pid'] = $careId;
                    $kwd_data['keyword'] = $_POST['keyword']; 
                    $kwd_data['status'] = 1;

                    $kwdId = $kwds_db->add($kwd_data);

                    if($kwdId !== false) {
                        $updateData['kwd_id'] = $kwdId;
                        $updateData['status'] = 1; // 设置该品牌为可用
                        M('car_care')->where(array('id'=>$careId))->save($updateData);

                        $this->success('添加成功！', U('Car/care'));
                    }else {
                        $this->error('添加失败！请刷新重试！');
                    }
                }else {
                    $this->error('添加失败！请刷新重试！');
                }
            }else {
                // edit;
                $data = $_POST;
                $data['token'] = $this->token;
                $data['update_time'] = time();
                M('car_care')->save($data);
                M('keyword')->where(array('id'=>$care_record['kwd_id']))->setField('keyword', $_POST['keyword']);
                 $this->success('更新成功！', U('Car/care'));
            }
            
        }else {
            $care = M('car_care')->where(array('token'=>$this->token))->find();
            $this->assign('care', $care);
            $this->display();
        }
    }
    
}