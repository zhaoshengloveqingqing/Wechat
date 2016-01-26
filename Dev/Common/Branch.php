<?php
require_once(COMMON_PATH.'/LbsHelper.php');

class Branch {
    private $token;
    function __construct($token) {
        $this->token = $token;
    }
    /**
     * 获得门店列表，并以距离目标点由远及近返回。
     * 
     * @param $longitude
     *            gps经度
     * @param $latitude
     *            gps纬度
     * @return （单位：千米）
     */
    function getNearbyBranchList_Gps($gpsLng, $gpsLat) {
        $res = LbsHelper::convertGpsToBaiduXY($gpsLng, $gpsLat);
        return $this->getNearbyBranchList($res['longitude'], $res['latitude']);
    }
     /**
     * 获得门店列表，并以距离目标点由远及近返回。
     * 
     * @param $longitude
     *            百度经度
     * @param $latitude
     *            百度纬度
     * @return （单位：千米）
     */
    function getNearbyBranchList($longitude, $latitude) {
        $branches=M('member_card_contact')
                ->where(array('token'=>$this->token))
                ->field('id,cname,longtitude,latitude,picture,tel')
                ->select();
        
        if(!empty($branches) && count($branches) > 0) {
            foreach($branches as &$branch ) {
                if(!empty($branch['picture'])) {
                    $img = M('raw_image')->find($branch['picture']);
                    $branch['picurl'] = $img['link'];
                }else {
                    $branch['picurl'] = '';
                }
                
                $branch['distance'] = LbsHelper::getDistance($longitude, $latitude, $branch['longtitude'], $branch['latitude']);
            }
        }
        
        uasort($branches, array($this, 'cmp'));
        
        return $branches;
        //http://api.map.baidu.com/telematics/v3/distance?waypoints=118.77147503233,32.054128923368;116.3521416286,39.965780080447;116.28215586757,39.965780080447&ak=ZkqN4MTTGPb3lo9gvwGhiScT
    }
    private function cmp($b1, $b2) {
        if($b1['distance'] == $b2['distance']) {
            return 0;
        }
        
        return ($b1['distance'] < $b2['distance']) ? -1 : 1;
    }
   
}
