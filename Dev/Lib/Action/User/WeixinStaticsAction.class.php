<?php
class WeixinStaticsAction extends UserAction
{

    public function index()
    {
    	$months_before = $this->_get('month_before','intval', 0);
    	if ($months_before > 11 || $months_before < 0) 
    	{
    		$months_before = 0;
    	}

    	$year = date("Y",strtotime("-$months_before months",time()));
    	$month = date("m",strtotime("-$months_before months",time()));

        $month_options = '';
        for ($i=0; $i < 12; $i++) 
        { 
        	if ($i == $months_before) 
        	{
        		$month_options .= '<option value="'.$i.'" selected>'.date("Y-m",strtotime("-$i months",time())).'</option>';
        	}
        	else
        	{
        		$month_options .= '<option value="'.$i.'">'.date("Y-m",strtotime("-$i months",time())).'</option>';
        	}
        }

        $this->assign('month_options', $month_options);


        $last_date = date('t'); //获取当前月的最后一天
        $start_epoch = mktime(0, 0, 0, $month, 1, $year);
        $end_epoch = mktime(0, 0, 0, $month, $last_date, $year);

        $where = array('to_public_token'=>$this->token,'receive_time'=> array('between',"$start_epoch,$end_epoch"));
        $static_db = M('wx_access');
        $static_list = $static_db->where($where)->order('access_id desc')->select();
        
        $day_statics = array();
        foreach ($static_list as $key => $static_item) 
        {
            $day = date('d', $static_item['receive_time']);
            if (!isset($day_statics[$day])) 
            {
                $day_statics[$day] = array();
            }
            
            if ($static_item['msg_type'] == 'event' && $static_item['event'] == 'subscribe') 
            {
                empty($day_statics[$day]['subscriber_num'])? $day_statics[$day]['subscriber_num'] = 1 : $day_statics[$day]['subscriber_num'] += 1; 
            } 
            else if ($static_item['msg_type'] == 'event' && $static_item['event'] == 'unsubscribe') 
            {
                empty($day_statics[$day]['unsubscriber_num'])? $day_statics[$day]['unsubscriber_num']= 1 : $day_statics[$day]['unsubscriber_num'] += 1; 
            }
            else if ($static_item['msg_type'] == 'text')
            {
                $day_statics[$day]['text'] = empty($day_statics[$day]['text'])? 1 : $day_statics[$day]['text'] + 1; 
            }
            else if ($static_item['msg_type'] == 'event' && $static_item['event'] == 'CLICK') 
            {
                $day_statics[$day]['click'] = empty($day_statics[$day]['click'])?  1 : $day_statics[$day]['click'] + 1; 
            }
            else if ($static_item['msg_type'] == 'location') 
            {
                empty($day_statics[$day]['location'])? $day_statics[$day]['location'] = 1 : $day_statics[$day]['location'] += 1; 
            }
            else if ($static_item['msg_type'] == 'image') 
            {
                empty($day_statics[$day]['image'])? $day_statics[$day]['image'] = 1 : $day_statics[$day]['image']+= 1; 
            }
            else if ($static_item['msg_type'] == 'voice') 
            {
                empty($day_statics[$day]['voice'])? $day_statics[$day]['voice'] = 1 : $day_statics[$day]['voice'] += 1; 
            }
            empty($day_statics[$day]['total'])? $day_statics[$day]['total'] = 1 : $day_statics[$day]['total'] += 1; 
        }
        ksort($day_statics);
        $this->assign('day_statics',$day_statics);

        $xml='<chart caption="'.$month.'月统计图" xAxisName="日期" yAxisName="" labelStep="" showNames="" showValues="" rotateNames="" showColumnShadow="1" animation="1" showAlternateHGridColor="0" AlternateHGridColor="ff5904" divLineColor="D0DCE4" divLineAlpha="100" alternateHGridAlpha="5"   formatNumberScale="0"  baseFontColor="666666" baseFont="宋体" baseFontSize="12" outCnvBaseFontSize="12"  canvasBorderThickness="1" canvasBorderColor="D0DCE4"  bgColor="FFFFFF" bgAlpha="0"  showBorder="0"  lineColor="0F6FCF" lineThickness="3"  anchorBorderColor="FFFFFF" anchorBorderThickness="2" anchorBgColor="0F6FCF"   numDivLines="2" numVDivLines="3"><categories>';
        $subscriber_items	= '';
        $text_items         = '';
        $click_items        = '';
        foreach ($day_statics as $key => $value) 
        {
            $xml .= '<category label="'.$key.'"/>';
            $subscriber_items .= '<set value="'.$day_statics[$key]['subscriber_num'].'"/>';
            $text_items .= '<set value="'.$day_statics[$key]['text'].'"/>';
            $click_items .= '<set value="'.$day_statics[$key]['click'].'"/>';
        }

        $xml.='</categories>'
            .'<dataset seriesName="关注数" color="1D8BD1" anchorBorderColor="1D8BD1" anchorBgColor="1D8BD1">'.$subscriber_items.'</dataset>'
            .'<dataset seriesName="文本请求数" color="2AD62A" anchorBorderColor="2AD62A" anchorBgColor="2AD62A">'.$text_items.'</dataset>'
            .'<dataset seriesName="点击数" color="A2FF01" anchorBorderColor="A2FF01" anchorBgColor="A2FF01">'.$click_items.'</dataset><styles><definition><style name="CaptionFont" type="font" size="12"/></definition><application><apply toObject="CAPTION" styles="CaptionFont"/><apply toObject="SUBCAPTION" styles="CaptionFont"/></application></styles></chart>';
        Log::record('xml:'.$xml);
        $this->assign('xml',$xml);
        $this->display();
    }
}


?>