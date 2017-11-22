<?php
class Sender {
	static public $content = '';

	static public function curlGetContent($url, $method = 'POST', $params = array(), $try = 0){
		$ch = curl_init();

		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_HEADER, false);
		curl_setopt($ch, CURLOPT_NOBODY, false);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
		curl_setopt($ch, CURLOPT_USERAGENT, self::rendomUserAgent());

		if ($proxy = false) {
			curl_setopt($ch, CURLOPT_PROXY, self::rendomProxy());
		}

		if ($params && $method == 'POST') {
			curl_setopt($ch, CURLOPT_POST, true);
			curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($params));
		}

		$content = curl_exec($ch);
		$status_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		curl_close($ch);

		if ($status_code == 200) {
			self::$content = $content;
			self::prepareHtml();
			return self::$content;
		} else if ( $try < 30 ) {
			sleep(1);
			self::curlGetContent($url, $method, $params, $try++);
		} else {
			return '';
		}
	}

	static public function parthUrl($url){
		$query_str = parse_url($url, PHP_URL_QUERY);
		parse_str($query_str, $query_params);
		return array(
			'url' => str_replace('&' . $query_str, '', $url),
			'params' => $query_params
		);
	}

	static public function prepareHtml(){
		$html = self::$content;

		$html = preg_replace('/style=[\'|"](.*?)[\'|"]/', '', $html);
		$html = preg_replace('/<noscript(.|\s)*?noscript>/', '', $html);
		$html = preg_replace('/<iframe(.|\s)*?iframe>/', '', $html);
		$html = preg_replace('/<script(.|\s)*?script>/', '', $html);
		$html = preg_replace('/<style(.|\s)*?style>/', '', $html);
		$html = preg_replace('/<link(.|\s)*?>/', '', $html);
		$html = preg_replace('/<!--(.|\s)*?-->/', '', $html);
		$html = preg_replace("/\t/", " ", $html);
		$html = preg_replace('/\ +/', " ", $html);

		$html = preg_replace("/(\r\n)+/", "\n", $html);
		$html = preg_replace("/\r+/", "\n", $html);
		$html = preg_replace("/[\s*]?\n+[\s*]?/", "\n", $html);
		$html = preg_replace("/\n+/", "\n", $html);

		$html = preg_replace("/src[\s*]?=[\s*]?/", "src=", $html);
		$html = preg_replace_callback(
			'/src=[\'|"](.*?)[\'|"]/',
			function ($matches) {
				if ( isset($matches[1]) && strpos($matches[1], 'http') === false ) {
					global $domain;
					$src = str_replace($matches[1], $domain . trim($matches[1], '/'), $matches[0]);
				}
				return $src;
			},
			$html
		);
		$html = str_replace("href='/", "href='" . $domain, $html);
		$html = str_replace('href="/', 'href="' . $domain, $html);

		self::$content = $html;
	}

	static public function ping($url, $times = 1){
		$result = shell_exec('ping -c ' . $times . ' ' . $url);
		if( !preg_match('/' . $times .' received/', $result) ){
			return false;
		} else {
			return true;
		}
	}

	static public function checkProxy($ip_port){
		//echo -n "GET / HTTP/1.0\r\n\r\n" | nc 188.255.12.241 8087
		$command = 'echo -n "GET / HTTP/1.0\r\n\r\n" | nc ' . str_replace(':', ' ', $ip_port) . ' 2>&1';
		exec($command, $result, $status);
		if ( $status !== 0 || strpos($result,'Connection refused') !== false ){
			return false;
		} else {
			return true;
		}

		/*
		echo -n "GET / HTTP/1.0\r\n\r\n" | nc 188.255.12.241 8087
		(UNKNOWN) [188.255.12.241] 8087 (?) : Connection refused


		echo -n "GET / HTTP/1.0\r\n\r\n" | nc 188.255.12.241 8081
		HTTP/1.1 400 Bad Request
		Server: Web server
		Date: Thu, 14 Sep 2017 11:24:31 GMT
		Content-Type: text/html
		Content-Length: 171
		Connection: close

		<html>
		<head><title>400 Bad Request</title></head>
		<body bgcolor="white">
		<center><h1>400 Bad Request</h1></center>
		<hr><center>Web server</center>
		</body>
		</html>
		*/
	}

	static public function rendomProxy(){
		// https://hidemy.name/ru/proxy-list/
		$values = array(
			'188.255.12.241' => '8081',
			'37.187.25.3' => '8080',
			'37.187.25.3' => '8110',
			'163.172.86.64' => '3128',
			'217.182.247.188' => '3128',
			'217.182.247.188' => '8888',
			'92.222.146.65' => '9999',
			'163.172.136.39' => '3128',
			'151.80.156.147' => '8080',
			'5.135.195.166' => '3128',
			'163.172.144.60' => '3128',
			'163.172.29.111' => '3128',
			'92.222.180.156' => '1080',
			'203.190.14.22' => '8080',
			'177.80.139.66' => '14866',
			'179.218.94.193' => '60699',
			'103.50.214.210' => '80',
			'139.59.114.21' => '8080',
			'117.240.79.20' => '3128',
			'103.15.62.113' => '8080',
			'105.22.34.118' => '8080',
			'103.252.186.212' => '8080',
			'139.59.118.0' => '8080',
			'103.240.100.106' => '8080',
			'103.242.152.113' => '8080',
			'139.59.21.143' => '3128',
			'139.59.69.30' => '3128',
			'139.59.2.223' => '8888',
			'27.106.62.214' => '8080',
			'103.10.52.83' => '1886',
			'101.255.76.50' => '80',
			'109.194.154.13' => '1090',
			'110.171.226.67' => '3128',
			'113.53.254.21' => '65103',
			'120.29.152.86' => '65103',
			'109.87.23.100' => '8080',
			'114.199.118.186' => '8080',
			'118.151.209.114' => '9090',
			'107.172.4.197' => '1080',
			'105.235.201.250' => '8080',
			'103.35.168.106' => '8080',
			'108.168.26.136' => '53281',
			'101.255.56.134' => '53281',
			'103.20.148.45' => '46324',
			'110.170.150.162' => '8080',
			'103.12.161.1' => '65103',
			'83.171.109.200' => '8080',
			'110.50.84.162' => '8080',
			'121.52.149.162' => '8080',
			'103.213.236.122' => '8080',
			'1.179.181.149' => '8080',
			'103.25.136.189' => '8080',
			'107.173.46.40' => '1080',
			'112.140.184.147' => '3129',
			'103.28.1.246' => '8080',
			'110.78.165.230' => '8080',
			'111.67.71.12' => '8080',
			'109.74.115.38' => '8080',
			'108.61.196.143' => '3128',
			'103.78.53.79' => '65103',
			'103.41.245.9' => '8080',
			'103.228.118.249' => '53281',
			'103.20.148.44' => '18186',
			'103.10.52.83' => '3041',
		);

		$ip = array_rand($values);

		$ip_port = $ip . ':' . $values[$ip];

		if( !self::checkProxy($ip_port) ){
			$ip_port = self::rendomProxy();
		}

		return $ip_port;
	}

	static public function rendomUserAgent(){
		$values = array(
			'Mozilla/5.0 (Windows NT 6.1) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/41.0.2228.0 Safari/537.36',
			'Opera/9.80 (Windows NT 6.2; Win64; x64) Presto/2.12 Version/12.16',
			'Mozilla/5.0 (Windows NT 6.1; WOW64; Trident/7.0; rv:11.0) like Gecko',
			'Mozilla/5.0 (compatible; MSIE 9.0; Windows NT 6.1; Trident/5.0)',
			'Mozilla/5.0 (compatible; MSIE 9.0; Windows NT 6.1; WOW64; Trident/5.0)',
			'Mozilla/5.0 (Windows NT 6.3; WOW64; rv:45.0) Gecko/20100101 Firefox/45.0',
			'Mozilla/5.0 (Windows NT 6.1; Trident/7.0; rv:11.0) like Gecko',
			'Mozilla/5.0 (compatible; MSIE 10.0; Windows NT 6.1; WOW64; Trident/6.0)',
			'Mozilla/4.0 (compatible; MSIE 7.0; Windows NT 6.1; WOW64; Trident/5.0; SLCC2; .NET CLR 2.0.50727; .NET CLR 3.5.30729; .NET CLR 3.0.30729; Media Center PC 6.0; .NET4.0C; .NET4.0E)',
			'Mozilla/5.0 (Windows NT 6.3; WOW64; Trident/7.0; rv:11.0) like Gecko',
			'Mozilla/5.0 (Windows NT 5.1) AppleWebKit/537.11 (KHTML like Gecko) Chrome/23.0.1271.95 Safari/537.11',
			'Mozilla/5.0 (Windows NT 6.1; WOW64; rv:31.0) Gecko/20100101 Firefox/31.0',
			'Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML like Gecko) Chrome/36.0.1985.143 Safari/537.36',
			'Mozilla/5.0 (Windows NT 6.1; WOW64; rv:32.0) Gecko/20100101 Firefox/32.0',
			'Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML like Gecko) Chrome/31.0.1650.63 Safari/537.36',
			'Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML like Gecko) Chrome/35.0.1916.153 Safari/537.36',
			'Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML like Gecko) Chrome/37.0.2062.120 Safari/537.36',
			'Mozilla/5.0 (Windows NT 6.3; WOW64; rv:54.0) Gecko/20100101 Firefox/54.0',
			'Mozilla/5.0 (Windows NT 6.1) AppleWebKit/537.11 (KHTML like Gecko) Chrome/23.0.1271.95 Safari/537.11',
			'Mozilla/5.0 (compatible; MSIE 10.0; Windows NT 6.1; Trident/6.0)',
			'Mozilla/5.0 (Windows NT 6.1; rv:31.0) Gecko/20100101 Firefox/31.0',
			'Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.31 (KHTML like Gecko) Chrome/26.0.1410.64 Safari/537.31',
			'Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML like Gecko) Chrome/36.0.1985.125 Safari/537.36',
			'Mozilla/5.0 (Windows NT 5.1; rv:31.0) Gecko/20100101 Firefox/31.0',
			'Mozilla/5.0 (Windows NT 6.1) AppleWebKit/537.36 (KHTML like Gecko) Chrome/36.0.1985.143 Safari/537.36',
			'Mozilla/5.0 (Windows NT 6.1; WOW64; rv:40.0) Gecko/20100101 Firefox/40.1',
			'Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML like Gecko) Chrome/27.0.1453.110 Safari/537.36',
			'Mozilla/5.0 (compatible; MSIE 9.0; Windows NT 6.0; Trident/5.0)',
			'Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML like Gecko) Chrome/30.0.1599.101 Safari/537.36',
			'Mozilla/4.0 (compatible; MSIE 8.0; Windows NT 5.1; Trident/4.0)',
			'Mozilla/5.0 (Windows NT 6.3; WOW64; rv:50.0) Gecko/20100101 Firefox/50.0',
			'Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML like Gecko) Chrome/37.0.2062.103 Safari/537.36',
			'Mozilla/5.0 (Windows NT 6.1) AppleWebKit/537.36 (KHTML like Gecko) Chrome/37.0.2062.120 Safari/537.36',
			'Mozilla/5.0 (Windows NT 6.1; WOW64; rv:26.0) Gecko/20100101 Firefox/26.0',
			'Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1; SV1)',
			'Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML like Gecko) Chrome/39.0.2171.95 Safari/537.36',
			'Mozilla/5.0 (Windows NT 6.1) AppleWebKit/537.36 (KHTML like Gecko) Chrome/31.0.1650.63 Safari/537.36',
			'Mozilla/5.0 (Windows NT 5.1) AppleWebKit/537.11 (KHTML like Gecko) Chrome/23.0.1271.64 Safari/537.11',
			'Mozilla/5.0 (Windows NT 6.1; WOW64; rv:30.0) Gecko/20100101 Firefox/30.0',
			'Mozilla/5.0 (Windows NT 6.3; WOW64; rv:31.0) Gecko/20100101 Firefox/31.0',
			'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_11_2) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/48.0.2564.48 Safari/537.36',
			'Mozilla/5.0 (compatible; MSIE 10.0; Windows NT 6.2; WOW64; Trident/6.0)',
			'Mozilla/5.0 (Windows NT 5.1) AppleWebKit/537.36 (KHTML like Gecko) Chrome/31.0.1650.63 Safari/537.36',
			'Mozilla/5.0 (Windows NT 6.3; WOW64; rv:32.0) Gecko/20100101 Firefox/32.0',
			'Mozilla/5.0 (Windows NT 6.1; WOW64; rv:27.0) Gecko/20100101 Firefox/27.0',
			'Mozilla/5.0 (Windows NT 6.3; WOW64) AppleWebKit/537.36 (KHTML like Gecko) Chrome/36.0.1985.143 Safari/537.36',
			'Mozilla/5.0 (Windows NT 6.1) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/43.0.2357.134 Safari/537.36',
			'Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML like Gecko) Chrome/33.0.1750.154 Safari/537.36',
			'Mozilla/5.0 (Windows NT 6.1; WOW64; rv:25.0) Gecko/20100101 Firefox/25.0',
			'Mozilla/5.0 (Windows NT 6.1; rv:21.0) Gecko/20100101 Firefox/21.0',
			'Mozilla/5.0 (iPhone; CPU iPhone OS 711 like Mac OS X) AppleWebKit/537.51.2 (KHTML like Gecko) Version/7.0 Mobile/11D201 Safari/9537.53',
			'Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML like Gecko) Chrome/30.0.1599.69 Safari/537.36',
			'Mozilla/5.0 (Windows NT 6.1; rv:28.0) Gecko/20100101 Firefox/28.0',
			'Mozilla/5.0 (Windows NT 5.1) AppleWebKit/537.36 (KHTML like Gecko) Chrome/29.0.1547.76 Safari/537.36',
			'Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.22 (KHTML like Gecko) Chrome/25.0.1364.172 Safari/537.22',
			'Mozilla/5.0 (Windows NT 6.1; rv:38.0) Gecko/20100101 Firefox/38.0',
			'Mozilla/5.0 (Windows NT 6.1) AppleWebKit/537.36 (KHTML like Gecko) Chrome/29.0.1547.76 Safari/537.36',
			'Mozilla/4.0 (compatible; MSIE 8.0; Windows NT 6.1; Trident/4.0; SLCC2; .NET CLR 2.0.50727; .NET CLR 3.5.30729; .NET CLR 3.0.30729; Media Center PC 6.0)',
			'Mozilla/5.0 (Windows NT 6.1; rv:22.0) Gecko/20100101 Firefox/22.0',
			'Mozilla/5.0 (Windows NT 6.1) AppleWebKit/537.36 (KHTML like Gecko) Chrome/31.0.1650.57 Safari/537.36',
			'Mozilla/5.0 (Windows NT 5.1; rv:28.0) Gecko/20100101 Firefox/28.0',
			'Mozilla/5.0 (Windows NT 6.1) AppleWebKit/537.36 (KHTML like Gecko) Chrome/27.0.1453.116 Safari/537.36',
			'Mozilla/4.0 (compatible; MSIE 8.0; Windows NT 5.1; Trident/4.0; .NET CLR 2.0.50727)',
			'Mozilla/5.0 (Windows NT 5.1; rv:32.0) Gecko/20100101 Firefox/32.0',
			'Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML like Gecko) Chrome/39.0.2171.71 Safari/537.36',
			'Mozilla/5.0 (Windows NT 6.1; rv:23.0) Gecko/20100101 Firefox/23.0',
			'Mozilla/5.0 (Windows NT 6.1) AppleWebKit/537.36 (KHTML like Gecko) Chrome/28.0.1500.72 Safari/537.36',
			'Mozilla/5.0 (iPad; CPU OS 712 like Mac OS X) AppleWebKit/537.51.2 (KHTML like Gecko) Version/7.0 Mobile/11D257 Safari/9537.53',
			'Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML like Gecko) Chrome/33.0.1750.146 Safari/537.36',
			'Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML like Gecko) Chrome/40.0.2214.115 Safari/537.36',
			'Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.11 (KHTML like Gecko) Chrome/23.0.1271.97 Safari/537.11',
			'Mozilla/5.0 (Windows NT 6.1) AppleWebKit/537.36 (KHTML like Gecko) Chrome/37.0.2062.124 Safari/537.36',
			'Mozilla/5.0 (Windows NT 6.1; rv:17.0) Gecko/20100101 Firefox/17.0',
			'Mozilla/5.0 (Windows NT 10.0; WOW64; Trident/7.0; rv:11.0) like Gecko',
			'Mozilla/5.0 (Windows NT 6.3; WOW64) AppleWebKit/537.36 (KHTML like Gecko) Chrome/39.0.2171.95 Safari/537.36',
			'Mozilla/5.0 (Windows NT 6.1; rv:35.0) Gecko/20100101 Firefox/35.0',
			'Mozilla/5.0 (Windows NT 6.1) AppleWebKit/537.36 (KHTML like Gecko) Chrome/28.0.1500.95 Safari/537.36',
			'Mozilla/5.0 (Windows NT 5.1) AppleWebKit/537.36 (KHTML like Gecko) Chrome/27.0.1453.116 Safari/537.36',
			'Mozilla/5.0 (iPad; CPU OS 704 like Mac OS X) AppleWebKit/537.51.1 (KHTML like Gecko) Version/7.0 Mobile/11B554a Safari/9537.53',
			'Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML like Gecko) Chrome/32.0.1700.76 Safari/537.36',
			'Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/55.0.2883.87 Safari/537.36',
			'Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/51.0.2704.103 Safari/537.36',
			'Mozilla/5.0 (Windows NT 5.1; rv:11.0) Gecko/20100101 Firefox/11.0',
			'Mozilla/5.0 (Windows NT 6.1; rv:34.0) Gecko/20100101 Firefox/34.0',
			'Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.4 (KHTML like Gecko) Chrome/22.0.1229.94 Safari/537.4',
			'Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML like Gecko) Chrome/33.0.1750.117 Safari/537.36',
			'Mozilla/4.0 (compatible; MSIE 8.0; Windows NT 5.1; Trident/4.0; InfoPath.2)',
			'Mozilla/5.0 (Windows NT 6.1; rv:19.0) Gecko/20100101 Firefox/19.0',
			'Mozilla/5.0 (Windows NT 5.1) AppleWebKit/537.36 (KHTML like Gecko) Chrome/28.0.1500.72 Safari/537.36',
			'Mozilla/5.0 (Windows NT 6.1; rv:33.0) Gecko/20100101 Firefox/33.0',
			'Mozilla/5.0 (Windows NT 6.1; rv:12.0) Gecko/20100101 Firefox/12.0',
			'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/55.0.2883.87 Safari/537.36',
			'Mozilla/5.0 (Windows NT 5.1) AppleWebKit/537.36 (KHTML like Gecko) Chrome/36.0.1985.125 Safari/537.36',
			'Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.11 (KHTML like Gecko) Chrome/23.0.1271.64 Safari/537.11',
			'Mozilla/5.0 (Windows NT 6.1; rv:20.0) Gecko/20100101 Firefox/20.0',
			'Mozilla/5.0 (Windows NT 6.1) AppleWebKit/537.36 (KHTML like Gecko) Chrome/38.0.2125.111 Safari/537.36',
			'Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.1 (KHTML like Gecko) Chrome/21.0.1180.89 Safari/537.1',
			'Mozilla/5.0 (Windows NT 5.1; rv:18.0) Gecko/20100101 Firefox/18.0',
			'Mozilla/5.0 (Windows NT 6.1) AppleWebKit/537.4 (KHTML like Gecko) Chrome/22.0.1229.94 Safari/537.4',
			'Mozilla/4.0 (compatible; MSIE 7.0; Windows NT 5.1; SV1; .NET CLR 2.0.50727)',
			'Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1; SV1)',
			'Mozilla/5.0 (Windows NT 6.1; WOW64; Trident/7.0; rv:11.0) like Gecko',
			'Mozilla/5.0 (Windows NT 6.3; WOW64; Trident/7.0; rv:11.0) like Gecko',
			'Mozilla/5.0 (Windows NT 6.1; Trident/7.0; rv:11.0) like Gecko',
			'Mozilla/4.0 (compatible; MSIE 8.0; Windows NT 5.1; Trident/4.0)',
			'Mozilla/4.0 (compatible; MSIE 8.0; Windows NT 6.1; Trident/4.0; SLCC2; .NET CLR 2.0.50727; .NET CLR 3.5.30729; .NET CLR 3.0.30729; Media Center PC 6.0; InfoPath.2)',
			'Mozilla/5.0 (compatible; MSIE 9.0; Windows NT 6.1; Trident/5.0)',
			'Mozilla/5.0 (compatible; MSIE 9.0; Windows NT 6.1; WOW64; Trident/5.0)',
			'Mozilla/5.0 (compatible; MSIE 9.0; Windows NT 6.0; Trident/5.0)',
			'Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML like Gecko) Chrome/28.0.1500.95 Safari/537.36',
			'Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML like Gecko) Chrome/28.0.1500.72 Safari/537.36',
			'Mozilla/5.0 (Windows NT 6.1) AppleWebKit/537.36 (KHTML like Gecko) Chrome/28.0.1500.95 Safari/537.36',
			'Mozilla/5.0 (Windows NT 5.1) AppleWebKit/537.36 (KHTML like Gecko) Chrome/28.0.1500.95 Safari/537.36',
			'Mozilla/5.0 (Windows NT 6.1) AppleWebKit/537.36 (KHTML like Gecko) Chrome/28.0.1500.72 Safari/537.36',
			'Mozilla/5.0 (Windows NT 5.1) AppleWebKit/537.36 (KHTML like Gecko) Chrome/28.0.1500.72 Safari/537.36',
			'Mozilla/5.0 (Windows NT 6.2; WOW64) AppleWebKit/537.36 (KHTML like Gecko) Chrome/28.0.1500.95 Safari/537.36',
			'Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML like Gecko) Chrome/29.0.1547.66 Safari/537.36',
			'Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML like Gecko) Chrome/29.0.1547.76 Safari/537.36',
			'Mozilla/5.0 (Windows NT 6.1) AppleWebKit/537.36 (KHTML like Gecko) Chrome/29.0.1547.66 Safari/537.36',
			'Mozilla/5.0 (Macintosh; Intel Mac OS X 1084) AppleWebKit/537.36 (KHTML like Gecko) Chrome/29.0.1547.65 Safari/537.36',
			'Mozilla/5.0 (Windows NT 5.1) AppleWebKit/537.36 (KHTML like Gecko) Chrome/29.0.1547.66 Safari/537.36',
			'Mozilla/5.0 (Windows NT 6.2; WOW64) AppleWebKit/537.36 (KHTML like Gecko) Chrome/29.0.1547.66 Safari/537.36',
			'Mozilla/5.0 (Windows NT 6.2) AppleWebKit/537.36 (KHTML like Gecko) Chrome/29.0.1547.76 Safari/537.36',
			'Mozilla/5.0 (X11; Linux x8664) AppleWebKit/537.36 (KHTML like Gecko) Chrome/29.0.1547.65 Safari/537.36',
			'Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML like Gecko) Chrome/30.0.1599.101 Safari/537.36',
			'Mozilla/5.0 (Windows NT 5.1) AppleWebKit/537.36 (KHTML like Gecko) Chrome/30.0.1599.101 Safari/537.36',
			'Mozilla/5.0 (Windows NT 6.1) AppleWebKit/537.36 (KHTML like Gecko) Chrome/30.0.1599.101 Safari/537.36',
			'Mozilla/5.0 (Windows NT 6.2; WOW64) AppleWebKit/537.36 (KHTML like Gecko) Chrome/30.0.1599.101 Safari/537.36',
			'Mozilla/5.0 (Macintosh; Intel Mac OS X 1090) AppleWebKit/537.36 (KHTML like Gecko) Chrome/30.0.1599.101 Safari/537.36',
			'Mozilla/5.0 (Windows NT 6.3; WOW64) AppleWebKit/537.36 (KHTML like Gecko) Chrome/30.0.1599.101 Safari/537.36',
			'Mozilla/5.0 (Windows NT 6.2) AppleWebKit/537.36 (KHTML like Gecko) Chrome/30.0.1599.101 Safari/537.36',
			'Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML like Gecko) Chrome/31.0.1650.63 Safari/537.36',
			'Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML like Gecko) Chrome/31.0.1650.57 Safari/537.36',
			'Mozilla/5.0 (Windows NT 6.1) AppleWebKit/537.36 (KHTML like Gecko) Chrome/31.0.1650.63 Safari/537.36',
			'Mozilla/5.0 (Windows NT 5.1) AppleWebKit/537.36 (KHTML like Gecko) Chrome/31.0.1650.63 Safari/537.36',
			'Mozilla/5.0 (Windows NT 6.1) AppleWebKit/537.36 (KHTML like Gecko) Chrome/31.0.1650.57 Safari/537.36',
			'Mozilla/5.0 (Windows NT 6.2; WOW64) AppleWebKit/537.36 (KHTML like Gecko) Chrome/31.0.1650.63 Safari/537.36',
			'Mozilla/5.0 (Windows NT 6.2; WOW64) AppleWebKit/537.36 (KHTML like Gecko) Chrome/31.0.1650.57 Safari/537.36',
			'Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML like Gecko) Chrome/32.0.1700.107 Safari/537.36',
			'Mozilla/5.0 (Windows NT 6.1) AppleWebKit/537.36 (KHTML like Gecko) Chrome/32.0.1700.107 Safari/537.36',
			'Mozilla/5.0 (Windows NT 5.1) AppleWebKit/537.36 (KHTML like Gecko) Chrome/32.0.1700.107 Safari/537.36',
			'Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML like Gecko) Chrome/32.0.1700.102 Safari/537.36',
			'Mozilla/5.0 (Windows NT 6.1) AppleWebKit/537.36 (KHTML like Gecko) Chrome/32.0.1700.102 Safari/537.36',
			'Mozilla/5.0 (Windows NT 6.2; WOW64) AppleWebKit/537.36 (KHTML like Gecko) Chrome/32.0.1700.107 Safari/537.36',
			'Mozilla/5.0 (Windows NT 6.3; WOW64) AppleWebKit/537.36 (KHTML like Gecko) Chrome/32.0.1700.107 Safari/537.36',
			'Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML like Gecko) Chrome/33.0.1750.146 Safari/537.36',
			'Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML like Gecko) Chrome/33.0.1750.117 Safari/537.36',
			'Mozilla/5.0 (Windows NT 6.2; WOW64) AppleWebKit/537.36 (KHTML like Gecko) Chrome/33.0.1750.146 Safari/537.36',
			'Mozilla/5.0 (Windows NT 6.3; WOW64) AppleWebKit/537.36 (KHTML like Gecko) Chrome/33.0.1750.146 Safari/537.36',
			'Mozilla/5.0 (Windows NT 6.0) AppleWebKit/537.36 (KHTML like Gecko) Chrome/33.0.1750.117 Safari/537.36',
			'Mozilla/5.0 (Macintosh; Intel Mac OS X 1091) AppleWebKit/537.36 (KHTML like Gecko) Chrome/33.0.1750.117 Safari/537.36',
			'Mozilla/5.0 (Macintosh; Intel Mac OS X 1092) AppleWebKit/537.36 (KHTML like Gecko) Chrome/33.0.1750.146 Safari/537.36',
			'Mozilla/5.0 (Windows NT 6.2) AppleWebKit/537.36 (KHTML like Gecko) Chrome/33.0.1750.117 Safari/537.36',
			'Mozilla/5.0 (X11; Linux x8664) AppleWebKit/537.36 (KHTML like Gecko) Chrome/33.0.1750.117 Safari/537.36',
			'Mozilla/5.0 (Windows NT 6.1; WOW64; rv:21.0) Gecko/20100101 Firefox/21.0',
			'Mozilla/5.0 (Windows NT 5.1; rv:21.0) Gecko/20100101 Firefox/21.0',
			'Mozilla/5.0 (Windows NT 6.1; rv:21.0) Gecko/20100101 Firefox/21.0',
			'Mozilla/5.0 (Windows NT 6.2; WOW64; rv:21.0) Gecko/20100101 Firefox/21.0',
			'Mozilla/5.0 (X11; Ubuntu; Linux x8664; rv:21.0) Gecko/20100101 Firefox/21.0',
			'Mozilla/5.0 (Windows NT 6.1; WOW64; rv:22.0) Gecko/20100101 Firefox/22.0',
			'Mozilla/5.0 (Windows NT 5.1; rv:22.0) Gecko/20100101 Firefox/22.0',
			'Mozilla/5.0 (Windows NT 6.1; rv:22.0) Gecko/20100101 Firefox/22.0',
			'Mozilla/5.0 (Windows NT 6.2; WOW64; rv:22.0) Gecko/20100101 Firefox/22.0',
			'Mozilla/5.0 (Windows NT 5.1; rv:23.0) Gecko/20100101 Firefox/23.0',
			'Mozilla/5.0 (Windows NT 6.1; WOW64; rv:23.0) Gecko/20100101 Firefox/23.0',
			'Mozilla/5.0 (Windows NT 6.1; rv:23.0) Gecko/20100101 Firefox/23.0',
			'Mozilla/5.0 (Windows NT 6.0; rv:23.0) Gecko/20100101 Firefox/23.0',
			'Mozilla/5.0 (Macintosh; Intel Mac OS X 10.8; rv:23.0) Gecko/20100101 Firefox/23.0',
			'Mozilla/5.0 (Macintosh; Intel Mac OS X 10.6; rv:23.0) Gecko/20100101 Firefox/23.0',
			'Mozilla/5.0 (Windows NT 6.1; WOW64; rv:25.0) Gecko/20100101 Firefox/25.0',
			'Mozilla/5.0 (Windows NT 6.1; rv:25.0) Gecko/20100101 Firefox/25.0',
			'Mozilla/5.0 (Windows NT 6.2; WOW64; rv:25.0) Gecko/20100101 Firefox/25.0',
			'Mozilla/5.0 (Windows NT 6.3; WOW64; rv:25.0) Gecko/20100101 Firefox/25.0',
			'Mozilla/5.0 (Windows NT 6.0; rv:25.0) Gecko/20100101 Firefox/25.0',
			'Mozilla/5.0 (Windows NT 6.1; WOW64; rv:26.0) Gecko/20100101 Firefox/26.0',
			'Mozilla/5.0 (X11; Ubuntu; Linux x8664; rv:26.0) Gecko/20100101 Firefox/26.0',
			'Mozilla/5.0 (X11; Linux x8664; rv:26.0) Gecko/20100101 Firefox/26.0',
			'Mozilla/5.0 (Windows NT 6.1; WOW64; rv:26.0) Gecko/20100101 Firefox/26.0 AlexaToolbar/alxf-2.19',
			'Mozilla/5.0 (Macintosh; Intel Mac OS X 10.9; rv:26.0) Gecko/20100101 Firefox/26.0',
			'Mozilla/5.0 (Windows NT 6.1; WOW64; rv:24.0) Gecko/20100101 Firefox/24.0',
			'Mozilla/5.0 (Windows NT 6.1; rv:24.0) Gecko/20100101 Firefox/24.0',
			'Mozilla/5.0 (Windows NT 5.1; rv:24.0) Gecko/20100101 Firefox/24.0',
			'Mozilla/5.0 (Windows NT 6.2; WOW64; rv:24.0) Gecko/20100101 Firefox/24.0',
			'Mozilla/5.0 (Windows NT 6.2; rv:24.0) Gecko/20100101 Firefox/24.0',
			'Mozilla/5.0 (Windows NT 6.1; WOW64; rv:27.0) Gecko/20100101 Firefox/27.0',
			'Mozilla/5.0 (Windows NT 6.1; rv:27.0) Gecko/20100101 Firefox/27.0',
			'Mozilla/5.0 (Windows NT 6.3; WOW64; rv:27.0) Gecko/20100101 Firefox/27.0',
			'Mozilla/5.0 (Windows NT 6.2; WOW64; rv:27.0) Gecko/20100101 Firefox/27.0',
			'Mozilla/5.0 (X11; Linux x8664; rv:27.0) Gecko/20100101 Firefox/27.0',
			'Mozilla/5.0 (Macintosh; Intel Mac OS X 1084) AppleWebKit/536.30.1 (KHTML like Gecko) Version/6.0.5 Safari/536.30.1',
			'Mozilla/5.0 (Macintosh; Intel Mac OS X 1082) AppleWebKit/536.26.17 (KHTML like Gecko) Version/6.0.2 Safari/536.26.17',
			'Mozilla/5.0 (Macintosh; Intel Mac OS X 1075) AppleWebKit/536.30.1 (KHTML like Gecko) Version/6.0.5 Safari/536.30.1',
			'Mozilla/5.0 (Macintosh; Intel Mac OS X 109) AppleWebKit/537.43.7 (KHTML like Gecko) Version/7.0 Safari/537.43.7',
			'Mozilla/5.0 (Macintosh; Intel Mac OS X 1083) AppleWebKit/536.28.10 (KHTML like Gecko) Version/6.0.3 Safari/536.28.10',
			'Mozilla/5.0 (Macintosh; Intel Mac OS X 1068) AppleWebKit/534.57.2 (KHTML like Gecko) Version/5.1.7 Safari/534.57.2',
			'Mozilla/5.0 (Macintosh; Intel Mac OS X 1068) AppleWebKit/534.59.8 (KHTML like Gecko) Version/5.1.9 Safari/534.59.8',
			'Mozilla/5.0 (Macintosh; Intel Mac OS X 1068) AppleWebKit/534.59.10 (KHTML like Gecko) Version/5.1.9 Safari/534.59.10',
			'Mozilla/5.0 (Macintosh; Intel Mac OS X 1074) AppleWebKit/534.57.2 (KHTML like Gecko) Version/5.1.7 Safari/534.57.2',
			'Mozilla/5.0 (Macintosh; Intel Mac OS X 109) AppleWebKit/537.46.5 (KHTML like Gecko) Version/7.0 Safari/537.46.5',
			'Mozilla/5.0 (Macintosh; Intel Mac OS X 109) AppleWebKit/537.71 (KHTML like Gecko) Version/7.0 Safari/537.71',
			'Mozilla/5.0 (Macintosh; Intel Mac OS X 1092) AppleWebKit/537.74.9 (KHTML like Gecko) Version/7.0.2 Safari/537.74.9',
			'Mozilla/5.0 (Macintosh; Intel Mac OS X 109) AppleWebKit/537.54.1 (KHTML like Gecko) Version/7.0 Safari/537.54.1',
			'Mozilla/5.0 (Macintosh; Intel Mac OS X 109) AppleWebKit/537.66 (KHTML like Gecko) Version/7.0 Safari/537.66',
			'Mozilla/5.0 (Macintosh; Intel Mac OS X 109) AppleWebKit/537.59 (KHTML like Gecko) Version/7.0 Safari/537.59',
			'Opera/9.80 (Windows NT 6.1; WOW64) Presto/2.12 Version/12.16',
			'Opera/9.80 (Windows NT 5.1) Presto/2.12 Version/12.16',
			'Opera/9.80 (Windows NT 6.1) Presto/2.12 Version/12.16',
			'Opera/9.80 (Windows NT 6.2; WOW64) Presto/2.12 Version/12.16',
			'Opera/9.80 (X11; Linux x8664) Presto/2.12 Version/12.16',
			'Opera/9.80 (Windows NT 5.1) Presto/2.12 Version/12.15',
			'Opera/9.80 (Windows NT 6.0) Presto/2.12 Version/12.15',
			'Opera/9.80 (X11; Linux i686) Presto/2.12 Version/12.15',
			'Opera/9.80 (X11; Linux x8664) Presto/2.12 Version/12.15',
			'Opera/9.80 (Windows NT 6.1; Edition Yx) Presto/2.12 Version/12.15',
			'Opera/9.80 (Windows NT 5.1) Presto/2.12 Version/12.14',
			'Opera/9.80 (Windows NT 6.2; WOW64) Presto/2.12 Version/12.14',
			'Opera/9.80 (Windows NT 6.2) Presto/2.12 Version/12.14',
			'Opera/9.80 (X11; Linux i686) Presto/2.12 Version/12.14',
			'Opera/9.80 (Windows NT 6.1; Edition Yx) Presto/2.12 Version/12.14',
			'Opera/9.80 (Windows NT 6.1; U; ru) Presto/2.6 Version/10.63',
			'Opera/9.80 (Windows NT 6.1; U; YB/3.5; ru) Presto/2.6 Version/10.63',
			'Opera/9.80 (Windows NT 6.1; U; en) Presto/2.6 Version/10.63',
			'Opera/9.80 (Windows NT 6.1; U; MRA 5.7 (build 03686); ru) Presto/2.6 Version/10.63',
			'Opera/9.80 (Macintosh; PPC Mac OS X; U; en) Presto/2.6 Version/10.63',
			'Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML like Gecko) Chrome/30.0.1599.12785 YaBrowser/13.12.1599.12785 Safari/537.36',
			'Mozilla/5.0 (Windows NT 6.2; WOW64) AppleWebKit/537.36 (KHTML like Gecko) Chrome/30.0.1599.13014 YaBrowser/13.12.1599.13014 Safari/537.36',
			'Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML like Gecko) Chrome/30.0.1599.13014 YaBrowser/13.12.1599.13014 Safari/537.36',
			'Mozilla/5.0 (Windows NT 6.1) AppleWebKit/537.36 (KHTML like Gecko) Chrome/30.0.1599.12785 YaBrowser/13.12.1599.12785 Safari/537.36',
			'Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML like Gecko) Chrome/30.0.1599.12124 YaBrowser/13.12.1599.12124 Safari/537.36',
			'Mozilla/5.0 (Windows NT 5.1) AppleWebKit/537.36 (KHTML like Gecko) Chrome/30.0.1599.13014 YaBrowser/13.12.1599.13014 Safari/537.36',
			'Mozilla/5.0 (Macintosh; Intel Mac OS X 1091) AppleWebKit/537.36 (KHTML like Gecko) Chrome/28.0.1500.95 YaBrowser/13.10.1500.9322 Safari/537.36',
			'Mozilla/5.0 (Macintosh; Intel Mac OS X 1090) AppleWebKit/537.36 (KHTML like Gecko) Chrome/28.0.1500.95 YaBrowser/13.10.1500.9322 Safari/537.36',
			'Mozilla/5.0 (Windows NT 6.1) AppleWebKit/537.36 (KHTML like Gecko) Chrome/28.0.1500.95 YaBrowser/13.10.1500.8299 Safari/537.36',
			'Mozilla/5.0 (Windows NT 6.2; WOW64) AppleWebKit/537.36 (KHTML like Gecko) Chrome/28.0.1500.95 YaBrowser/13.10.1500.9323 Safari/537.36',
			'Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML like Gecko) Chrome/28.0.1500.95 YaBrowser/13.10.1500.9323 Safari/537.36',
			'Mozilla/5.0 (Windows NT 6.1) AppleWebKit/537.36 (KHTML like Gecko) Chrome/28.0.1500.95 YaBrowser/13.10.1500.9323 Safari/537.36',
			'Mozilla/5.0 (Windows NT 6.0) AppleWebKit/537.36 (KHTML like Gecko) Chrome/28.0.1500.95 YaBrowser/13.10.1500.9323 Safari/537.36',
			'Mozilla/5.0 (Windows NT 5.1) AppleWebKit/537.36 (KHTML like Gecko) Chrome/28.0.1500.95 YaBrowser/13.10.1500.9151 Safari/537.36',
			'Mozilla/5.0 (X11; Linux x8664) AppleWebKit/537.36 (KHTML like Gecko) Ubuntu Chromium/28.0.1500.52 Chrome/28.0.1500.52 Safari/537.36',
			'Mozilla/5.0 (X11; Linux i686) AppleWebKit/537.36 (KHTML like Gecko) Ubuntu Chromium/28.0.1500.52 Chrome/28.0.1500.52 Safari/537.36',
			'Mozilla/5.0 (X11; Linux x8664) AppleWebKit/537.36 (KHTML like Gecko) Ubuntu Chromium/28.0.1500.71 Chrome/28.0.1500.71 Safari/537.36',
			'Mozilla/5.0 (X11; Linux i686) AppleWebKit/537.36 (KHTML like Gecko) Ubuntu Chromium/28.0.1500.71 Chrome/28.0.1500.71 Safari/537.36',
			'Mozilla/5.0 (X11; Linux i686) AppleWebKit/537.36 (KHTML like Gecko) Ubuntu Chromium/31.0.1650.63 Chrome/31.0.1650.63 Safari/537.36',
			'Mozilla/5.0 (X11; Linux i686) AppleWebKit/537.36 (KHTML like Gecko) Ubuntu Chromium/30.0.1599.114 Chrome/30.0.1599.114 Safari/537.36',
			'Mozilla/5.0 (X11; Linux x8664) AppleWebKit/537.36 (KHTML like Gecko) Ubuntu Chromium/30.0.1599.114 Chrome/30.0.1599.114 Safari/537.36',
			'Mozilla/5.0 (X11; Linux i686) AppleWebKit/537.22 (KHTML like Gecko) Ubuntu Chromium/25.0.1364.160 Chrome/25.0.1364.160 Safari/537.22',
		);

		return $values[array_rand($values)];
	}
}
?>