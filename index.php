<?php

require_once('weather.php');
require_once('city.php');
//require_once('history_today.php');
//error_reporting(E_ALL);
date_default_timezone_set('Asia/Shanghai');

define("TOKEN", "hello");
$wechatObj = new wechatCallbackapiTest();
if (isset($_GET['echostr'])) {
		$wechatObj->valid();
}else{
		$wechatObj->responseMsg();
}

class wechatCallbackapiTest
{
		public function valid()
		{
				$echoStr = $_GET["echostr"];
				if($this->checkSignature()){
						echo $echoStr;
						exit;
				}
		}

		private function checkSignature()
		{
				$signature = $_GET["signature"];
				$timestamp = $_GET["timestamp"];
				$nonce = $_GET["nonce"];

				$token = TOKEN;
				$tmpArr = array($token, $timestamp, $nonce);
				sort($tmpArr);
				$tmpStr = implode( $tmpArr );
				$tmpStr = sha1( $tmpStr );

				if( $tmpStr == $signature ){
						return true;
				}else{
						return false;
				}
		}

		public function responseMsg()
		{
				$postStr = $GLOBALS["HTTP_RAW_POST_DATA"];

				if (!empty($postStr)){
						$postObj = simplexml_load_string($postStr, 'SimpleXMLElement', LIBXML_NOCDATA);
						$fromUsername = $postObj->FromUserName;
						$toUsername = $postObj->ToUserName;
						$keyword = trim($postObj->Content);
						$RX_TYPE = trim($postObj->MsgType);
						$time = time();

						switch ($RX_TYPE)
						{
								 /*case "event":
								       $result = self::receiveEvent($postObj);
								       

								       $textTpl111 = "<xml>
								       <ToUserName><![CDATA[%s]]></ToUserName>
								       <FromUserName><![CDATA[%s]]></FromUserName>
								       <CreateTime>%s</CreateTime>
								       <MsgType><![CDATA[%s]]></MsgType>
								       <Content><![CDATA[%s]]></Content>
								       </xml>";
								       $msgType = "text";
								       $contentStr = $result;
								       $resultStr = sprintf($textTpl111, $fromUsername, $toUsername, $time, $msgType, $contentStr);
								       echo $resultStr;
								   }
								 break;*/
								case "text":
										$textTpl = "<xml>
										<ToUserName><![CDATA[%s]]></ToUserName>
										<FromUserName><![CDATA[%s]]></FromUserName>
										<CreateTime>%s</CreateTime>
										<MsgType><![CDATA[%s]]></MsgType>
										<Content><![CDATA[%s]]></Content>
										<FuncFlag>0</FuncFlag>
										</xml>";
										if (mb_substr($keyword, 0, 2) == '音乐') {
												$music_info = self::getMusicInfo($keyword);
												$music_xml = "<xml>
												<ToUserName><![CDATA[%s]]></ToUserName>
												<FromUserName><![CDATA[%s]]></FromUserName>
												<CreateTime>%s</CreateTime>
												<MsgType><![CDATA[%s]]></MsgType>
												<Music>
												<Title><![CDATA[%s]]></Title>
												<Description><![CDATA[%s]]></Description>
												<MusicUrl><![CDATA[%s]]></MusicUrl>
												<HQMusicUrl><![CDATA[%s]]></HQMusicUrl>
												</Music>
												<FuncFlag>0</FuncFlag>
												</xml>";
												$msgType = 'music';
												$title = $music_info['Title'];
												$description = $music_info['Description'];
												$music_url = $music_info['MusicUrl'];
												$hq_music_url = $music_info['HQMusicUrl'];
												$resultStr = sprintf($music_xml, $fromUsername, $toUsername, $time, $msgType, $title, $description, $music_url, $hq_music_url);
												echo $resultStr;
												break;
								     	} elseif (mb_substr($keyword, 0, 2) == '翻译') {
												$resultStr = self::getTranslateInfo($keyword);	
										   		$msgType = 'text';
										   		$resultStr = sprintf($textTpl, $fromUsername, $toUsername, $time, $msgType, $resultStr);
                                                echo $resultStr;
                                                break;

										} elseif ($keyword == '图文') {
												$item = array(
														'Title' => '图片测试',
														'Description' => 'OFashion',
														'PicUrl' => 'http://pic14.nipic.com/20110522/7411759_164157418126_2.jpg',
														'Url' => 'http://pic14.nipic.com/20110522/7411759_164157418126_2.jpg'
													);
												$news_info = self::transmitNews($item, $fromUsername, $toUsername, $time);
												echo $news_info;
												break;
										} elseif ($keyword == '历史上的今天') {
												$history_str = self::getHistoryInfo();
												$msgType = "text";
												$contentStr = date("Y-m-d H:i:s",time());
												$resultStr = sprintf($textTpl, $fromUsername, $toUsername, $time, $msgType, $history_str);
												echo $resultStr;
												break;

										} else {
												$weather = new weather();
												$city = new city();
												$areaid = $city -> getAreaId($keyword);
												if (!$areaid) {
												$result = '暂无该地区数据';
												} else {
												$result = $weather->getWeather(array('areaid'=>$areaid, 'type'=>'forecast_f'));
												}
												$msgType = "text";
												$contentStr = date("Y-m-d H:i:s",time());
												$resultStr = sprintf($textTpl, $fromUsername, $toUsername, $time, $msgType, $result);
												echo $resultStr;
												break;
										}
								
						}

				}else{
						echo "";
						exit;
				}
		}


		




		private function transmitNews($item, $fromUsername, $toUsername, $time)
		{
				$xmlTpl = "<xml>
						<ToUserName><![CDATA[%s]]></ToUserName>
						<FromUserName><![CDATA[%s]]></FromUserName>
						<CreateTime>%s</CreateTime>
						<MsgType><![CDATA[news]]></MsgType>
						<ArticleCount>1</ArticleCount>
						<Articles>
						<item>
						<Title><![CDATA[%s]]></Title>
						<Description><![CDATA[%s]]></Description>
						<PicUrl><![CDATA[%s]]></PicUrl>
						<Url><![CDATA[%s]]></Url>
						</item>
						</Articles>
						<FuncFlag>1</FuncFlag>
						</xml>";
				$result = sprintf($xmlTpl, $fromUsername, $toUsername, $time, $item['Title'], $item['Description'], $item['PicUrl'], $item['Url']);
				return $result;
		}

		/**
		 *有道翻译
		 */
		 private function getTranslateInfo($keyword)
		 {
		 	$keyword = explode(' ', $keyword)[1];
			if ($keyword == '') {
				return "翻译内容不能为空";
			}
     		/*$api_host = "http://fanyi.youdao.com/";
			$api_method = "openapi.do?";
			$api_params = array(
					'keyfrom' => "mtmsmk",
					'key'     => "1063900181",
					'type'    => "data",
					'doctype' => "json",
					'version' => "1.1",
					'q'       => $keyword
				);
			$url = $api_host.$api_method.http_bulid_query($api_params);*/
		    $url = "http://fanyi.youdao.com/openapi.do?keyfrom=mtmsmk&key=1063900181&type=data&doctype=json&version=1.1&q=$keyword";
			//$url = mb_convert_encoding($url, "utf-8");  

			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, $url);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			$output = curl_exec($ch);
			if (curl_errno($ch)) {
				return 'code'.curl_errno($ch).',reason'.curl_error($ch);
			}
			curl_close($ch);

			$youdao = json_decode($output, true);  
			$result = '';
			switch ($youdao['errorCode']) 
			{
				case 0:
					if (isset($youdao['basic'])) {
						$result .= $youdao['basic']['phonetic']."\n";
						foreach ($youdao['basic']['explains'] as $value) {
							$result .= $value."\n";
						}
					} else {
						$result .= $youdao['translation'][0];
					}
					break;
				default:
					$result .= "系统错误：错误代码：". $errorcode;
					break;
			}
			return trim($result);
		 }

		/**
		 * 获取音乐信息
		 */
		private function getMusicInfo($keyword)
		{
			$music_info = explode(' ', $keyword);
			$title = !empty($music_info[1]) ? $music_info[1] : '祝你平安';
			$url = "http://box.zhangmen.baidu.com/x?op=12&count=1&title={$title}"."$$";
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, $url);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			$data = curl_exec($ch);
			$music = '没有找到这首歌，换一首试试吧！';

			$menus = simplexml_load_string($data, 'SimpleXMLElement', LIBXML_NOCDATA);
			foreach ($menus as $menu) {
				if (isset($menu->encode) && isset($menu->decode) && !strpos($menu->encode, 'baidu.com') && strpos($menu->decode, '.mp3')) {
					$result = substr($menu->encode, 0, strripos($menu->encode,'/')+1).$menu->decode;
					if (!strpos($result, "?") && !strpos($result, 'xcode')) {
						$music = array(
						'Title' => $keyword,
						'Description' => 'OFashion',
						'MusicUrl' => urldecode($result),
						'HQMusicUrl' => urldecode($result)
					   /* 'Title' => '英雄',                                                                                                                            
					    'Description' => 'OFashion',
				        'MusicUrl' => 'http://music.163.com/#/m/song?id=407679169',
						'HQMusicUrl' => urldecode($result) */

						);
						break;
					}
				}
			}
			return $music;
		}

		private function getHistoryInfo()
		{
			include_once('simple_html_dom.php');
			try {
				$url = "http://www.todayonhistory.com/";
				$html_analysis = file_get_html($url);
				if (!isset($html_analysis)) {
					$html_analysis -> clear();
					return "获取失败，请再试一次！";
				} else {
					$contenStr = "历史上的:". date('m') ."月". date('d') ."日\n";
					foreach ($html_analysis->find('div[class="wrap main oh mt18"] ul[class="oh"] div[class="t"] a') as $item) {
							$contenStr .= str_replace(date('m').'月'.date('d').'日', "", $item->plaintext). "\n";
							if (strlen($contenStr) > 2000) {
								break;
							}
					}
					$html_analysis -> clear();
					return trim($contenStr);
				}

			} catch (Exception $e) {

			}
		}

		private function receiveEvent($object)
		{
			$content = "";
			switch ($object -> Event) {
				/*case "subscribe": //关注事件
					$content = "欢迎关注小鲨鱼公众号";
					break;
				case "unsubscribe": //取消关注事件
					$content = "";
					break;*/
				case "CLICK":
					$content = "aaaaaaaaaaa";
					break;
			}
			return $content;
		}











}
