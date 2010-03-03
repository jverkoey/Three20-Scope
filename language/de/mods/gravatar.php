<?php
/**
*
* mods/gravatar [German]
*
* @package language
* @version $Id: gravatar.php darkonia$
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
*
*/

/**
* DO NOT CHANGE
*/
if (!defined('IN_PHPBB'))
{
	exit;
}

if (empty($lang) || !is_array($lang))
{
	$lang = array();
}

$lang = array_merge($lang, array(
	'ALLOW_GRAVATAR'			=> 'Erlaube Gravatar',
	'ALLOW_GRAVATAR_EXPLAIN'	=> 'Avatare werden gehosted von Gravatar.com',
	'USE_GRAVATAR'				=> 'BenÃ¼tze Gravatar',
	'USE_GRAVATAR_EXPLAIN'		=> 'BenÃ¼tze den Gravatar der verbunden ist mit der Email Addresse bei diesem Forum Account.',
));

?>