<?php
 
/*
* File: SimpleImage.php
* Author: Simon Jarvis
* Copyright: 2006 Simon Jarvis
* Date: 08/11/06
* Link: http://www.white-hat-web-design.co.uk/articles/php-image-resizing.php
*
* This program is free software; you can redistribute it and/or
* modify it under the terms of the GNU General Public License
* as published by the Free Software Foundation; either version 2
* of the License, or (at your option) any later version.
*
* This program is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
* GNU General Public License for more details:
* http://www.gnu.org/licenses/gpl.html
*
*/
 
class SimpleImage{
 
	var $image;
	var $image_type;
	var $width;
	var $height;
	var $mime;
	private $font;

	function __construct($data=array()){
	 	$this->font = dirname(__FILE__).'/DroidSerif-Bold.ttf';
	}

	function load($filename='') {
		
		if(!empty($filename)){

			$image_info = getimagesize($filename);
			
			if($image_info != false){
				$this->image_type = $image_info[2];
				$this->mime = $image_info['mime'];
				$this->width = $image_info[0];
				$this->height = $image_info[1];
				
				switch($this->image_type){
					case IMAGETYPE_JPEG:
					$this->image = imagecreatefromjpeg($filename);
					break;
					
					case IMAGETYPE_GIF:
					$this->image = imagecreatefromgif($filename);
					break;
					
					case IMAGETYPE_PNG:
					$this->image = imagecreatefrompng($filename);
					break;
				}
				
				$result = $this->image == false ? false:true;
			
			}else{$result = false;}
			
		} else {$result = false;}
		
		return $result;
	}
	
	function save($filename, $image_type=IMAGETYPE_JPEG, $compression=85, $permissions=NULL) {
 
      //$image_info = getimagesize($filename);
      //$this->image_type = $image_info[2];
		$image_type = empty($this->image_type) ? $image_type :$this->image_type;
	  
		switch($image_type){
		  case IMAGETYPE_JPEG:
		  $result = imagejpeg($this->image,$filename,$compression);
		  break;
		  
		  case IMAGETYPE_GIF:
		  $result = imagegif($this->image,$filename);
		  break;
		  
		  case IMAGETYPE_PNG:
		  $result = imagepng($this->image,$filename);
		  break;
		}

		if($permissions != NULL){chmod($filename,$permissions);}

		return $result;
	}
	
	function output() {
	
		header("Content-Type: $this->image_type");
		switch($this->image_type){
		  case IMAGETYPE_JPEG:
		  imagejpeg($this->image);
		  break;
		  
		  case IMAGETYPE_GIF:
		  imagegif($this->image);
		  break;
		  
		  case IMAGETYPE_PNG:
		  imagepng($this->image);
		  break;
		}
	}
   
	function getWidth(){return imagesx($this->image);}
  
	function getHeight(){return imagesy($this->image);}
	
	function resizeToHeight($height) {
		$ratio = $height / $this->getHeight();
		$width = $this->getWidth() * $ratio;
		$this->resize($width,$height);
	}
	
	function resizeToWidth($width) {
		
		$ratio = $width / $this->getWidth();
		$height = intval($this->getheight() * $ratio);
		$width = intval($width);
		
		$this->resize($width,$height);
	}
	
	function scale($scale) {
		$width = $this->getWidth() * $scale/100;
		$height = $this->getheight() * $scale/100;
		$this->resize($width,$height);
	}
   
	function resize($width,$height) {
		$new_image = imagecreatetruecolor($width, $height);
		
		imagealphablending($this->image, false);
		imagesavealpha($this->image, true);
		$transparent = imagecolorallocatealpha($new_image, 255, 255, 255, 127);
		imagefilledrectangle($new_image, 0, 0, $width, $height, $transparent);
		
		imagecopyresampled($new_image, $this->image, 0, 0, 0, 0, $width, $height, $this->getWidth(), $this->getHeight());
		$this->image = $new_image;
		$this->width = $width;
		$this->height = $height;
	}      

	//following funciton is by zubair
	function scaleTofit($maxwidth, $maxheight) {
		
		$width = $this->getWidth();
		$height = $this->getHeight();
		
		if($width > $height && $width > $maxwidth) {
			
			$ratio = $maxwidth/$width;
	
			$newheight = intval($height*$ratio);
			$newwidth = intval($width*$ratio);
			
			if($newheight > $maxheight){
				$ratio = $maxheight/$newheight;
				$newheight = intval($newheight*$ratio);
				$newwidth = intval($newwidth*$ratio);
			}
			
			$this->resize($newwidth,$newheight);
		} 
		
		elseif($height > $width && $height > $maxheight) {
		
			$ratio = $maxheight/$height;
			
			$newwidth = intval($width*$ratio);
			$newheight = intval($height*$ratio);
			
			if($newwidth > $maxwidth){
				$ratio = $maxwidth/$newwidth;
				$newwidth = intval($newwidth*$ratio);
				$newheight = intval($newheight*$ratio);
			}

			$this->resize($newwidth,$newheight);
		} 
	
		elseif($height == $width && $height > $maxheight) {
		
			$ratio = $maxheight/$height;
			
			$newwidth = intval($width*$ratio);
			$newheight = intval($height*$ratio);
			
			if($newwidth > $maxwidth){
				$ratio = $maxwidth/$newwidth;
				$newwidth = intval($newwidth*$ratio);
				$newheight = intval($newheight*$ratio);
			}

			$this->resize($newwidth,$newheight);
		} 

		elseif($width == $height && $width > $maxwidth) {

			$ratio = $maxwidth/$width;
			
			$newwidth = intval($width*$ratio);
			$newheight = intval($height*$ratio);

			if($newheight > $maxheight){
				$ratio = $maxheight/$newheight;
				$newheight = intval($newheight*$ratio);
				$newwidth = intval($newwidth*$ratio);
			}

			$this->resize($newwidth,$newheight);
		} 
		
	}
	
	function cropProfile($x = 0, $y = 0){
		
		$width = 250;
		$height = 250;
		$x = -$x;
		$y = -$y;
		
		switch(true){
			case ($x < 0 &&	$y < 0):
			$dst_width = $this->width + (-$x) <= 250 ? 250 :$this->width + (-$x);
			$dst_height = $this->height + (-$y) <= 250 ? 250 : $this->height + (-$y);
			
			$mask = imagecreatetruecolor($dst_width, $dst_height);

			imagealphablending($mask, true);
			imagesavealpha($mask, true);
			$transparent = imagecolorallocatealpha($mask, 255, 255, 255, 127);
			imagefilledrectangle($mask, 0, 0, $dst_width, $dst_height, $transparent);

			imagecopymerge($mask, $this->image, -$x, -$y, 0, 0, $this->width, $this->height,85);
			
			$this->image = $mask;
			$this->width = $dst_width;
			$this->height = $dst_height;

			$this->crop();
			break;
			
			case ($x >= 0 && $y < 0):
			$dst_width = $this->width + $x <= 250 ? 250 :$this->width + $x;
			$dst_height = $this->height + (-$y) <= 250 ? 250 : $this->height + (-$y);
			
			$mask = imagecreatetruecolor($dst_width, $dst_height);

			imagealphablending($mask, true);
			imagesavealpha($mask, true);
			$transparent = imagecolorallocatealpha($mask, 255, 255, 255, 127);
			imagefilledrectangle($mask, 0, 0, $dst_width, $dst_height, $transparent);

			imagecopymerge($mask, $this->image, -$x, -$y, 0, 0, $this->width, $this->height,85);
			
			$this->image = $mask;
			$this->width = $dst_width;
			$this->height = $dst_height;

			$this->crop();
			break;

			case ($x < 0 && $y >= 0):
			$dst_width = $this->width + (-$x) <= 250 ? 250 :$this->width + (-$x);
			$dst_height = $this->height + $y <= 250 ? 250 : $this->height + $y;
			
			$mask = imagecreatetruecolor($dst_width, $dst_height);

			imagealphablending($mask, true);
			imagesavealpha($mask, true);
			$transparent = imagecolorallocatealpha($mask, 255, 255, 255, 127);
			imagefilledrectangle($mask, 0, 0, $dst_width, $dst_height, $transparent);

			imagecopymerge($mask, $this->image, -$x, -$y, 0, 0, $this->width, $this->height,85);
			
			$this->image = $mask;
			$this->width = $dst_width;
			$this->height = $dst_height;

			$this->crop();
			break;
			
			default:
			$this->crop($x,$y);
			break;
			
		}
	}
	
	function crop($x=0,$y=0,$width=250,$height=250){
		
		$new_image = imagecrop($this->image, array('x' => $x, 'y' => $y, 'width' => $width, 'height' => $height));
		
		if($new_image !== FALSE){
			$this->image = $new_image;
			$this->width = $width;
			$this->height = $height;
		}
			
	}
	
	function printImage($srcfile){
		$dst_height = 140;
		$dst_width = 140;
		
		if(!empty($srcfile)){

			$src_image_info = getimagesize($srcfile);
			
			if($src_image_info != false){
				$src_image_type = $src_image_info[2];
				
				switch($src_image_type){
					case IMAGETYPE_JPEG:
						$src_image = imagecreatefromjpeg($srcfile);
					break;
					
					case IMAGETYPE_GIF:
					$src_image = imagecreatefromgif($srcfile);
					break;
					
					case IMAGETYPE_PNG:
					$src_image = imagecreatefrompng($srcfile);
					break;
				}
				
				$new_image = imagecreatetruecolor($dst_width, $dst_height);
				imagecopyresampled($new_image, $src_image, 0, 0, 0, 0, $dst_width, $dst_height,imagesx($src_image),imagesy($src_image));
				
				$mask = imagecreatetruecolor($dst_width, $dst_height);
				$pink = imagecolorallocate($mask, 255, 0, 255);
				imagefill($mask, 0, 0, $pink);
				//this cuts a hole in the middle of the pink mask
				$black = imagecolorallocate($mask, 0, 0, 0);
				imagecolortransparent($mask, $black);
				imagefilledellipse($mask, $dst_width/2, $dst_height/2, $dst_width-4, $dst_height-4, $black);
				//this merges the mask over the pic and makes the pink corners transparent
				imagecopymerge($new_image, $mask, 0, 0, 0, 0, $dst_width, $dst_height,100);
				imagecolortransparent($new_image, $pink);

				//imagepng($new_image,date('Ymd_His').".png");

				imagecopymerge($this->image,$new_image,55,55,0,0,$dst_width,$dst_height,85);
			}
			
		} 

	}
	
	function printText($string=''){
		$text = ucfirst($string);
		$text_color = imagecolorallocate($this->image, 255, 255, 255);
		
		$text_length = strlen($text);
		$text_length_half = abs($text_length/2);
		
		$first_part = substr($text, 0, $text_length_half);
		$last_part = substr($text,$text_length_half,$text_length);
		 
		if($text_length > 20){
			$dimensions = imagettfbbox(13, 0, $this->font, $first_part);
			$textWidth = abs($dimensions[4] - $dimensions[0]);
			$x = 250 - $textWidth;
			$x=($x/2);
			imagettftext($this->image, 13, 0, $x, 220, $text_color, $this->font, $first_part);
 
			$dimensions = imagettfbbox(13, 0, $this->font, $last_part);
			$textWidth = abs($dimensions[4] - $dimensions[0]);
			$x1 = 250 - $textWidth;
			$x1=($x1/2); 
			imagettftext($this->image, 13, 0, $x1, 240, $text_color, $this->font, $last_part);

		}else{
			$dimensions = imagettfbbox(13, 0, $this->font, $text);
			$textWidth = abs($dimensions[4] - $dimensions[0]);
			$x = 250 - $textWidth;
			$x=($x/2)+0; 
			imagettftext($this->image, 13, 0, $x, 230, $text_color, $this->font, $text);
		}
	}
	
	function createFBImage($src){

		$dst_height = 250;
		$dst_width = 2*$dst_height;

		if(!empty($src)){

			$src_image_info = getimagesize($src);
			
			if($src_image_info != false){
				$src_image_type = $src_image_info[2];
				
				switch($src_image_type){
					case IMAGETYPE_JPEG:
					$src_image = imagecreatefromjpeg($src);
					break;
					
					case IMAGETYPE_GIF:
					$src_image = imagecreatefromgif($src);
					break;
					
					case IMAGETYPE_PNG:
					$src_image = imagecreatefrompng($src);
					break;
				}
				
				//$this->resize(250,250);
				$mask = imagecreatetruecolor($dst_width, $dst_height);

				imagealphablending($mask, false);
				imagesavealpha($mask, true);
				$transparent = imagecolorallocatealpha($mask, 255, 255, 255, 127);
				imagefilledrectangle($mask, 0, 0, 250, 250, $transparent);

				imagecopyresampled($mask, $src_image, 0, 0, 0, 0, 250, 250,imagesx($src_image),imagesy($src_image));

				imagecopymerge($mask, $this->image, 250, 0, 0, 0, $dst_width, $dst_height,85);
				
				$this->image = $mask;
				$this->width = $dst_width;
				$this->height = $dst_height;
				
				
			}
			
		} 
		
	}

}
?>
