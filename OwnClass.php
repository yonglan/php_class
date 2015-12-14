<?
class Own {
/**
 * 文本记录函数
 * @param string $word 输入记录的值
 */
	function logRes($word = '') {
		$fp = fopen("logRes.txt", "a");
		flock($fp, LOCK_EX);
		fwrite($fp, "执行日期：" . strftime("%Y-%m-%d %H:%M:%S", time()) . "\r\n" . $word . "\r\n");
		flock($fp, LOCK_UN);
		fclose($fp);
	}

/**
 * curl 模拟post
 * @param $url          post 地址
 * @param $data         post 数据
 * @param string $time  超时时间
 * @return mixed        返回结果
 */
	function curl_post($url, $data, $time = "30") {
		$curl = curl_init();
		curl_setopt($curl, CURLOPT_URL, $url);
		if (is_array($data)) {
			curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($data));
		}
		curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
		curl_setopt($curl, CURLOPT_POST, 1);
		curl_setopt($curl, CURLOPT_TIMEOUT, $time);
		curl_setopt($curl, CURLOPT_HEADER, 0);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
		$out_put = curl_exec($curl);
		if (curl_errno($curl)) {
			echo 'Errno' . curl_error($curl); //捕抓异常
		}
		curl_close($curl);
		return $out_put;
	}

/**
 * 切除过长的字符串，用...代替
 * @param $string       字符串
 * @param $sublen       设置长度
 * @param int $start    开始位置
 * @param string $code
 * @return string       返回字符串结果
 */
	function cut_str($string, $sublen, $start = 0, $code = 'UTF-8') {
		if ($code == 'UTF-8') {
			$pa = "/[\x01-\x7f]|[\xc2-\xdf][\x80-\xbf]|\xe0[\xa0-\xbf][\x80-\xbf]|[\xe1-\xef][\x80-\xbf][\x80-\xbf]|\xf0[\x90-\xbf][\x80-\xbf][\x80-\xbf]|[\xf1-\xf7][\x80-\xbf][\x80-\xbf][\x80-\xbf]/";
			preg_match_all($pa, $string, $t_string);
			if (count($t_string[0]) - $start > $sublen) {
				return join('', array_slice($t_string[0], $start, $sublen)) . "...";
			}

			return join('', array_slice($t_string[0], $start, $sublen));
		} else {
			$start = $start * 2;
			$sublen = $sublen * 2;
			$strlen = strlen($string);
			$tmpstr = '';
			for ($i = 0; $i < $strlen; $i++) {
				if ($i >= $start && $i < ($start + $sublen)) {
					if (ord(substr($string, $i, 1)) > 129) {
						$tmpstr .= substr($string, $i, 2);
					} else {
						$tmpstr .= substr($string, $i, 1);
					}
				}
				if (ord(substr($string, $i, 1)) > 129) {
					$i++;
				}

			}
			if (strlen($tmpstr) < $strlen) {
				$tmpstr .= "...";
			}

			return $tmpstr;
		}
	}

/**
 * 二维数组某一字段值重复去重
 * @param $arr  输入数组
 * @param $key  重复字段名
 * @return mixed
 */
	function assoc_unique($arr, $key) {
		$tmp_arr = array();
		foreach ($arr as $k => $v) {
			if (in_array($v[$key], $tmp_arr)) //搜索$v[$key]是否在$tmp_arr数组中存在，若存在返回true
			{
				unset($arr[$k]);
			} else {
				$tmp_arr[] = $v[$key];
			}
		}
		sort($arr); //sort函数对数组进行排序
		return $arr;
	}

/**
 * 二维数组完全重复去重
 * @param $array2D  输入数组
 * @return array    输出数组
 */
	function array_unique_fb($array2D) {
		foreach ($array2D as $v) {
			$v = join(",", $v); //降维,也可以用implode,将一维数组转换为用逗号连接的字符串
			$temp[] = $v;
		}
		$temp = array_unique($temp); //去掉重复的字符串,也就是重复的一维数组
		foreach ($temp as $k => $v) {
			$temp[$k] = explode(",", $v); //再将拆开的数组重新组装
		}
		return $temp;
	}

	/**
	 * 获取微信缓存的全局ACCESS_TOKEN
	 * @return mixed
	 */
	function get_access_token() {
		$appid = APPID;
		$appsecret = SECRET;
		$res = file_get_contents('access_token.json');
		$result = json_decode($res, true);
		$expires_time = $result["expires_time"];
		$access_token = $result["access_token"];

		if (time() > ($expires_time + 7000)) {
			$url = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=" . $appid . "&secret=" . $appsecret;
			$res = file_get_contents($url);
			$result = json_decode($res, true);
			$access_token = $result["access_token"];
			$expires_time = time();
			file_put_contents('access_token.json', '{"access_token": "' . $access_token . '", "expires_time": ' . $expires_time . '}');
		}
		return $access_token;
	}

/**
 * 获取OPENID
 * @return [string] 返回OPENID
 */
	function get_openid() {
		$appid = APPID;
		$secret = SECRET;
		$code = $_GET['code'];

		if ($code == "") {
			$red_uri = INDEX_URL;
			$red_uri = urldecode($red_uri);
			header("Location:https://open.weixin.qq.com/connect/oauth2/authorize?appid=$appid&redirect_uri=$red_uri&response_type=code&scope=snsapi_base&state=123#wechat_redirect");
		}
		$url1 = "https://api.weixin.qq.com/sns/oauth2/access_token?appid=$appid&secret=$secret&code=$code&grant_type=authorization_code";
		$res1 = file_get_contents($url1);
		$res1 = json_decode($res1, true);
		$openid = $res1['openid'];
		return $openid;
	}

	/**
	 * 5.4之前中文不转义json编码
	 * @param $input    输入字符串
	 * @return string   返回字符串
	 */
	function json_own_encode($input) {
		// 从 PHP 5.4.0 起, 增加了这个选项.
		if (defined('JSON_UNESCAPED_UNICODE')) {
			return json_encode($input, JSON_UNESCAPED_UNICODE);
		}
		if (is_string($input)) {
			$text = $input;
			$text = str_replace('\\', '\\\\', $text);
			$text = str_replace(
				array("\r", "\n", "\t", "\""),
				array('\r', '\n', '\t', '\\"'),
				$text);
			return '"' . $text . '"';
		} else if (is_array($input) || is_object($input)) {
			$arr = array();
			$is_obj = is_object($input) || (array_keys($input) !== range(0, count($input) - 1));
			foreach ($input as $k => $v) {
				if ($is_obj) {
					$arr[] = json_own_encode($k) . ':' . json_own_encode($v);
				} else {
					$arr[] = json_own_encode($v);
				}
			}
			if ($is_obj) {
				return '{' . join(',', $arr) . '}';
			} else {
				return '[' . join(',', $arr) . ']';
			}
		} else {
			return $input . '';
		}
	}

/**
 * 计算两个坐标之间的距离(米)
 * @param float $fP1Lat 起点(纬度)两位的那个
 * @param float $fP1Lon 起点(经度)
 * @param float $fP2Lat 终点(纬度)两位的那个
 * @param float $fP2Lon 终点(经度)
 * @return int
 */
	function getDistance($fP1Lat, $fP1Lon, $fP2Lat, $fP2Lon) {
		$fEARTH_RADIUS = 6378137;
		//角度换算成弧度
		$fRadLon1 = deg2rad($fP1Lon);
		$fRadLon2 = deg2rad($fP2Lon);
		$fRadLat1 = deg2rad($fP1Lat);
		$fRadLat2 = deg2rad($fP2Lat);
		//计算经纬度的差值
		$fD1 = abs($fRadLat1 - $fRadLat2);
		$fD2 = abs($fRadLon1 - $fRadLon2);
		//距离计算
		$fP = pow(sin($fD1 / 2), 2) +
		cos($fRadLat1) * cos($fRadLat2) * pow(sin($fD2 / 2), 2);

		$output = intval($fEARTH_RADIUS * 2 * asin(sqrt($fP)) + 0.5);
		return $output;
	}
/**
 * 百度坐标系转换成标准GPS坐系
 * @param float $lnglat 坐标(如:106.426, 29.553404)
 * @return string 转换后的标准GPS值:
 */
	function BD09LLtoWGS84($fLng, $fLat) {
		// 经度,纬度
		$lnglat = explode(',', $lnglat);
		list($x, $y) = $lnglat;
		$Baidu_Server = "http://api.map.baidu.com/ag/coord/convert?from=0&to=4&x={$x}&y={$y}";
		$result = @file_get_contents($Baidu_Server);
		$json = json_decode($result);
		if ($json->error == 0) {
			$bx = base64_decode($json->x);
			$by = base64_decode($json->y);
			$GPS_x = 2 * $x - $bx;
			$GPS_y = 2 * $y - $by;
			return $GPS_x . ',' . $GPS_y; //经度,纬度
		} else {
			return $lnglat;
		}

	}

	/**
	 * 获取客户端ip
	 * @return string   ip
	 */
	function get_ip() {
		$on_line_ip = '';
		if (getenv('HTTP_CLIENT_IP') && strcasecmp(getenv('HTTP_CLIENT_IP'), 'unknown')) {
			$on_line_ip = getenv('HTTP_CLIENT_IP');
		} elseif (getenv('HTTP_X_FORWARDED_FOR') && strcasecmp(getenv('HTTP_X_FORWARDED_FOR'), 'unknown')) {
			$on_line_ip = getenv('HTTP_X_FORWARDED_FOR');
		} elseif (getenv('REMOTE_ADDR') && strcasecmp(getenv('REMOTE_ADDR'), 'unknown')) {
			$on_line_ip = getenv('REMOTE_ADDR');
		} elseif (isset($_SERVER['REMOTE_ADDR']) && $_SERVER['REMOTE_ADDR'] && strcasecmp($_SERVER['REMOTE_ADDR'], 'unknown')) {
			$on_line_ip = $_SERVER['REMOTE_ADDR'];
		}
		return $on_line_ip;
	}

	/**
	 * 计算不同时间的月份差
	 * @param $data1    之前的时间戳
	 * @param $data2    之后的时间戳
	 * @return mixed    之间距离的月份
	 */
	function diff_date_mouth($data1, $data2) {
		//如果￥data1时间晚于data2的时间，则交换
		if ($data1 > $data2) {
			$temp = $data1;
			$data1 = $data2;
			$data2 = $temp;
		}
		list($y1, $m1) = explode("-", date("Y-m", $data1));
		list($y2, $m2) = explode("-", date("Y-m", $data2));
		$rs = ($y2 - $y1) * 12 + ($m2 - $m1);
		return $rs;
	}
}
?>