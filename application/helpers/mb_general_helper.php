<?php defined('BASEPATH') || exit('No direct script access allowed');
/**
 * Meeting Bullet Form Helper
 *
 * @package   Meeting Bullet
 * @author    Viet Hoang Duc
 */

if (! function_exists('avatar_url')) {
	/**
	 * Returns a properly linked avatar
	 *
	 * @return string The formatted input element, label tag and wrapping divs.
	 */
	function avatar_url($avatar, $email = NULL, $size = 48)
	{
		if (! empty($avatar)) {
			if (strstr($avatar, 'http') !== false) {
				return $avatar;
			} else {
				if (file_exists('assets/images/users/' . $avatar)) {
					return base_url('images/' . $avatar . '?assets=assets/images/users&ratio=1&width=' . $size . '&height=' . $size);
				}
			}
		}

		if (! empty($email)) {
			return gravatar_url($email, $size);
		}

		return $avatar;
	}
}