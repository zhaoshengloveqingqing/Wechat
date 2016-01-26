<?php
class TipsAction extends ManageAction
{

    public function check()
    {
        $order_type = $this->_get('t');
        $remind_info = null;
        switch ($order_type) 
        {
            case 'Booking':
                $remind_info = $this->getNewReserveCount();
                break;

            case 'Shop':
                $remind_info = $this->getNewShopOrderCount();
                break;

            case 'Dining':
                $remind_info = $this->getNewDiningOrderCount();
                break;

            case 'Hotel':
                $remind_info = $this->getNewHotelOrderCount();
                break;
            
            default:
                # code...
                break;
        }
            
            Log::save();
        $this->ajaxReturn($remind_info, "OK", 1);
    }

    private function getNewHotelOrderCount()
    {
		$where = array('token'=>$this->token,'order_status'=>3);

		$hotel_id = $_SESSION['manage_hotel_branch'];
        $where = array('token'=>$this->token);
        if (!empty($hotel_id)) 
        {
            $where['hid'] = $hotel_id;
        }
        $new_orders = M('Hotel_order')->where()->select();
        $tipMsgs = array();
        $id = "";
        if (count($new_orders) > 0) {
            $id = $id."order_";
            foreach ($new_orders as $order) {
                $id = $id.$tipId.$order['id'];
            }
            $tipMsg["href"] = U('Hotel/orderList',array('mid'=>$new_orders[0]['hid']));
            $tipMsg["type"] = "order";
            $tipMsg["num"] = count($new_orders);
            array_push($tipMsgs, $tipMsg);
        }
        $tips["id"]  = $id;
        $tips["msg"] = $tipMsgs;
        $tips["num"] = count($tipMsgs);    
        return $tips;
    }

    private function getNewReserveCount()
    {
        $new_orders = M('Host_order')->where(array('token'=>$this->token,'order_status'=>3))->select();
        $tipMsgs = array();
        $id = "";
        if (count($new_orders) > 0) {
            $id = $id."order_";
            foreach ($new_orders as $order) {
                $id = $id.$tipId.$order['id'];
            }
            $tipMsg["href"] = U('Booking/orderList',array('mid'=>$new_orders[0]['hid']));
            $tipMsg["type"] = "order";
            $tipMsg["num"] = count($new_orders);
            array_push($tipMsgs, $tipMsg);
        }
        $tips["id"]  = $id;
        $tips["msg"] = $tipMsgs;
        $tips["num"] = count($tipMsgs);    
        return $tips;
    }

    private function getNewShopOrderCount()
    {
    	$new_orders = M('b2c_order')->where(array('token'=>$this->token,'status'=>1))->select();
        $tipMsgs = array();
        $id = "";
        if (count($new_orders) > 0) {
            $id = $id."order_";
            foreach ($new_orders as $order) {
                $id = $id.$tipId.$order['id'];
            }
            $tipMsg["href"] = U('Shop/orderList');
            $tipMsg["type"] = "order";
            $tipMsg["num"] = count($new_orders);
            array_push($tipMsgs, $tipMsg);
        }
        $tips["id"]  = $id;
        $tips["msg"] = $tipMsgs;
        $tips["num"] = count($tipMsgs);    
        return $tips;
    }

    private function getNewDiningOrderCount() 
    {
        parent::checkAction("Dining-order");

        $rest_id = $_SESSION['manage_dine_branch'];
        
        $where = array('token'=>$this->token);
        if (!empty($rest_id)) 
        {
            $where['id'] = $rest_id;
        }

        $rest = M('dine_rest')->where($where)->find();
        
        if ($rest == false) 
        {
            return array();
        }
    	$new_orders = M('dine_order')->where(array('rest_id'=>$rest['id'],'status'=>2))->select();
        $tipMsgs = array();
        $id = "";
        if (count($new_orders) > 0) {
            $id = $id."order_";
            foreach ($new_orders as $order) {
                $id = $id.$order['id'];
            }
            $tipMsg["href"] = U('Dining/orderList', array('status'=>2));
            $tipMsg["type"] = "order";
            $tipMsg["num"] = count($new_orders);
            array_push($tipMsgs, $tipMsg);
        }
        $tips["id"]  = $id;
        $tips["msg"] = $tipMsgs;
        $tips["num"] = count($tipMsgs);    
        return $tips;
    }
}



?>