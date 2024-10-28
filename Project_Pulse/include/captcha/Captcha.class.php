<?php
@session_start();

/*
* File: CaptchaSecurityImages.php
* Author: Simon Jarvis
* Copyright: 2006 Simon Jarvis
* Date: 03/08/06
* Updated: 07/02/07
* Requirements: PHP 4/5 with GD and FreeType libraries
* Link: http://www.white-hat-web-design.co.uk/articles/php-captcha.php
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

class Captcha{

	public $width;
	public $height;
	public $length;
	private $font;
	
	function __construct($data=array()){
		
		$this->width = isset($data['width']) ? $data['width'] : 100;
		$this->height = isset($data['height']) ? $data['height'] : 32;
		$this->length = isset($data['length']) ? $data['length'] : 5;
	 	$this->font = dirname(__FILE__).'/arial.ttf';
	}

	private function generateCode($characters) {
		/* list all possible characters, similar looking characters and vowels have been removed */
		$possible = '23456789bcdfghjkmnpqrstvwxyz';
		$code = '';
		$i = 0;
		while ($i < $characters) { 
			$code .= substr($possible, mt_rand(0, strlen($possible)-1), 1);
			$i++;
		}
		return $code;
	}

	public function SecurityCode() {
		
		$code = $this->generateCode($this->length);
		
		/* font size will be 75% of the image height */
		$font_size = $this->height * 0.60;
		$image = @imagecreate($this->width, $this->height) or die('Cannot initialize new GD image stream');
		
		/* set the colours */
		$background_color = imagecolorallocate($image, 255, 255, 255);
		$text_color = imagecolorallocate($image, 58,121,185); #325B91#8A8239
		$noise_color = imagecolorallocate($image, 104,155,206);
		
		/* generate random dots in background */
		for( $i=0; $i<($this->width*$this->height)/3; $i++ ) {
			imagefilledellipse($image, mt_rand(0,$this->width), mt_rand(0,$this->height), 1, 1, $noise_color);
		}
		
		/* generate random lines in background */
		for( $i=0; $i<($this->width*$this->height)/150; $i++ ) {
			imageline($image, mt_rand(0,$this->width), mt_rand(0,$this->height), mt_rand(0,$this->width), mt_rand(0,$this->height), $noise_color);
		}
		
		/* create textbox and add text */
		$textbox = imagettfbbox($font_size, 0, $this->font, $code) or die('Error in imagettfbbox function');
		$x = intval(($this->width - $textbox[4])/2);
		$y = intval(($this->height - $textbox[5])/2);
		imagettftext($image, $font_size, 0, $x, $y, $text_color, $this->font , $code) or die('Error in imagettftext function');
		
		/* output captcha image with img html tag */
		$_SESSION['captchacode'] = md5($code);
		ob_start();
		imagejpeg($image, NULL, 100); 
		//imagejpeg($image);
		imagedestroy($image);
		$i = ob_get_clean();
		$result = '<img src="data:image/jpeg;base64,'.base64_encode($i).'"/>';
		return $result;
	}

}

?>