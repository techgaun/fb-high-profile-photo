<?php
function createImage($img1, $img2, $msg1_title, $msg1, $msg2_title, $msg2)
{
	//header("Content-type: image/jpeg");
	$img = imagecreatetruecolor(350, 200);
	imagefilledrectangle($img, 0, 0, 350, 200, imagecolorallocate($img, 0, 0, 0));
	$img1 = imagecreatefromjpeg($img1);
	$img2 = imagecreatefromjpeg($img2);
	$img1_width = imagesx($img1);
	$img2_width = imagesx($img2);
	$max_width = max($img1_width, $img2_width);
	$img1_processed = imagecreatetruecolor($max_width, 100);
	$img2_processed = imagecreatetruecolor($max_width, 100);
	if ($img1_width > $img2_width)
	{
		imagecopyresized($img2_processed, $img2, 0, 0, 0, 0, $img1_width, 100, $img2_width, 100);
		$img1_processed = $img1;
	}
	else 
	{
		imagecopyresized($img1_processed, $img1, 0, 0, 0, 0, $img2_width, 100, $img1_width, 100);	
		$img2_processed = $img2;
	}
	imagecopymerge($img, $img1_processed, 0, 0, 0, 0, 100, 100, 100);
	imagecopymerge($img, $img2_processed, 0, 101, 0, 0, 100, 100, 100);
	putenv('GDFONTPATH=' . realpath('.'));
	$font = 'masexy.ttf';
	imagettftext($img, 20, 0, $max_width+6, 35, imagecolorallocate($img, 0, 255, 0), $font, $msg1_title);
	imagettftext($img, 20, 0, $max_width+6, 135, imagecolorallocate($img, 0, 255, 0), $font, $msg2_title);
	imagestring($img, 5, $max_width+10, 45, $msg1, imagecolorallocate($img, 0, 255, 0));
	imagestring($img, 5, $max_width+10, 145, $msg2, imagecolorallocate($img, 0, 255, 0));
	imagettftext($img, 10, 0, $max_width+6, 180, imagecolorallocate($img, 0, 225, 0), $font, "www.techgaun.com");	
	imagejpeg($img, "post.jpg", 100);
	imagedestroy($img);
	imagedestroy($img1);
	imagedestroy($img2);
	//imagedestroy($img1_processed);
	//imagedestroy($img2_processed);
}
