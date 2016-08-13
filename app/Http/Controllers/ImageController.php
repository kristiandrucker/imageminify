<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use Intervention\Image\Facades\Image;

class ImageController extends Controller
{
	private $quality;
	
	public function index($options, $slug)
	{
		if($options == 'd')
		{
			$options = config('image.default');
			$data = '';
			foreach($options as $option) {
				if(!$data == '') {
					$data .= ',' . $option;
				} else {
					$data = $option;
				}
			}
			$options = $data;
		}
		$options = $this->explodeOptions($options);
		$image = $this->generateImage($options, $slug);
		return $image;
    }
	
	public function generateImage($options, $slug)
	{
		$image = Image::cache(function($img) use($options, $slug) {
			$img = $img->make(env('BASE_IMG') . $slug);
			
			foreach($options as $key => $option) {
				$val = explode(':', preg_replace('/[^0-9]/', ':', $option));
				$key = preg_replace('/[^a-zA-Z]/', '', $option);
				$func = $this->functions($key);
				if($val[0] == null) {
					$val[0] = null;
				} elseif($val[1] == null) {
					$val[1] = null;
				}
				if($key == 'x') {
					$img = $img->$func($val[0], $val[1], function($constraint) {
						$constraint->aspectRatio();
					});
				} elseif($key == 'q') {
					$this->quality = $val[1];
				} else {
					$img = $img->$func($val[1]);
				}
			}
		}, 86400, true);
		return $image->response($this->getExtension($slug), $this->quality);
    }
    
	public function explodeOptions($options)
	{
		if(strpos($options, ',') != false) {
			return explode(',', $options);
		} else {
			return [
				$options
			];
		}
    }
	
	public function functions($slug)
	{
		$functions = [
			'B' => 'blur',
			'b' => 'brightness',
			'cir' => 'circle',
			'C' => 'colorize',
			'c' => 'crop',
			'con' => 'contrast',
			'f' => 'flip',
			'fit' => 'fit',
			'g' => 'gamma',
			'G' => 'greyscale',
			'i' => 'invert',
			'x' => 'resize',
			'r' => 'rotate',
			'q' => '',
		];
		return $functions[$slug];
    }
	
	public function getExtension($slug)
	{
		return strtolower(array_reverse(explode('.', $slug))[0]);
    }
}
