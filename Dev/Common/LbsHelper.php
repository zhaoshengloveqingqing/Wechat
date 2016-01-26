<?php

class LbsHelper {
    public static function convertGpsToBaiduXY($gpsLng, $gpsLat) {
        $longitude = $gpsLng;
        $latitude = $gpsLat;
        
        
        $url = 'http://api.map.baidu.com/ag/coord/convert?from=0&to=4&x='.$gpsLng.'&y='.$gpsLat;
        
        $s = curl_init();
        curl_setopt($s, CURLOPT_URL, $url);
        $headers = array(
            'Accept-Language: zh-CN,zh;q=0.8',
            'Accept: application/json, text/javascript, */*; q=0.01' 
            );
        
        curl_setopt($s,CURLOPT_HEADER,true); 
        curl_setopt($s,CURLOPT_HTTPHEADER, $headers); 
        curl_setopt($s,CURLOPT_FOLLOWLOCATION,true); 
        curl_setopt($s,CURLOPT_RETURNTRANSFER,true);
        curl_setopt($s,  CURLINFO_HEADER_OUT, true);
        curl_setopt($s, CURLOPT_USERAGENT, 'Mozilla/5.0 (compatible; MSIE 5.01; Windows NT 5.0)');
        curl_setopt($s, CURLOPT_HTTPGET,true);
        
        Log::record('convertGpsToBaiduXY fetching: '.$url);
        Log::save();
        
        $content = curl_exec($s); 
        if(curl_errno($s)){//出错则显示错误信息
            Log::record('convertGpsToBaiduXY: fail : '. curl_error($s));
            Log::save();
            curl_close($s); 
            return array('longitude'=>$longitude, 'latitude'=>$latitude);
        }
        
        $headerSize = curl_getinfo($s, CURLINFO_HEADER_SIZE);
        
        curl_close($s); 
        
        
        $header = substr($content, 0, $headerSize);
        $body = substr($content, $headerSize);
        
        Log::record('convertGpsToBaiduXY response: '.$body, Log::INFO );
        $response = json_decode($body);
        
        if(empty($response) || $response->error !== 0) {
            
            Log::record('convertGpsToBaiduXY no change on input');
            Log::save();
        }else {
            $longitude = base64_decode($response->x);
            $latitude = base64_decode($response->y);
            Log::record('convertGpsToBaiduXY convert succeeds:(lng,lat)=>'.$longitude.','.$latitude);
            Log::save();
        }
        
        return array('longitude'=>$longitude, 'latitude'=>$latitude);
    }
            
        /** 地球半径（单位：公里） */
    const EARTH_RADIUS_KM = 6378.137;

    /**
     * 根据经纬度计算地球上任意两点间的距离
     * 
     * @param lng1
     *            起点经度
     * @param lat1
     *            起点纬度
     * @param lng2
     *            终点经度
     * @param lat2
     *            终点纬度
     * @return 两点距离（单位：千米）
     */
    public static function getDistance($lng1, $lat1, $lng2,$lat2) {
            $radLat1 = deg2rad($lat1);
            $radLat2 = deg2rad($lat2);
            $radLng1 = deg2rad($lng1);
            $radLng2 = deg2rad($lng2);
            $deltaLat = $radLat1 - $radLat2;
            $deltaLng = $radLng1 - $radLng2;
            
            $distance = 2 * asin(sqrt(pow(
                            sin($deltaLat / 2), 2)
                            + cos($radLat1)
                            * cos($radLat2)
                            * pow(sin($deltaLng / 2), 2)));
            
            $distance = $distance * self::EARTH_RADIUS_KM;
            $distance = round($distance * 10000) / 10000;
            
            return $distance;
    }
}
