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
	 * @param string $avatar Get from avatar field in DB
	 * @param string $email User's email
	 * @param int $size Avatar size
	 * @return string Avatar's url
	 */
	function avatar_url($avatar, $email = NULL, $size = 48)
	{
		if (! empty($avatar)) {
			if (strstr(strtolower($avatar), 'http') !== false) {
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

if (! function_exists('display_user')) {
	/**
	 * Return Avatar with full name each user
	 *
	 * @param string $email
	 * @param string $first_name
	 * @param string $last_name
	 * @param string $avatar
	 * @param boolean $avatar_only
	 * @param int $avatar_size
	 * @return string html content include avatar image and full name
	 */
	function display_user($email, $first_name, $last_name, $avatar, $avatar_only = false, $avatar_size = 24)
	{
		$full_name = $first_name . ' ' . $last_name;
		$html = '<img class="user-avatar" title="' . $full_name . '" src="' . avatar_url($avatar, $email, $avatar_size) . '" style="width: ' . $avatar_size . 'px; height: ' . $avatar_size . 'px">';
		if (! $avatar_only) $html .= ' <span class="user-name">' . $full_name . '</span>';
		return $html;
	}
}

if (! function_exists('display_time')) {
	/**
	 * Return time following the local timezone
	 *
	 * @param string $time (Y-m-d H:i:s)
	 * @param string $timezone
	 * @param string $format (optional)
	 * @return string time in local timezone
	 */
	function display_time($time, $timezone = null, $format = 'M j, Y h:i A')
	{
		date_default_timezone_set('UTC');
		$timestamp = strtotime($time);
		if ($timestamp === false) {
			return false;
		}

		$ci =& get_instance();
		$ci->load->helper('date');

		return user_time($timestamp, $timezone, $format);
	}
}