<?php 
require 'config.php';
$conn = mysqli_connect(DB_HOST, DB_USER, DB_PASS);
// Check connection
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// Create database
$sql = "CREATE DATABASE IF NOT EXISTS `".DB_NAME."`;";
if (mysqli_query($conn, $sql)) {
    echo "Database created successfully";
    require 'db.php';

    $db = new DB();

    $sql = "CREATE TABLE IF NOT EXISTS `urls` (
				`url` text NOT NULL,
				`type` tinyint(1) NOT NULL DEFAULT '0',
				`status` tinyint(1) NOT NULL DEFAULT '0',
				UNIQUE KEY `url` (`Url`)
			) ENGINE=InnoDB DEFAULT CHARSET=utf8;";

	$result = $db->query($sql);

	$sql = "CREATE TABLE IF NOT EXISTS `products` (
				`title` text,
				`description` text,
				`meta_title` text,
				`meta_keywords` text,
				`meta_description` text,
				`img` text,
				`imgs` text,
				`sku` text,
				`url` text,
				`price` text,
				`date` text,
			) ENGINE=InnoDB DEFAULT CHARSET=utf8;";

	$result = $db->query($sql);
} else {
    echo "Error creating database: " . mysqli_error($conn);
}

mysqli_close($conn);