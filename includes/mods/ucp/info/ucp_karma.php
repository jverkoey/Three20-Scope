<?php
/** 
*
* @package karmamod (ucp)
* @version $Id: ucp_karma.php,v 9 2009/10/17 23:19:38 m157y Exp $
* @copyright (c) 2007, 2009 David Lawson, m157y, A_Jelly_Doughnut
* @license http://opensource.org/licenses/gpl-license.php GNU Public License 
*
*/

/**
* @package module_install
*/
class ucp_karma_info
{
	function module()
	{
		return array(
			'filename'	=> 'ucp_karma',
			'title'		=> 'UCP_KARMA',
			'version'	=> '1.2.3',
			'modes'		=> array(
				'karma'	=> array('title' => 'UCP_KARMA', 'auth' => '', 'cat' => array('UCP_PREFS')),
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