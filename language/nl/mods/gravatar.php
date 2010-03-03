<?php
/**
*
* mods/gravatar [Dutch]
*
* @package language
* @version $Id: gravatar.php Erik Frèrejean$
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
	'ALLOW_GRAVATAR'			=> 'Gravatar toestaan',
	'ALLOW_GRAVATAR_EXPLAIN'	=> 'Avatars gehost bij Gravatar.com',
	'USE_GRAVATAR'				=> 'Gebruik Gravatar',
	'USE_GRAVATAR_EXPLAIN'		=> 'Gebruik de Gravatar die geassocieerd is met het e-mail adres van dit account.',
)); 

?>