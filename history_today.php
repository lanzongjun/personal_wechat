<?php
function getHistoryInfo()
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
			foreach ($html_analysis->find('div[class="wrap main oh mt18"] ul[class="oh"] li') as $item) {
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