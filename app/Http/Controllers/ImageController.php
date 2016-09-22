<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Facades\Image;

class ImageController extends Controller
{
	private $quality;
	
	public function index($options, $slug)
	{
	    $name = implode('-', explode(',', $options)) . '&' . implode('-', explode('/', $slug));
        if($this->getExtension($name) == 'gif') {
            return response(file_get_contents(env('BASE_IMG') . $slug), 200)->header('Content-Type', 'image/gif');
        }
        if(Storage::exists(env('S3_URL') . '/' . $name)) {
            return response(Storage::get(env('S3_URL') . '/' . $name), 200)->header('Content-Type', 'image/jpeg');
        }
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
        Storage::put(env('S3_URL') . '/' . $name, $image);
		return response($image, 200)->header('Content-Type', 'image/jpeg');
    }
	
	public function generateImage($options, $slug)
	{
		$img = Image::make(env('BASE_IMG') . $slug);
			
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
		return (string) $img->encode($this->getExtension($slug), $this->quality);
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
