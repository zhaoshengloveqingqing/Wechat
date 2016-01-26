<?php
class CarAction extends BaseAction{
    private $wecha_id;
    private $token;
    
    protected function _initialize(){
        parent::_initialize();
        $agent = $_SERVER['HTTP_USER_AGENT']; 
        if(!strpos($agent,"MicroMessenger")) {
                //echo '此功能只能在微信浏览器中使用';exit;
        }
        
        $this->wecha_id = $_GET['wecha_id'];
        $this->token = trim($_GET['token']);
        
        if(empty($this->token)) {
            echo 'Invalid request!';
            exit;
        }
    }
    
    // get all brands
    public function index(){
        $where['token'] = $this->token;
        $where['status'] = 1;
        $brands = M('car_brand')->where($where)->order('sequence asc, id asc')->select();
        $this->assign('brands', $brands);
        $this->display();
    }
    
    public function brand() {
        $brand_id = intval($_GET['id']);
        if(empty($brand_id)) {
            echo 'Invalid request!';
            exit;
        }
        
        $where['token'] = $this->token;
        $where['status'] = 1;
        $where['id'] = $brand_id;
        $brand = M('car_brand')->where($where)->find();
        
        if(empty($brand)) {
            echo 'invalid request!';
            exit;
        }
        
        unset($where);
        $where['token'] = $this->token;
        $where['status'] = 1;
        $where['brand_id'] = $brand_id;
        $series = M('car_series')->where($where)->order('sequence asc, id asc')->select();
        
        $this->assign('brand', $brand);
        $this->assign('series', $series);
        $this->display();
    }
    
    public function series() {
        $brand_id = intval($_GET['bid']);
        $series_id = intval($_GET['id']);
        if(empty($brand_id) || empty($series_id)) {
            echo 'Invalid request!';
            exit;
        }
        
        // brand information
        unset($where);
        $where['token'] = $this->token;
        $where['status'] = 1;
        $where['id'] = $brand_id;
        $brand = M('car_brand')->where($where)->field('id,name,logo')->find();
        if(empty($brand)) {
            echo 'Invalid request!';
            exit;
        }
        
        // series under this brand
        unset($where);
        $where['token'] = $this->token;
        $where['status'] = 1;
        $where['brand_id'] = $brand_id;
        $series = M('car_series')->where($where)->select();
        if(empty($series)) {
            echo 'Invalid request';
            exit;
        }
        $currSeries = array();
        foreach($series as $s) {
            if($s['id'] == $series_id) {
                $currSeries = $s;
                break;
            }
        }
        
        // models under this series
        unset($where);
        $where['token'] = $this->token;
        $where['status'] = 1;
        $where['car_brand'] = $brand_id;
        $where['car_series'] = $series_id;
        $models = M('car_models')->where($where)->select();
        
        $this->assign('brand', $brand);
        $this->assign('series', $series);
        $this->assign('models', $models);
        $this->assign('currSeries', $currSeries);
        $this->display();
    }
    
    public function model() {
        $model_id = intval($_GET['id']);
        $series_id = intval($_GET['sid']);
        $brand_id = intval($_GET['bid']);
        
        if(empty($model_id) || empty($series_id) || empty($brand_id)) {
            echo 'Invalid request!';
            exit;
        }
        
        // brand information
        unset($where);
        $where['token'] = $this->token;
        $where['status'] = 1;
        $where['id'] = $brand_id;
        $brand = M('car_brand')->where($where)->field('id,name,logo')->find();
        if(empty($brand)) {
            echo 'Invalid request!';
            exit;
        }
        
        // series under this brand
        unset($where);
        $where['token'] = $this->token;
        $where['status'] = 1;
        $where['brand_id'] = $brand_id;
        $series = M('car_series')->where($where)->select();
        if(empty($series)) {
            echo 'Invalid request';
            exit;
        }
        
        $where['token'] = $this->token;
        $where['id'] = $model_id;
        $where['car_series'] = $series_id;
        $where['car_brand'] = $brand_id;
        $where['status'] = 1;
        $model = M('car_models')->where($where)->find();
        
        $img_id_list = unserialize($model['pic_id_list']);
        $pics = array();
        if(!empty($img_id_list) && count($img_id_list)) {
            unset($where);
            $where['token'] = $this->token;
            $where['id'] = array('in', $img_id_list);
            $where['status'] = 1;
            $pics = M('raw_image')->where($where)->select();
        }
        
        $this->assign('pic_id_list', $img_id_list);
        $this->assign('pics', $pics);
        $this->assign('model', $model);
        $this->assign('brand', $brand);
        $this->assign('series', $series);
        $this->assign('car_box_options', C('car_box_options'));
        $this->display();
    }
    
    public function sales() {
        // origin model on which user navigates to sales page
        $model_id = $_GET['mid'];
        
        $where['token'] = $this->token;
        $where['status'] = 1;
        $sales = M('car_sales')->where($where)->order('sequence asc')->select();
        
        $pre_sales = array();
        $post_sales = array();
        foreach($sales as $sale) {
            if($sale['pre_sale'] == 1) {
                array_push($pre_sales, $sale);
            }
            if($sale['post_sale'] == 1) {
                array_push($post_sales, $sale);
            }
        }
        
        $this->assign('pre_sales', $pre_sales);
        $this->assign('post_sales', $post_sales);
        $this->display();
    }
    
    public function rmaintain() {
        // origin model on which user navigates to reserve drive page
        $model_id = $_GET['mid'];
        
        $rdriveId = intval($_GET['id']);
        if(!empty($rdriveId)) {
            $where['id'] = $rdriveId;
        }
        $where['token'] = $this->token;
        $where['status'] = 1;
        //$where['id'] = $rdriveId;
        $rdrive = M('car_rmaintain')->where($where)->find();
        if(empty($rdrive)) {
            echo 'Invalid request';
            exit;
        }
        
        $this->assign('rdrive', $rdrive);
        
        $navigationLink = "http://api.map.baidu.com/marker?location="
             .$rdrive['latitude'].','.$rdrive['longtitude']
             .'&title='.urlencode($rdrive['img_title'])
             .'&name='.urlencode($rdrive['img_title'])
             .'&content='.urlencode($rdrive['address'])
             .'&output=html&src=lingzhtech';
        $this->assign('navigationLink', $navigationLink);
        
        
        if(!empty($rdrive['default_col_show'])) {
            $this->assign('default_col_show', unserialize($rdrive['default_col_show']));
        }
        $text_cols = unserialize($rdrive['text_cols']);
        $this->assign('text_cols', $text_cols);
        $select_cols = unserialize($rdrive['select_cols']);
        $selectCols = array();
        foreach($select_cols as $selectArray) {
            if(count($selectArray) == 2) {
                 array_push($selectCols, array($selectArray[0], explode('|', $selectArray[1])));
            }
        }
        $this->assign('select_cols', $selectCols);
        
        
        $order_count = M('car_rmaintain_order')->where(array('wecha_id'=>$this->wecha_id,'token'=>$this->token, 'rdrive_id'=>$rdrive['id']))->count();
        $this->assign('order_count', $order_count);
        
        $this->display();
    }
    
    public function reserve_maintain()
    {
        if(IS_POST) {
            // validation
            $rdrive_id = intval($_POST['rdrive_id']);
            if(empty($rdrive_id)) {
                echo'{"success":0,"msg":"非法请求！"}';
                exit;
            }
            $rdrive = M('car_rmaintain')->where(array('token'=>$this->token, 'status'=>1, 'id'=>$rdrive_id))->find();
            if(empty($rdrive)) {
                echo'{"success":0,"msg":"系统忙，请稍后预定！"}';
                exit;
            }
            
            // 检查预约限制
            $reserve_date = strtotime($_POST['reserve_date']);
            $currTime = time();
            switch($rdrive['setting_type']) {
                case 1:
                    $start_time = $rdrive["start_time"];
                    $end_time = $rdrive['end_time'];
                    if(!empty($start_time) && !empty($end_time)) {
                       
                        if($reserve_date >= $start_time && $reserve_date < $end_time) {
                            // pass
                        }else{
                            // refuse
                            echo'{"success":0,"msg":"因商家设定了时间限制，您的预约失败！"}';
                            exit;
                        }
                    }
                    break;
                case 2:
                    $upperbound = $rdrive['upperbound'];
                    if(!empty($upperbound)) {
                        //预定日期都被取整到自然天的开始
                        $start_time = $reserve_date;
                        $end_time = $reserve_date + 24 * 60 * 60;
                        $where['token'] = $this->token;
                        $where['rdrive_id'] = $rdrive_id;
                        $where['submit_time'] = array('EGT', $start_time);
                        $where['submit_time'] = array('ELT', $end_time);
                        $tmp_cnt = M('car_rmaintain_order')->where($where)->count();
                        if($tmp_cnt >= $upperbound) {
                            // refuse
                            echo'{"success":0,"msg":"因商家设置的每日预约数到达上限，您的预约失败！"}';
                            exit;
                        }
                    }
                            
                    break;
                case 3:
                    $upperbound = $rdrive['upperbound'];
                    if(!empty($upperbound)) {
                        //直接往前数24小时
                        $where['token'] = $this->token;
                        $where['rdrive_id'] = $rdrive_id;
                        $tmp_cnt = M('car_rmaintain_order')->where($where)->count();
                        if($tmp_cnt >= $upperbound) {
                            // refuse
                            echo'{"success":0,"msg":"因商家设置的总预约数到达上限，您的预约失败！"}';
                            exit;
                        }
                    }
                    break;
                default:
                    break;
                    
            }
            
            $data = $_POST;
            $data['reserve_date'] = strtotime($_POST['reserve_date']);
            $data['token'] = $this->token;
            $data['wecha_id'] = $this->wecha_id;
            $data['submit_time'] = time();
            $data['update_time'] = time();
            $data['status'] = 1;
            $data['text_cols'] = serialize($_POST['text_cols']);
            $data['select_cols'] = serialize($_POST['select_cols']);
            
            $order = M('car_rmaintain_order')->add($data);
            if ($order) {
                echo'{"success":1,"msg":"恭喜,预定成功。"}';
            }else{
                echo'{"success":0,"msg":"系统忙，请稍后预定。"}';
            }            
            exit;
        }
    }
    
    public function rmaintain_orders()
    {
        $wecha_id = $this->_get('wecha_id');
        $token  = $this->_get('token');
        $rdrive_id = $this->_get('rdrive_id');
        
        $rdrive = M('car_rmaintain')->where(array('token'=>$token,'id'=>$rdrive_id, 'status' => 1))->find();
        if(empty($rdrive)) {
            $this->error('非法请求！');
            exit;
        }
        $this->assign('rdrive', $rdrive);
        if(!empty($rdrive['default_col_show'])) {
            $this->assign('default_col_show', unserialize($rdrive['default_col_show']));
        }

        // 单行文本字段名
        $text_cols = unserialize($rdrive['text_cols']);
        if(!empty($text_cols)){
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
        if(!empty($select_cols)){
            $tmpSelCols = array();
            foreach($select_cols as $sel_col ) {
                if(!empty($sel_col[0])) {
                    array_push($tmpSelCols, $sel_col[0]);
                }
            }
            // 单行文本列名列表
            $this->assign('select_cols', $tmpSelCols);
        }
        
        $orders = M('car_rmaintain_order')->where(array('wecha_id'=>$wecha_id,'token'=>$token, 'rdrive_id'=>$rdrive_id))->order('id desc')->select();
       
        $this->assign('orders',$orders);
        
        $this->display();
    }
    
    
    public function rmaintain_order_cancel() {
        $wecha_id = $this->_get('wecha_id');
        $token  = $this->_get('token');
        $orderid = $this->_get('id');
        
        M('car_rmaintain_order')->where(array('wecha_id'=>$wecha_id,'token'=>$token, 'id'=>$orderid))->setField('status',3);
        echo '0';
    }
    
    
    public function rdrive() {
        // origin model on which user navigates to reserve drive page
        $model_id = $_GET['mid'];
        $series_id = $_GET['sid'];
        $brand_id = $_GET['bid'];
        
        $rdriveId = intval($_GET['id']);
        if(!empty($rdriveId)) {
            $where['id'] = $rdriveId;
        }
        $where['token'] = $this->token;
        $where['status'] = 1;
        //$where['id'] = $rdriveId;
        $rdrive = M('car_rdrive')->where($where)->find();
        if(empty($rdrive)) {
            echo 'Invalid request';
            exit;
        }
        
        $this->assign('rdrive', $rdrive);
        
        $navigationLink = "http://api.map.baidu.com/marker?location="
             .$rdrive['latitude'].','.$rdrive['longtitude']
             .'&title='.urlencode($rdrive['img_title'])
             .'&name='.urlencode($rdrive['img_title'])
             .'&content='.urlencode($rdrive['address'])
             .'&output=html&src=lingzhtech';
        $this->assign('navigationLink', $navigationLink);
        
        $mapping = $this->constructBrandSeriesCS($this->token);
        $this->assign('mapping', $mapping);
        
        
        if(!empty($rdrive['default_col_show'])) {
            $this->assign('default_col_show', unserialize($rdrive['default_col_show']));
        }
        $text_cols = unserialize($rdrive['text_cols']);
        $this->assign('text_cols', $text_cols);
        $select_cols = unserialize($rdrive['select_cols']);
        $selectCols = array();
        foreach($select_cols as $selectArray) {
            if(count($selectArray) == 2) {
                 array_push($selectCols, array($selectArray[0], explode('|', $selectArray[1])));
            }
        }
        $this->assign('select_cols', $selectCols);
        
        
        $order_count = M('car_rdrive_order')->where(array('wecha_id'=>$this->wecha_id,'token'=>$this->token, 'rdrive_id'=>$rdrive['id']))->count();
        $this->assign('order_count', $order_count);
        
        $this->display();
    }
    
    
    private function constructBrandSeriesCS($token) {
        $allModels = M('car_models')->join("inner join tp_car_series s on tp_car_models.car_series=s.id")
                ->join("inner join tp_car_brand b on tp_car_models.car_brand = b.id")
                ->where(array('b.status'=>1, 's.status'=>1, 'tp_car_models.status'=>1, 'b.token'=>$token))
                ->field('b.name brand_name, b.id brand_id, s.id series_id, s.name series_name, tp_car_models.id model_id, tp_car_models.name model_name')
                ->select();

        
        // construct brand&series cascading select control content
        $brand2series = array();
        foreach($allModels as $model) {
            $brandId = $model['brand_id'];
            if(!array_key_exists($brandId, $brand2series)) {
                $brand2series[$brandId] = array();
            }
            $duplicated = false;
            foreach($brand2series[$brandId] as $seriesItem) {
                if($seriesItem['series_id'] == $model['series_id']) {
                    $duplicated = true;
                    break;
                }
            }
            if(!$duplicated) {
                array_push($brand2series[$brandId], $model);
            }
        }
        
        $series2model = array();
        foreach($allModels as $model) {
            $seriesId = $model['series_id'];
            if(!array_key_exists($seriesId, $series2model)) {
                $series2model[$seriesId] = array();
            }
            
            array_push($series2model[$seriesId], $model);
        }

        // brand 2 series
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
        
        $mapping['brand2series'] = join('#', $cs);
        
        // series 2 
        $cs2 = array();
        array_push($cs2, '请选择-0$请选择-0');
        foreach($series2model as $s2m) {
            $modelsOfSeries = array();
            foreach($s2m as $slist) {
                array_push($modelsOfSeries, $slist['model_name'].'-'.$slist['model_id']);
            }

            array_push($cs2, $slist['series_name'].'-'.$slist['series_id'].'$'.join(',', $modelsOfSeries));
        }
        $mapping['series2model'] = join('#', $cs2);
        return $mapping;
    }
    
    public function reserve_drive()
    {
        if(IS_POST) {
            // validation
            $rdrive_id = intval($_POST['rdrive_id']);
            if(empty($rdrive_id)) {
                echo'{"success":0,"msg":"非法请求！"}';
                exit;
            }
            $rdrive = M('car_rdrive')->where(array('token'=>$this->token, 'status'=>1, 'id'=>$rdrive_id))->find();
            if(empty($rdrive)) {
                echo'{"success":0,"msg":"系统忙，请稍后预定！"}';
                exit;
            }
            
            // 检查预约限制
            $reserve_date = strtotime($_POST['reserve_date']);
            $currTime = time();
            switch($rdrive['setting_type']) {
                case 1:
                    $start_time = $rdrive["start_time"];
                    $end_time = $rdrive['end_time'];
                    if(!empty($start_time) && !empty($end_time)) {
                       
                        if($reserve_date >= $start_time && $reserve_date < $end_time) {
                            // pass
                        }else{
                            // refuse
                            echo'{"success":0,"msg":"因商家设定了时间限制，您的预约失败！"}';
                            exit;
                        }
                    }
                    break;
                case 2:
                    $upperbound = $rdrive['upperbound'];
                    if(!empty($upperbound)) {
                        //预定日期都被取整到自然天的开始
                        $start_time = $reserve_date;
                        $end_time = $reserve_date + 24 * 60 * 60;
                        $where['token'] = $this->token;
                        $where['rdrive_id'] = $rdrive_id;
                        $where['submit_time'] = array('EGT', $start_time);
                        $where['submit_time'] = array('ELT', $end_time);
                        $tmp_cnt = M('car_rdrive_order')->where($where)->count();
                        if($tmp_cnt >= $upperbound) {
                            // refuse
                            echo'{"success":0,"msg":"因商家设置的每日预约数到达上限，您的预约失败！"}';
                            exit;
                        }
                    }
                            
                    break;
                case 3:
                    $upperbound = $rdrive['upperbound'];
                    if(!empty($upperbound)) {
                        //直接往前数24小时
                        $where['token'] = $this->token;
                        $where['rdrive_id'] = $rdrive_id;
                        $tmp_cnt = M('car_rdrive_order')->where($where)->count();
                        if($tmp_cnt >= $upperbound) {
                            // refuse
                            echo'{"success":0,"msg":"因商家设置的总预约数到达上限，您的预约失败！"}';
                            exit;
                        }
                    }
                    break;
                default:
                    break;
                    
            }
            
            $data = $_POST;
            $data['reserve_date'] = strtotime($_POST['reserve_date']);
            $data['token'] = $this->token;
            $data['wecha_id'] = $this->wecha_id;
            $data['submit_time'] = time();
            $data['update_time'] = time();
            $data['status'] = 1;
            $data['text_cols'] = serialize($_POST['text_cols']);
            $data['select_cols'] = serialize($_POST['select_cols']);
            
            $order = M('car_rdrive_order')->add($data);
            if ($order) {
                echo'{"success":1,"msg":"恭喜,预定成功。"}';
            }else{
                echo'{"success":0,"msg":"系统忙，请稍后预定。"}';
            }            
            exit;
        }
    }
    
    public function rdrive_orders()
    {
        $wecha_id = $this->_get('wecha_id');
        $token  = $this->_get('token');
        $rdrive_id = $this->_get('rdrive_id');
        
        $rdrive = M('car_rdrive')->where(array('token'=>$token,'id'=>$rdrive_id, 'status' => 1))->find();
        if(empty($rdrive)) {
            $this->error('非法请求！');
            exit;
        }
        $this->assign('rdrive', $rdrive);
        if(!empty($rdrive['default_col_show'])) {
            $this->assign('default_col_show', unserialize($rdrive['default_col_show']));
        }

        // 单行文本字段名
        $text_cols = unserialize($rdrive['text_cols']);
        if(!empty($text_cols)){
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
        if(!empty($select_cols)){
            $tmpSelCols = array();
            foreach($select_cols as $sel_col ) {
                if(!empty($sel_col[0])) {
                    array_push($tmpSelCols, $sel_col[0]);
                }
            }
            // 单行文本列名列表
            $this->assign('select_cols', $tmpSelCols);
        }
        
        //$orders = M('car_rdrive_order')->where(array('wecha_id'=>$wecha_id,'token'=>$token, 'rdrive_id'=>$rdrive_id))->order('id desc')->select();
        $orders = M('car_rdrive_order')
                ->join('inner join tp_car_brand b on b.id=tp_car_rdrive_order.brand')
                ->join('inner join tp_car_series s on s.id=tp_car_rdrive_order.series')
                ->join('inner join tp_car_models m on m.id=tp_car_rdrive_order.model')
                ->where(array('tp_car_rdrive_order.wecha_id'=>$wecha_id,'tp_car_rdrive_order.token'=>$token, 'tp_car_rdrive_order.rdrive_id'=>$rdrive_id))
                ->field('tp_car_rdrive_order.*, b.name brand_name, s.name series_name,s.short_name series_short_name, m.name model_name')
                ->order('tp_car_rdrive_order.id desc')->select();
        $this->assign('orders',$orders);
        
        $this->display();
    }
    
    
    public function rdrive_order_cancel() {
        $wecha_id = $this->_get('wecha_id');
        $token  = $this->_get('token');
        $orderid = $this->_get('id');
        
        M('car_rdrive_order')->where(array('wecha_id'=>$wecha_id,'token'=>$token, 'id'=>$orderid))->setField('status',3);
        echo '0';
    }
    
    public function tools() {
        $tools = C('car_tools');
        $car_tools = M('car_tools')->where(array('token'=>$this->token))->find();
        $closed_tools = array();
        if(!empty($car_tools)) {
            $closed_tools = unserialize($car_tools['closed_tools']);
        }
        
        $tmp_tools = array();
        foreach($tools as $key => $tool) {
            if(!in_array($key, $closed_tools)) {
                array_push($tmp_tools, $tool);
            }
        }
        
        $this->assign('tools', $tmp_tools);
        $this->display();
    }
    
    public function care() {
        $detailRecord = M('car_details')->where(array('token'=>$this->token, 'wecha_id'=>$this->wecha_id))->find();
        if(!empty($detailRecord)) {
            $this->assign('detailRecord', $detailRecord);
            $care_durations = C('car_care_duration');
            // 保险
            $duration_insurance = print_r(date('Y-m-d', strtotime("+1 year", strtotime($detailRecord['insurance_lastDate']))), true);
            // 保养
            $duration_maintain = print_r(date('Y-m-d', strtotime("+5 month", strtotime($detailRecord['care_lastDate']))), true);
             // 年检
            //上牌日期算，6年内，两年检一次，6年后，一年检一次
            $curr_time = time();
            $num_starttime = strtotime($detailRecord['number_starttime']);
            $past_years = (int)(($curr_time - $num_starttime) / 365/24/60/60);
            dump($past_years);
            if($past_years < 6) {
                // 六年内
                
                $next_unit = ( (int)($past_years/2) + 1) * 2;
                dump( $next_unit);
                $duration_check = print_r(date('Y-m-d', strtotime("+".$next_unit." year", strtotime($detailRecord['number_starttime']))), true);
            }else {
                echo $past_years;
                $duration_check = print_r(date('Y-m-d', strtotime("+".($past_years + 1)." year", strtotime($detailRecord['number_starttime']))), true);
            }
            
            $next_mileage = C('car_insurance_mileage_unit') + $detailRecord['care_mileage'];
            $this->assign('next_mileage', $next_mileage);
            
            $this->assign('duration_insurance', $duration_insurance);
            $this->assign('duration_maintain', $duration_maintain);
            $this->assign('duration_check', $duration_check);
        }
        
        $care = M('car_care')->where(array('token'=>$this->token))->find();
        if(!empty($care)) {
            $this->assign('care', $care);
        }
        $this->display();
    }
    
    public function details() {
        $detailRecord = M('car_details')->where(array('token'=>$this->token, 'wecha_id'=>$this->wecha_id))->find();
        if(IS_POST) {
            $detail_id = intval($_POST['id']);
            $data = $_POST;
            if(!empty($detail_id)) {
                
                if(empty($detailRecord) || $detailRecord['id'] != $detail_id) {
                    // invalid request
                    $this->redirect(U('Wap/Car/care', array('token'=>$this->token, 'wecha_id'=>$this->wecha_id)));
                }
                
                $data['update_time'] = time();
                M('car_details')->save($data);
                $this->redirect(U('Wap/Car/care', array('token'=>$this->token, 'wecha_id'=>$this->wecha_id)));
                
            }else {
                
                if(!empty($detailRecord)) {
                    // invalid request
                    $this->redirect(U('Wap/Car/care', array('token'=>$this->token, 'wecha_id'=>$this->wecha_id)));
                }
                
                $data['submit_time'] = time();
                $data['update_time'] = time();
                $data['token'] = $this->token;
                $data['wecha_id'] = $this->wecha_id;
                M('car_details')->add($data);
                $this->redirect(U('Wap/Car/care', array('token'=>$this->token, 'wecha_id'=>$this->wecha_id)));
            }
        }
        
        if(!empty($detailRecord)) {
            $this->assign('detailRecord', $detailRecord);
        }
        
        $mapping = $this->constructBrandSeriesCS($this->token);
        $this->assign('mapping', $mapping);
        $this->display();
    }
}

