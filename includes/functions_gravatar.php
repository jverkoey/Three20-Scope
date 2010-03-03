<?php
/**
*
* @package phpBB3
* @version $Id: gravatar.php SyntaxError90 $
* @copyright (c) 2009 http://phpbbmodders.net
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
*
*/

/**
* @ignore
*/
if (!defined('IN_PHPBB'))
{
	exit;
}

/**
* Creates the Gravatar URL
*/
function make_gravatar($email)
{
	global $config;
	
	return "http://www.gravatar.com/avatar/" . md5(strtolower(trim($email))) . (($config['avatar_max_width'] > 1 && $config['avatar_max_width'] < 512) ? "&amp;size=" . $config['avatar_max_width'] : '');
}

/**
* Acts in place of the standard avatar processing function. 
*/
function gravatar_process($data, $error)
{
	global $config, $db, $user, $phpbb_root_path, $phpEx;
	
	// Make sure getimagesize works...
	if (($image_data = @getimagesize($data['gravatar'])) === false && (empty($data['width']) || empty($data['height'])))
	{
		$error[] = $user->lang['UNABLE_GET_IMAGE_SIZE'];
		return false;
	}

	if (!empty($image_data) && ($image_data[0] < 2 || $image_data[1] < 2))
	{
		$error[] = $user->lang['AVATAR_NO_SIZE'];
		return false;
	}

	$width = ($data['width'] && $data['height']) ? $data['width'] : $image_data[0];
	$height = ($data['width'] && $data['height']) ? $data['height'] : $image_data[1];

	if ($width < 2 || $height < 2)
	{
		$error[] = $user->lang['AVATAR_NO_SIZE'];
		return false;
	}

	// Check image type
	include_once($phpbb_root_path . 'includes/functions_upload.' . $phpEx);
	$types = fileupload::image_types();

	if (!isset($types[$image_data[2]]))
	{
		$error[] = $user->lang['UNABLE_GET_IMAGE_SIZE'];
	}

	if ($config['avatar_max_width'] || $config['avatar_max_height'])
	{
		if ($width > $config['avatar_max_width'] || $height > $config['avatar_max_height'])
		{
			$error[] = sprintf($user->lang['AVATAR_WRONG_SIZE'], $config['avatar_min_width'], $config['avatar_min_height'], $config['avatar_max_width'], $config['avatar_max_height'], $width, $height);
			return false;
		}
	}

	if ($config['avatar_min_width'] || $config['avatar_min_height'])
	{
		if ($width < $config['avatar_min_width'] || $height < $config['avatar_min_height'])
		{
			$error[] = sprintf($user->lang['AVATAR_WRONG_SIZE'], $config['avatar_min_width'], $config['avatar_min_height'], $config['avatar_max_width'], $config['avatar_max_height'], $width, $height);
			return false;
		}
	}

	return array(AVATAR_REMOTE, $data['gravatar'], $width, $height);
}

?>