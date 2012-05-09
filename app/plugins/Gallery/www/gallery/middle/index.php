<?php
require_once('Object.php');
require_once('Image.php');
require_once('ImageMagick.php');
$image_name = $_GET['image'];
//echo $image_name;
//die();
if (file_exists("../".$image_name)) {
	$hash = md5($image_name).'.jpg';
	if (file_exists("tmp/".$hash)) {
		$path = "tmp/".$hash;
		$image = Image::fromFile($path);
		$image->send(Image::JPEG);
	} else {		
		$path = "../".$image_name;
		$image = Image::fromFile($path);
		$image->resize(NULL, 500);
		$image->save("tmp/".$hash);
		$image->send(Image::JPEG);
	}
} else {
	header("http/1.0 404 not found");
}
