<?php 
	$option = $_GET["option"];

	if ($option == "list") {
		list_files();
	} else if($option == "delete") {
		$file_name = $_GET["file_name"];
		delete_file($file_name);
	}

	function list_files() {
		$bucket_name = "**";
		$host = "sinacloud.net";
		$url = $host."/".$bucket_name."/?formatter=json";
		$date = gmdate('D, d M Y H:i:s \G\M\T', time());
		$resource = "/**/";
		$mySecretKey = "*************************************";
		$accessKey = "****************";

		$ssig = calc_ssig($resource, $date, "GET");
		// 获得文件列表信息使用http认证
		$curl = curl_init();
		curl_setopt($curl, CURLOPT_HTTPHEADER, array(
			'Host: '.$host,
			'Date: '.$date,
			'Authorization: SINA ' . $accessKey . ':' . $ssig
			));
		curl_setopt_array($curl, array(
			CURLOPT_RETURNTRANSFER => 1,
			CURLOPT_URL => $url
			));

		$resp = curl_exec($curl);
		curl_close($curl);
		$json = json_decode($resp) -> {"Contents"};
		foreach ($json as $aFile) {
			$aFile -> {"file_url"} = generate_file_url($aFile -> {"Name"});
		}
		echo json_encode($json);
	}

	function delete_file($file_name) {
		$bucket_name = "**";
		$host = $bucket_name.".sinacloud.net";
		//$url = $host."/".$bucket_name."/?formatter=json";
		$url = $host."/".$file_name."?formatter=json";
		$date = gmdate('D, d M Y H:i:s \G\M\T', time());
		$resource = "/**/".$file_name;
		$mySecretKey = "*************************************";
		$accessKey = "****************";

		$ssig = calc_ssig($resource, $date, "DELETE");
		// 删除文件使用http认证
		$curl = curl_init();
		curl_setopt($curl, CURLOPT_HTTPHEADER, array(
			'Host: '.$host,
			'Date: '.$date,
			'Authorization: SINA ' . $accessKey . ':' . $ssig
			));
		curl_setopt_array($curl, array(
			CURLOPT_RETURNTRANSFER => 1,
			CURLOPT_URL => $url,
			CURLOPT_CUSTOMREQUEST => 'DELETE'
			));

		$resp = curl_exec($curl);
		$http_code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
		curl_close($curl);
		echo $http_code;
	}
	
	/**
	 * 计算签名ssig
	 * 当访问的url的queryString中有Expires时，date用Expires代替
	 *//*
	function calc_ssig_with_date($resource) {
		$mySecretKey = "*************************************";
		$date = gmdate('D, d M Y H:i:s \G\M\T', time());

		$strToSign = "GET" . "\n" 	// HTTP-Verb
					. "\n" 			// Content_MD5(empty)
					. "\n"			// Content-Typ(empty)
					. $date . "\n"	// Date
					. "" 			// CanonicalizedAmzHeaders(empty)
					. $resource;	// CanonicalizedResource
		return substr(base64_encode(hash_hmac("sha1", $strToSign, $mySecretKey, true)), 5, 10);
	}*/
	function calc_ssig($resource, $date, $method) {
		$mySecretKey = "*************************************";

		$strToSign = $method . "\n" 	// HTTP-Verb
					. "\n" 			// Content_MD5(empty)
					. "\n"			// Content-Typ(empty)
					. $date . "\n"	// Date
					. "" 			// CanonicalizedAmzHeaders(empty)
					. $resource;	// CanonicalizedResource
		return substr(base64_encode(hash_hmac("sha1", $strToSign, $mySecretKey, true)), 5, 10);
	}

	// 根据文件名，生成文件的url，使用url签名认证
	function generate_file_url($file_name) {
		$bucket_name = "**";
		$host = "sinacloud.net";
		//$file_name = $file -> {"Name"};
		$file_name = urlencode($file_name);
		$resource = '/'.$bucket_name.'/'.$file_name;
		$file_url = 'http://'.$host.$resource;
		$expires = (time() + 600)."";
		$accessKey = "****************";
		$ssig = urlencode(calc_ssig($resource, $expires, "GET"));
		$params = array(
			"KID" => "sina,".$accessKey,
			"ssig" => $ssig,
			"Expires" => $expires//,
			//"formatter" => "json"
			);
		
		// 下载文件的url，将params中参数附在其后
		$url = build_url($file_url, $params);
		return $url;
	}
	
	function build_url($host_path,$data) {
		$host_path = $host_path.'?';
		foreach ($data as $key => $value) {
			$host_path = $host_path.$key.'='.$value.'&';
		}
		return $host_path;
	}

 ?>
