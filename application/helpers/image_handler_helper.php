<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Image Handler
 *
 * @package    Image Handler Helper
 * @author     Le Sy Dat
 * @category   Image processing
 * @copyright  Le Sy Dat 2012 - 2016
 * @version    2.0
 */

/**
 * Handler a image
 * @return string (path to cache image)
 */
if ( ! function_exists('ih_image')) {
	function ih_image($image_path, $max_width, $max_height, $maintain_ratio = true, $watermark = null, $watermark_padding = -10, $force_cache = false)
	{
		$CI = &get_instance();
		$CI->load->config('image_handler');

		if (! is_file(FCPATH . $image_path)) {
			return  $image_path = base_url('assets/images/image_not_available.png');
		}

		$source_img = pathinfo($image_path);
		$new_image_name = md5($image_path . ':' . filesize(FCPATH . $image_path) . ':' . filemtime(FCPATH . $image_path));
		$new_image_dir = $CI->config->item('image_handler_path') . $new_image_name[0] . '/';
		$new_image_path = $new_image_dir . $new_image_name . '.' . $max_width . 'x' . $max_height . '.' . $source_img['extension'];

		//tra ve file da ton tai neu da co san tren he thong
		if (file_exists(FCPATH . $new_image_path)) return base_url($new_image_path);

		$CI = &get_instance();
		$CI->load->library('image_lib');

		//neu anh goc nho hon thi ko phong to
		if ($force_cache === false) {
			$source_image_info = $CI->image_lib->get_image_properties(FCPATH . $image_path, true);
			if ($source_image_info['width'] <= $max_width && $source_image_info['height'] <= $max_height) {
				return base_url($image_path);
			}
		}

		//tao thu muc neu chua co
		if (! file_exists(FCPATH . $new_image_dir)) mkdir(FCPATH . $new_image_dir, 0777, true);

		$ih_config['image_library'] = 'gd2';
		$ih_config['source_image'] = FCPATH . $image_path;
		$ih_config['maintain_ratio'] = $maintain_ratio;
		$ih_config['width'] = $max_width;
		$ih_config['height'] = $max_height;
		$ih_config['new_image'] = FCPATH . $new_image_path;

		$CI->image_lib->initialize($ih_config);

		if ($CI->image_lib->resize()) {
			if (! is_null($watermark) && file_exists(FCPATH . $watermark)) {
				//chen anh watermark
				$wmconfig['wm_type'] = 'overlay';
				$wmconfig['source_image'] = FCPATH . $new_image_path;
				$wmconfig['wm_padding'] = $watermark_padding;
				$wmconfig['wm_vrt_alignment'] = 'bottom';
				$wmconfig['wm_hor_alignment'] = 'right';
				$wmconfig['wm_overlay_path'] = $watermark;
				$wmconfig['wm_opacity'] = 100;

				$CI->image_lib->initialize($wmconfig);
				$CI->image_lib->watermark();
			}
			$CI->image_lib->clear();
			return base_url($new_image_path);
		} else {
			$CI->image_lib->clear();
			return base_url($image_path);
		}
	}
}

/**
 * Purge image cache
 */
if ( ! function_exists('ih_purge_cache')) {
	function ih_purge_cache()
	{
		$CI = &get_instance();
		$CI->load->helper('file');
		$CI->load->config('image_handler');
		delete_files(FCPATH . $CI->config->item('image_handler_path'), true);
	}
}

/**
 * Convert a image (for cache)
 * @return string (path to cache image)
 */
if ( ! function_exists('ih_convert')) {
	function ih_convert($image_path, $max_width, $max_height, $new_extension, $maintain_ratio = true, $watermark = null, $watermark_padding = -10)
	{
		$CI = &get_instance();
		$CI->load->config('image_handler');

		if (! is_file(FCPATH . $image_path)) {
			$image_path = 'assets/images/image_not_available.png';
		}

		$source_img = pathinfo($image_path);
		$new_image_name = md5($image_path . ':' . filesize(FCPATH . $image_path) . ':' . filemtime(FCPATH . $image_path));
		$new_image_dir = $CI->config->item('image_handler_path') . $new_image_name[0] . '/';
		$new_image_path = $new_image_dir . $new_image_name . '.' . $max_width . 'x' . $max_height . '.' . $new_extension;

		//tra ve file da ton tai neu da co san tren he thong
		if (file_exists(FCPATH . $new_image_path)) return base_url($new_image_path);

		$CI = &get_instance();
		$CI->load->library('image_lib');

		//neu anh goc nho hon thi ko phong to
		$source_image_info = $CI->image_lib->get_image_properties(FCPATH . $image_path, true);
		if ($source_image_info['width'] < $max_width && $source_image_info['height'] < $max_height) {
			return base_url($image_path);
		}

		//tao thu muc neu chua co
		if (! file_exists(FCPATH . $new_image_dir)) mkdir(FCPATH . $new_image_dir, 0777, true);

		$ih_config['image_library'] = 'ImageMagick';
		$ih_config['library_path'] = '/usr/bin';
		$ih_config['source_image'] = FCPATH . $image_path;
		$ih_config['maintain_ratio'] = $maintain_ratio;
		$ih_config['width'] = $max_width;
		$ih_config['height'] = $max_height;
		$ih_config['new_image'] = FCPATH . $new_image_path;

		$CI->image_lib->initialize($ih_config);

		if ($CI->image_lib->resize()) {
			if (! is_null($watermark) && file_exists(FCPATH . $watermark)) {
				//chen anh watermark
				$wmconfig['wm_type'] = 'overlay';
				$wmconfig['source_image'] = FCPATH . $new_image_path;
				$wmconfig['wm_padding'] = $watermark_padding;
				$wmconfig['wm_vrt_alignment'] = 'bottom';
				$wmconfig['wm_hor_alignment'] = 'right';
				$wmconfig['wm_overlay_path'] = $watermark;
				$wmconfig['wm_opacity'] = 100;

				$CI->image_lib->initialize($wmconfig);
				$CI->image_lib->watermark();
			}
			$CI->image_lib->clear();
			return base_url($new_image_path);
		} else {
			$CI->image_lib->clear();
			return base_url($image_path);
		}
	}
}

/**
 * Get real image info after check image orientation
 *
 * @param       string  $filename        Full local path to file
 * @param       bool   $is_file_data     Use this param if filename is file data content
 * @return      array                    Array of image info include width, height, image_type, mime, is_color
 */
if ( ! function_exists('ih_get_real_image_info')) {
	function ih_get_real_image_info($filename, $is_file_data = FALSE)
	{
		// read info by getimagesize function
		if ($is_file_data) $size = @getimagesize('data://text/plain;base64,' . base64_encode($filename));
		else $size = @getimagesize($filename);

		if ($size === FALSE) return FALSE;

		// if image is jpg or tiff, read exif for more accurate
		if ($size['mime'] == 'image/jpeg' OR $size['mime'] == 'image/pjpeg' OR $size['mime'] == 'image/tiff' OR $size['mime'] == 'image/x-tiff') {
			try {
				if ($is_file_data) $exif = exif_read_data('data://text/plain;base64,' . base64_encode($filename));
				else $exif = exif_read_data($filename);

				$info = [
					'width' => $exif['COMPUTED']['Width'],
					'height' => $exif['COMPUTED']['Height'],
					'image_type' => $exif['FileType'],
					'mime' => $exif['MimeType'],
				];

				if ( ! empty($exif['Orientation'])) {
					// if image rotate 90 degrees CW or rotate 90 degrees CCW
					if ($exif['Orientation'] == 6 OR $exif['Orientation'] == 8) {
						$info['width'] = $exif['COMPUTED']['Height'];
						$info['height'] = $exif['COMPUTED']['Width'];
					}
				}
			} catch (Exception $e) {
				$info = [
					'width' => $size[0],
					'height' => $size[1],
					'image_type' => $size[2],
					'mime' => $size['mime'],
				];
			}
		} else {
			$info = [
				'width' => $size[0],
				'height' => $size[1],
				'image_type' => $size[2],
				'mime' => $size['mime'],
			];
		}

		return $info;
	}
}

/**
 * Auto rorate image file after check image orientation
 *
 * @param       string  $filename      Full local path to file
 * @param       bool    $overwrite     Overwrite original file
 * @param       string  $suffix        File name suffix if not overwrite
 * @return      bool
 */
if ( ! function_exists('ih_auto_rotate_image_file')) {
	function ih_auto_rotate_image_file(&$filename, $overwrite = FALSE, $suffix = '_rotated')
	{
		// is file exist
		if (file_exists($filename)) {
			$path_info = pathinfo($filename);
		} else {
			return FALSE;
		}

		// check is image
		$size = @getimagesize($filename);
		if ($size === FALSE) return FALSE;

		// if image is jpg or tiff, read exif to rotate, if not, nothing to do
		if ($size['mime'] == 'image/jpeg' OR $size['mime'] == 'image/pjpeg' OR $size['mime'] == 'image/tiff' OR $size['mime'] == 'image/x-tiff') {
			// get exif
			$exif = @exif_read_data($filename);

			// $image = imagecreatefromstring(file_get_contents($filename));
			$image = new Imagick($filename);
			if ( ! empty($exif['Orientation'])) {
				switch ($exif['Orientation']) {
					case 8:
						// $image = imagerotate($image, 90, 0);
						$image->rotateimage("#000", -90); // rotate 90 degrees CCW
						break;
					case 3:
						// $image = imagerotate($image, 180, 0);
						$image->rotateimage("#000", 180); // rotate 180 degrees
						break;
					case 6:
						// $image = imagerotate($image, -90, 0);
						$image->rotateimage("#000", 90); // rotate 90 degrees CW
						break;
				}
			}

			// remove exif and xmp data
			$image->stripImage();

			//Save to file
			if ($overwrite) {
				$new_image_path = $image_path;
			} else {
				$new_image_path = $path_info['dirname'] . '/' . $path_info['filename'] . $suffix . '.' . $path_info['extension'];
			}

			try {
				$image->writeImage($new_image_path);
				$filename = $new_image_path;
				return TRUE;
			} catch (Exception $e) {
				return FALSE;
			}
		}

		return TRUE;
	}
}

/**
 * Auto rorate image data after check image orientation
 *
 * @param       string  $filedata      Binary file data
 * @return      bool
 */
if ( ! function_exists('ih_auto_rotate_image_data')) {
	function ih_auto_rotate_image_data(&$file_data)
	{
		// check is image
		$size = @getimagesize('data://text/plain;base64,' . base64_encode($file_data));
		if ($size === FALSE) return FALSE;

		// if image is jpg or tiff, read exif to rotate, if not, nothing to do
		if ($size['mime'] == 'image/jpeg' OR $size['mime'] == 'image/pjpeg' OR $size['mime'] == 'image/tiff' OR $size['mime'] == 'image/x-tiff') {
			$exif = @exif_read_data('data://text/plain;base64,' . base64_encode($file_data));

			// $image = imagecreatefromstring($file_data);
			$image = new Imagick();
			$image->readImageBlob($file_data);

			if ( ! empty($exif['Orientation'])) {

				switch ($exif['Orientation']) {
					case 2: // Mirrored horizontal
						$image->flopImage();
						break;
					case 3:
						$image->rotateimage("#000", 180); // Rotate 180 degrees
						break;
					case 4: // Mirrored vertical
						$image->flipImage();
						break;
					case 5: // Mirrored horizontal then rotated 90 CCW
						$image->flopImage();
						$image->rotateimage("#000", -90); // Rotate 90 degrees CCW
						break;
					case 6:
						$image->rotateimage("#000", 90); // Rotate 90 degrees CW
						break;
					case 7: // Mirrored horizontal then rotated 90 CW
						$image->flopImage();
						$image->rotateimage("#000", 90); // Rotate 90 degrees CW
						break;
					case 8:
						$image->rotateimage("#000", -90); // Rotate 90 degrees CCW
						break;
				}
			}

			// remove exif and xmp data
			$image->stripImage();

			// set new image data
			$file_data = $image->getImagesBlob();
		}

		return TRUE;
	}
}