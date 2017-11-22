<script  src="https://code.jquery.com/jquery-3.2.1.min.js" integrity="sha256-hwg4gsxgFZhOsEEamdOYGBf13FyQuiTwlAQgxVSNgt4=" crossorigin="anonymous"></script>
<?php 
	require 'config.php';
	require 'db.php';

	$db = new DB();

	$result = $db->query("SELECT * FROM urls WHERE status='0'");

	$url = isset($result['row']['url'])?$result['row']['url']:'';
	
	if(!$url){
		$url = SITE;
	}
	var_dump($url);

	$ch = curl_init(); 
    curl_setopt($ch, CURLOPT_URL, $url); 
    curl_setopt($ch, CURLOPT_HEADER, false); // выключить тег HEAD
    curl_setopt($ch, CURLOPT_NOBODY, false); // включая тег BODY
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true); // если редирект то переходить по нему
    $html = curl_exec($ch); 
    //$html = preg_replace('/(<script.+?<\/script>)/', '', $html); // удаляем скрипты
    $html = preg_replace('/(<script(.|\s)+?script>)/', '', $html); // удаляем скрипты
    $html = preg_replace('/(<link.+?>)/', '', $html); // удаляем стили
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE); 
    curl_close($ch);

    echo $html;
?>
<script class="main">
	$(document).ready(function(){
		$('script:not(.main):not([src])').remove();
		$('script:not(.main):not([src]), script[src*="yandex"],script[src*="google"]').remove();
		
		var type = 0;

		if($('*[itemtype="http://schema.org/Product"]').length > 0){
			type = 1;
		}

		var imgs = [];
		$('.thumbnails ul li p a').each(function(n,elem){
			imgs.push($(elem).attr('href'));
		});

		var hrefs = [];
		href_filter = $('a[href*="domain"]')
		.filter(':not(a[href*="?mfp"])')
		.filter(':not(a[href*="#"])')
		
		.filter(':not(a[href^="tel:"])')
		.filter(':not(a[href^="mailto:"])')
		.filter(':not(a[href$=".png"])')
		.filter(':not(a[href$=".jpg"])')
		.filter(':not(a[href$=".jpeg"])')
		.filter(':not(a[href$=".gif"])')
		.filter(':not(a[href$=".pdf"])')

		.filter(':not(a[href*=".xsl"])')
		.filter(':not(a[href*=".doc"])')
		.filter(':not(a[href*="facebook"])')
		.filter(':not(a[href*="google"])')
		.filter(':not(a[href*="twitter"])')
		.filter(':not(a[href*="vk."])')
		.filter(':not(a[href*="skype"])')
		.filter(':not(a[href*="javascript"])');

		$(href_filter).each(function(n,elem){
			hrefs.push($(elem).attr('href'));
		});

		var data = {
			title: $('h1').text(),
			description: $('#tab-description').html(),
			meta_title: $('title').text(),
			meta_keywords: $('meta[name="keywords"]').attr('content'),
			meta_description: $('meta[name="description"]').attr('content'),
			price: $('span[itemprop="price"]').text(),
			sku: $('.upc_text strong').text(),
			img: $('.product-image > a > img').attr('src'),
			imgs: imgs.join('^'),
			url: '<?php echo $url; ?>',
			type: type,
			hrefs: hrefs
		};

		$.ajax({
			url: '/write.php',
			type: 'post',
			data: data,
			dataType: 'json',
			beforeSend: function() {
				console.clear();
				console.log(data);
			},
			complete: function() {
				location.reload();
			},
			success: function(json) {
				console.log(json);
			},
			error: function(xhr, ajaxOptions, thrownError) {
				console.log(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
			}
		});
	});
</script>