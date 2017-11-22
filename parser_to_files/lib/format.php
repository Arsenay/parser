<?php
	$dir = realpath(__DIR__ . '/../');

	$ready_dir = $dir . '/result';
	@mkdir($ready_dir, 0777, true);

	foreach (glob($dir . '/*.com') as $site_dir) {
		$product_dirs = $site_dir . '/info';
		exec('cp -R ' . $product_dirs . ' ' . $ready_dir);
		exec('mv ' . $ready_dir . '/info ' . $ready_dir . '/' .basename($site_dir));
		$product_dirs = $ready_dir . '/' .basename($site_dir);
		if ( !file_exists($product_dirs) ) { continue; }
		echo ('<hr>'.$product_dirs);
		foreach (glob($product_dirs . '/*') as $product_dir) {
			foreach (glob($product_dir . '/*.txt') as $file) {
				//echo $file."<br>";
				$text = file_get_contents($file);
				$text = formatText($text);
				file_put_contents($file, $text);
			}
		}
	}

	function formatText($text){
		$text = preg_replace('/ +/', ' ', $text);
		$text = preg_replace('/\n+$/', '', $text);
		$text = preg_replace('/\:\n/', ':', $text);
		
		$arr = explode('short_description', $text);
		$arr[1] = preg_replace_callback(
			'/([.,?:;!])([^\d\s])/',
			function ($matches1) {
				if ( isset($matches1[0]) ) {
					return $matches1[1] . "\n" . $matches1[2];
				}
			},
			$arr[1]
		);

		$text = implode('short_description', $arr);

		return $text;
	}
?>