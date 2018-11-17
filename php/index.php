<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<title>程序员文件中转站</title>
	<script type="text/javascript">
		window.onload = fetch_file_list;

		function fetch_file_list() {
			var req = new XMLHttpRequest();
			req.onreadystatechange = function () {
				if(req.readyState == 4 && req.status == 200) {
					var data = req.responseText;
					show_files(data);
				}
			}
			req.open("GET", "./file_handler.php?option=list", "true");
			req.send();
		}

		function show_files(json_data) {
			var json = eval(json_data);
			var ul = document.getElementById("id");
			ul.innerHTML = '';
			for (var i = json.length - 1; i >= 0; i--) {
				var file_name = json[i]["Name"];
				var file_url = json[i]["file_url"];
				var file_size = json[i]["Size"];
				var a1 = document.createElement("a");
				var a2 = document.createElement("a");
				var size = document.createElement("span");
				var btn_delete = document.createElement("button");
				a1.href = file_url;
				a2.href = file_url + "fn=" + file_name;
				a1.target = "_blank";
				a1.innerHTML = file_name;
				a2.setAttribute("download", "");
				a2.innerHTML = "下载";
				size.innerHTML = "&nbsp;" + file_size + " B&nbsp;";
				btn_delete.innerHTML = "删除";
				btn_delete.setAttribute("onclick", 'delete_file("'+ file_name + '", this);');
				var tmp = document.createElement("li");
				var separator = document.createElement("span");
				separator.innerHTML = "&nbsp;";
				tmp.appendChild(a1);
				tmp.appendChild(separator);
				tmp.appendChild(a2);
				tmp.appendChild(size);
				tmp.appendChild(btn_delete);
				ul.appendChild(tmp);
			};
		}

		function delete_file(file_name, btn_element) {
			var li = btn_element.parentElement;
			var delete_alert = document.createElement("span");
			delete_alert.innerHTML = "&nbsp;&nbsp;正在删除此项";
			li.appendChild(delete_alert);
			var req = new XMLHttpRequest();
			req.onreadystatechange = function () {
				if(req.readyState == 4 && req.status == 200) {
					var data = req.responseText;
					if (data == '204') { // 删除成功
						li.remove();
					} else {
						alert('删除失败');
						delete_alert.remove();
					}
				}
			}
			req.open("GET", "./file_handler.php?option=delete&file_name=" + file_name, "true");
			req.send();
		}
	</script>
</head>
<body>
	<ul id="id">
		文件加载中，请等待。
	</ul>

<?php 
	$mySecretKey = "*****";
	$expiration = gmdate('Y-m-d\TH:i:s.000\Z', time() + 600);
	$policy = '{"expiration":"'.$expiration.'","conditions":[{"bucket":"**"},{"acl":"private"},["eq","$acl","private"],["starts-with", "$key", ""],["content-length-range",1,52428800]]}';
	$policy_value = base64_encode($policy);
	$signature_value = base64_encode( hash_hmac( "sha1", $policy_value, $mySecretKey, true ) );
 ?>
	<form method="POST" action="http://**.sinacloud.net/" enctype="multipart/form-data">
		<input type="hidden" name="AWSAccessKeyId" value="*****" />
		<input type="hidden" name="key" value="${filename}" />
		<input type="hidden" name="acl" value="private" />
		<input type="hidden" name="success_action_redirect" value="http://file.mybluemix.net/" />
		<input type="hidden" name="Policy" value="<?php echo $policy_value; ?>" />
		<input type="hidden" name="Signature" value="<?php echo $signature_value; ?>" />
		<input type="file" name="file" required/>
		<input type="submit" value="上传" />
	</form>
</body>
</html>
