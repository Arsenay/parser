<?php
	$dir = $local . '/info/';
	$file = $local .'/all_links.php';

	include(__DIR__ . '/sender.php');

	$all_links = array();
	if( file_exists($file) ) {
		$all_links = include $file;
	} else {
		foreach (getLinks() as $target) {
			foreach (getLangs() as $code => $lang) {
				$href = str_replace('%lang%', $lang, $target);

				$prefixes = getPrefix();
				if ( isset($prefixes[$code]) ) {
					$href = str_replace('%prefix%', $prefixes[$code], $href);
				} else {
					$href = str_replace('%prefix%', '', $href);
				}

				$all_links[$href] = array(
					'name'		=> getProductName($target),
					'status'	=> false,
					'lang'		=> $code,
					'image'		=> false,
				);
			}
		}
		file_put_contents($file, '<?php return ' . var_export($all_links, true) . '; ?>');
	}

	if ( isset($_POST['parse']) ) {
		$params = $_POST;
		$href = $params['parse'];
		unset($params['parse']);
		if ( isset($all_links[$href]) ) {
			if ( $params['error'] ) {
				$all_links[$href]['status'] = false;
				echo 'error: parse text';
			} else {
				unset($params['error']);
				$all_links[$href]['status'] = true;
				$product = $all_links[$href]['name'];
				$lang = $all_links[$href]['lang'];
				$product_dir = $dir . $product;
				$product_file = $product_dir . '/' . $lang . '.txt';
				@mkdir($product_dir, 0777, true);
				$info = '';
				$length = '';
				foreach ($params as $name => $text) {
					if($name == 'image'){
						$product = $all_links[$href]['image'] = $text;
						if ( !glob($product_dir . '/../image.*') ) {
							copy($text, $product_dir . '/image.' . pathinfo($text, PATHINFO_EXTENSION));
						}
					}
					$info .= '--------------------- ' . $name . ' ---------------------' . "\n\n";
					$info .=  trim($text,"\n") . "\n\n\n\n";
					$length .= $text;
				}

				if( $length ){
					file_put_contents($product_file, $info);
					echo 'ok';
				} else {
					$all_links[$href]['status'] = 'error';
					echo 'error: empty text';
				}
			}
		} else {
			echo 'error: href "' . $href . '" not valid';
		}

		file_put_contents($file, '<?php return ' . var_export($all_links, true) . '; ?>');
		return false;
	}

	if ( isset($_POST['href']) ) {
		echo getContent($_POST['href']);
		return false;
	}
?>
<h1 data-status="off"></h1>
Speed: <input id="speed" type="number" value="0"><br>
<button onclick="procces(true);" style="background:green;">START</button> 
<button onclick="procces(false);" style="background:red;">STOP</button>
<br>
<br>
<div id="progress"><div></div><span></span></div>
<br>
<br>
<table id="result_table">
	<tr>
		<th>Processed (<span class="y_count">0</span>)</th>
		<th>Queue (<span class="n_count">0</span>)</th>
		<th>Error (<span class="e_count">0</span>)</th>
	</tr>
	<tr>
		<td class="y"></td>
		<td class="n"></td>
		<td class="e"></td>
	</tr>
</table>
<script 
	src="https://code.jquery.com/jquery-3.2.1.min.js" 
	integrity="sha256-hwg4gsxgFZhOsEEamdOYGBf13FyQuiTwlAQgxVSNgt4=" 
	crossorigin="anonymous" 
></script>
<script>
	hrefs = JSON.parse('<?php echo json_encode($all_links) ?>');
	$.each(hrefs, function(href, info){
		if(info.status === 'error') {
			$('.e').append('<div>' + href + '</div>');
		} else if(info.status) {
			$('.y').append('<div>' + href + '</div>');
		} else {
			$('.n').append('<div>' + href + '</div>');
		}
	});

	progress();

	function procces(swi){
		if (swi) {
			$('h1').data('status', 'on');
			getInfo();
		} else {
			$('h1').data('status', 'off');
		}
		console.log($('h1').data('status'));
	}

	function getInfo(){
		if ($('h1').data('status') == 'off') {
			return false;
		}
		elem = $('.n div').first();
		if( elem.length == 0 ){
			$('h1').data('status', 'off');
			return false;
		}
		
		href = elem.text();

		$.ajax({
			type: 'POST',
			url: '',
			data: {
				href: href
			},
			success: function(html){
				var data = parseContent(html);
				//console.log(html,data);return;
				setTimeout(function(){
					sendInfo(elem, data);
				}, $('#speed').val());
			}
		});
	}

	function sendInfo(elem, data){
		$.ajax({
			type: 'POST',
			url: '',
			data: data,
			success: function(html){
				if ( html == 'ok' ) {
					$('.y').append(elem);
				} else {
					$('.y').append(elem);
					$('.e').clone(elem);
					console.log('error: ' + data.parse + ' [' + html + ']');
				}

				progress();
				getInfo();
			}
		});
	}

	function progress(){
		var all = $('.n div,.y div').length;
		var ready = $('.y div').length;
		var percent = Math.floor(100 / all * ready);

		$('.y_count').html($('.y div').length);
		$('.n_count').html($('.n div').length);
		$('.e_count').html($('.e div').length);

		$('#progress > div').css('width', percent+'%');
		$('#progress > span').text(percent+' %');
	}
</script>
<style>
	#progress {
		position: relative;
		background: #dcdcdc;
		border: 2px solid black;
		text-align: center;
	}
	#progress div {
		background: #00de00;
		height: 30px;
	}
	#progress span {
		top: 0;
		left: 0;
		position: absolute;
		display: block;
		width: 100%;
		margin-top: 5px;
		font-size: 20px;
		text-align: center;
	}

	#result_table {
		width: 100%;
		border-collapse: collapse;
	}
	#result_table th, 
	#result_table td {
		border: 1px solid black;
		padding: 5px;
		width: calc(100% / 3);
	}
	.y, .n, .e {
		vertical-align: top;
	}
	.y {
		color: green;
	}
	.n {
		color: #ff9800;
	}
	.e {
		color: red;
	}
</style>