<?php

/**
 *	中国天气网接口调用
 */
define('BASE_URL', 'http://open.weather.com.cn/data/?areaid=%s&type=%s&date=%s&appid=%s');
define('APPID', "11d03ebac911623d");
define('PRIVATE_KEY', "aeca2d_SmartWeatherAPI_debda62");

class weather {
		//$data = array('areaid', 'type')
		public function getWeather($data)
		{
			$areaid = $data['areaid'];
			$type = $data['type'];
			$date = date("YmdHi",time());
			$appid = APPID;
			$uri = sprintf(BASE_URL, $areaid, $type, $date, $appid);
			$key = self::setKey($uri);
			$real_appid = substr($appid,0,6);
			$real_uri = sprintf(BASE_URL, $areaid, $type, $date, $real_appid);
			$url = $real_uri."&key=".urlencode($key);

			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, $url);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
			curl_setopt($ch, CURLOPT_HEADER, 0); 
			$result = curl_exec($ch);
			curl_close($ch);
			return self::resolveData($result);
		}

		private function setKey($uri) 
		{
				$key=base64_encode(hash_hmac('sha1',$uri,PRIVATE_KEY,TRUE));	
				return $key;
		}
		
		private static $_wind_direction = array(
			0 => '无持续风向',
			1 => '东北风',
			2 => '东风',
			3 => '东南风',
			4 => '南风',
			5 => '西南风',
			6 => '西风',
			7 => '西北风',
			8 => '北风',
			9 => '旋转风'
		);

		private static $_wind_power = array(
			0 => '微风',
			1 => '3-4级	',
			2 => '4-5级	',
			3 => '5-6级	',
			4 => '6-7级	',
			5 => '7-8级	',
			6 => '8-9级	',
			7 => '9-10级',
			8 => '10-11级',
			9 => '11-12级'
		);

		private function resolveData($json)
		{
			$orig_data = json_decode($json, TRUE);
			$wind_direc_1 = self::$_wind_direction[$orig_data['f']['f1'][0]['fe']];
			$wind_power_1 = self::$_wind_power[$orig_data['f']['f1'][0]['fg']];
			$str = "{$orig_data['c']['c3']}城市天气预报\n".
				   "预报日期：{$orig_data['f']['f0']}\n".
				   "城市级别：{$orig_data['c']['c10']}\n".
				   "今天白天气温：{$orig_data['f']['f1'][0]['fc']}，{$wind_direc_1}{$wind_power_1},夜间气温：{$orig_data['f']['f1'][0]['fd']}\n".
				   "明天白天气温：{$orig_data['f']['f1'][1]['fc']}夜间气温：{$orig_data['f']['f1'][1]['fd']}\n".
				   "后天白天气温：{$orig_data['f']['f1'][2]['fc']}夜间气温：{$orig_data['f']['f1'][2]['fd']}";
			return $str;  
		}
}
