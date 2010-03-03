<?php
/** 
*
* @package karmamod (acp)
* @version $Id: acp_karma.php,v 11 2009/10/17 23:19:18 m157y Exp $
* @copyright (c) 2007, 2009 David Lawson, m157y, A_Jelly_Doughnut
* @license http://opensource.org/licenses/gpl-license.php GNU Public License 
*
*/

/**
* @package module_install
*/
class acp_karma_info
{
	function module()
	{
		return array(
			'filename'	=> 'acp_karma',
			'title'		=> 'ACP_KARMA',
			'version'	=> '1.2.3',
			'modes'		=> array(
				'updater'	=> array('title' => 'ACP_KARMA_VERSION_CHECK_MENU', 'auth' => 'acl_a_board', 'cat' => array('ACP_KARMA')),
				'config'	=> array('title' => 'ACP_KARMA_CONFIG',	'auth' => 'acl_a_board',	'cat' => array('ACP_KARMA')),
				'history'	=> array('title' => 'ACP_KARMA_HISTORY','auth' => 'acl_a_viewlogs', 'cat' => array('ACP_KARMA')),
//				'stats'		=> array('title' => 'ACP_KARMA_STATS',	'auth' => 'acl_a_user',		'cat' => array('ACP_KARMA')),
			),
		);
	}

	function install()
	{
	}

	function uninstall()
	{
	}
}

?>