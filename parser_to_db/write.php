<?php 
require 'config.php';
require 'db.php';

$db = new DB();

$data = $_POST;

foreach ($data as $key => $value) {
	if( !is_array($value) ){
		$data[$key] = trim($value);
	}
}

if($data['url']){
	$sql = "UPDATE 
				urls 
			SET 
				status = '1', 
				type = '" . (int)$data['type'] . "'
			WHERE 
				url = '" . $db->escape($data['url']) . "'";

	$result = $db->query($sql);
}

if($data['hrefs']){
	foreach ($data['hrefs'] as $href) {
		$href = str_replace('http://', 'https://', $href);

		$result = $db->query("SELECT * FROM urls WHERE url = '" . $db->escape($href) . "'");

		if( $result->num_rows == 0){
			$sql = "INSERT INTO 
						urls 
					SET 
						url = '" . $db->escape($href) . "'";

			$result = $db->query($sql);
		}
	}
}

if( $data['type'] == 1 ){
	$result = $db->query("DELETE FROM products WHERE url = '" . $db->escape($data['url']) . "'");

	$sql = "INSERT INTO 
				products 
			SET 
				title = '" . $db->escape($data['title']) . "', 
				description = '" . $db->escape($data['description']) . "', 
				meta_title = '" . $db->escape($data['meta_title']) . "', 
				meta_keywords = '" . $db->escape($data['meta_keywords']) . "', 
				meta_description = '" . $db->escape($data['meta_description']) . "', 
				img = '" . $db->escape($data['img']) . "', 
				imgs = '" . $db->escape($data['imgs']) . "', 
				sku = '" . $db->escape($data['sku']) . "', 
				url = '" . $db->escape($data['url']) . "', 
				price = '" . (float)$data['price'] . "', 
				date = NOW();";
	
	$result = $db->query($sql);
}

echo json_encode($_POST['hrefs']);