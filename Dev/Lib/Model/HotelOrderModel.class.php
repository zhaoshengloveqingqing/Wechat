<?php 
/**
* 
*/
class HotelOrderModel extends Model
{
    
    protected function gettoken(){
        return session('token');
    }

    public static function roll_inventory($order_id,$status)
    {
        if(!in_array($status, array(2,4,5) ) )//2，用户取消，4商家取消，5 checkout
        {
            return;
        }
        $order = M('Hotel_order')->where(array('id'=>$order_id))->field('id,room_id,order_status,sn,book_num')->find();
        if($order)
        {
            $room = M('hotel_room')->where(array('id' => $order['room_id'] ))->field('id,inventory,open_inventory')->find();
                //dump(M('hotel_room')->getlastsql());
                //exit();
            if($room['open_inventory'] == 1)
            {
                $room['inventory'] = $room['inventory'] + $order['book_num'];
                M('hotel_room')->where(array('id' => $order['room_id'] ))->save($room);
            }
            //$order['status'] = $status;
            //M('Hotel_order')->where(array('id'=>$order_id))->save($order);
        }
    }
}
 ?>