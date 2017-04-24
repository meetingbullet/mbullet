<?php defined('BASEPATH') || exit('No direct script access allowed');
/**
 * Meeting Bullet Form Helper
 *
 * @package   Meeting Bullet
 * @author    Viet Hoang Duc
 */

if (! function_exists('mb_form_input')) {
	/**
	 * Returns a properly linked avatar
	 *
	 * @return string The formatted input element, label tag and wrapping divs.
	 */
	function link_avatar($avatar)
	{
		return strstr($avatar, 'http') !== false 
					? $avatar 
					: img_path() . 'users/' . $avatar;
	}
}