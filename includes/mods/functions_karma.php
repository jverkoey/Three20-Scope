<?php
/** 
*
* @package karmamod
* @version $Id: functions_karma.php,v 73 2009/10/13 21:31:38 m157y Exp $
* @copyright (c) 2007, 2009 David Lawson, m157y, A_Jelly_Doughnut
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

// Table name
if (isset($table_prefix))
{
	define('KARMA_TABLE', $table_prefix . 'karma');
}

class karmamod
{
	// Config vars
	var $version	= '';
	var $config		= array();

	// Auth vars
	var $can_karma	= false;
	var $user_level	= false;

	// Optimization's vars
	var $current_time	= false;

	/**
	* Constructor
	*/
	function karmamod()
	{
		global $config, $user, $auth;

		// Load language
		$user->add_lang('mods/karma');

		$this->current_time = time();

		// Are we installed?
		if (!isset($config['karma_version']))
		{
			$this->config['enabled'] = false;
			$this->config['enabled_ucp'] = 'false';
			return;
		}
		else if (defined('IN_INSTALL'))
		{
			 // We're at installer?
			 $this->version = $config['karma_version'];
			 return;
		}

		// Move configs to internal array, for less globals in functions
		$this->version						= $config['karma_version'];
		$this->config['enabled']			= ($config['karma_enabled']) ? true : false;
		$this->config['enabled_ucp']		= ($config['karma_enabled_ucp']) ? true : false;
		$this->config['comments']			= ($config['karma_comments']) ? true : false;
		$this->config['comments_reqd']		= ($config['karma_comments_reqd']) ? true : false;
		$this->config['notify_email']		= ($config['email_enable'] && $config['karma_notify_email']) ? true : false;
		$this->config['notify_pm']			= ($config['allow_privmsg'] && $config['karma_notify_pm']) ? true : false;
		$this->config['notify_jabber']		= ($config['jab_enable'] && $config['karma_notify_jabber']) ? true : false;
		$this->config['drafts']				= ($config['karma_drafts']) ? true : false;
		$this->config['icons']				= ($config['karma_icons']) ? true : false;
		$this->config['viewprofile']		= ($config['karma_viewprofile']) ? true : false;
		$this->config['toplist']			= ($config['karma_toplist']) ? true : false;
		$this->config['toplist_users']		= (int) ($user->data['is_registered']) ? $user->data['user_karma_toplist_users'] : $config['karma_toplist_users'];
		$this->config['time']				= (float) $config['karma_time'];
		$this->config['posts']				= (int)	$config['karma_posts'];
		$this->config['comments_per_page']	= (int) $config['karma_comments_per_page'];
		$this->config['power']				= ($config['karma_power']) ? true : false;
		$this->config['power_show']			= ($config['karma_power_show']) ? true : false;
		$this->config['power_max']			= (int) ($config['karma_power_max'] < 1) ? 5 : $config['karma_power_max'];
		$this->config['per_day']			= ($config['karma_per_day'] > 0) ? (int) $config['karma_per_day'] : false;
		$this->config['zebra']				= ($config['karma_zebra']) ? true : false;
		$this->config['anonym_increase']				= ($config['karma_anonym_increase']) ? true : false;
		$this->config['anonym_decrease']				= ($config['karma_anonym_decrease']) ? true : false;
		$this->config['minimum']			= (int) $config['karma_minimum'];

		// Load ban settings, if mod already updated
		if (isset($config['karma_ban']))
		{
			$this->config['ban']					= ($config['karma_ban']) ? true : false;
			$this->config['ban_karma']		= (int) $config['karma_ban_value'];
			$this->config['ban_reason']		= $config['karma_ban_reason'];
			$this->config['ban_give_reason']	= $config['karma_ban_give_reason'];
		}
		else
		{
			$this->config['ban'] = false;
		}

		// Load beta version checker setting, if mod already updated
		if (isset($config['karma_updater_beta']))
		{
			$this->config['updater_beta'] = ($config['karma_updater_beta']) ? true : false;
		}
		else
		{
			$this->config['updater_beta'] = false;
		}

		// Configure user settings
		$this->user_level = ($auth->acl_get('a_')) ? 'admin' : (($user->data['is_registered']) ? 'user' : 'guest');
		if ($this->user_level != 'guest')
		{
			$this->config['user_enabled']		= ($this->config['enabled_ucp'] && !$user->data['user_karma_enable']) ? false : true;
			$this->config['user_notify_email']	= ($this->config['notify_email'] && $user->data['user_karma_notify_email']) ? true : false;
			$this->config['user_notify_pm']		= ($this->config['notify_pm'] && $user->data['user_karma_notify_pm']) ? true : false;
			$this->config['user_notify_jabber']	= ($this->config['notify_jabber'] && $user->data['user_karma_notify_jabber']) ? true : false;
			$this->config['toplist']			= ($this->config['toplist'] && $user->data['user_karma_toplist']) ? true : false;
			$this->config['comments_per_page']	= (int) $user->data['user_karma_comments_per_page'];
			$this->config['comments_self']		= (isset($user->data['user_karma_comments_self']) && $user->data['user_karma_comments_self']) ? true : false;
		}
		else
		{
			$this->config['user_enabled']		= true;
			$this->config['user_notify_email']	= false;
			$this->config['user_notify_pm']		= false;
			$this->config['user_notify_jabber']	= false;
		}

		// Copyright
		$user->lang['TRANSLATION_INFO'] = ((isset($user->lang['TRANSLATION_INFO']) && !empty($user->lang['TRANSLATION_INFO'])) ? $user->lang['TRANSLATION_INFO'] . '<br />' : '') . 'Karma functions powered by Karma MOD &copy; 2007, 2009 m157y';
	}

	/**
	* Generate toplist if required ...
	*/
	function toplist()
	{
		if (!$this->config['enabled'] || !$this->config['toplist'])
		{
			return;
		}

		global $db, $phpbb_root_path, $phpEx, $template;

		$karma_toplist = '';
		$sql = 'SELECT user_id, username, user_colour, user_karma, user_karma_powered
			FROM ' . USERS_TABLE . '
			WHERE user_id <> ' . ANONYMOUS . '
				AND ' . (($this->config['power']) ? 'user_karma_powered' : 'user_karma') . ' > 0
				AND ' . $db->sql_in_set('user_type', array(USER_NORMAL, USER_FOUNDER)) . '
			ORDER BY ' . (($this->config['power']) ? 'user_karma_powered' : 'user_karma') . ' DESC';
		$result = $db->sql_query_limit($sql, $this->config['toplist_users']);

		while ($row = $db->sql_fetchrow($result))
		{
			$user_colour = ($row['user_colour']) ? ' style="color:#' . $row['user_colour'] .'"' : '';
			$karma_toplist .= (($karma_toplist != '') ? ', ' : '') . '<a' . $user_colour . ' href="' . append_sid("{$phpbb_root_path}memberlist.$phpEx", 'mode=viewprofile&amp;u=' . $row['user_id']) . '">' . $row['username'] . '</a> (' . (($this->config['power']) ? $row['user_karma_powered'] : $row['user_karma']) . ')';
		}
		$db->sql_freeresult($result);

		// Assign index specific vars
		$template->assign_vars(array(
			'S_KARMA_TOPLIST'	=> true,
			'KARMA_TOPLIST'		=> $karma_toplist)
		);
	}

	/**
	* Checks can user karma or not?
	*/
	function check_can_karma($user_id = ANONYMOUS, $karma_time = 0, $foe = false, $friend = false)
	{
		if (!$this->config['enabled'])
		{
			return;
		}

		$this->can_karma = false;

		if ($this->user_level != 'guest')
		{
			global $user;

			if ($user->data['user_id'] != $user_id)
			{
				// It disables not needed checks for admins
				if ($this->user_level == 'admin')
				{
					$this->can_karma = true;
				}
				else
				{
					global $auth;

					// Enough posts?
					$this->can_karma = ($user->data['user_posts'] >= $this->config['posts']) ? true : false;

					// Disabled via ACP user-management?
					if (!$auth->acl_get('u_karma_can'))
					{
						$this->can_karma = false;
						return;
					}

					// Enough karma?
					if ($this->config['power'] && ($user->data['user_karma_powered'] < $this->config['minimum']))
					{
						$this->can_karma = false;
						return;
					}
					else if (!$this->config['power'] && ($user->data['user_karma'] < $this->config['minimum']))
					{
						$this->can_karma = false;
						return;
					}

					// Zebra enabled? Okay, let's check friend and foe status.
					if ($this->can_karma && !$this->config['zebra'])
					{
						$this->can_karma = ($foe || $friend) ? false : true;
					}

					// Karma time limit checker
					if ($this->can_karma && (($this->current_time - $karma_time) < ($this->config['time'] * 3600)))
					{
						$this->can_karma = false;
						return;
					}

					// Karmas per day checker
					// If the user already cannot karma, then there is no point in an additional query
					if ($this->can_karma && $this->config['per_day'])
					{
						global $db;

						$sql = 'SELECT COUNT(*) as karmas_per_day
							FROM ' . KARMA_TABLE . '
							WHERE poster_id = ' . $user->data['user_id'] . '
								AND karma_time > ' . (time() - 86400);
						$result = $db->sql_query($sql);
						$row = $db->sql_fetchrow($result);

						if ($row['karmas_per_day'] >= $this->config['per_day'])
						{
							$this->can_karma = false;
						}
					}
				}
			}
		}
	}

	/**
	* Check karma's install directory
	*/
	function acp_main_install()
	{
		global $phpbb_root_path;
		// Warn if install is still present
		if (file_exists($phpbb_root_path . 'install_karma'))
		{
			global $template, $user;

			$template->assign_vars(array(
				'S_REMOVE_INSTALL'	=> true,

				'L_REMOVE_INSTALL'	=> $user->lang['ACP_KARMA_REMOVE_INSTALL'],
			));
		}

	}
	
	/**
	* Adds karma modules to module selector
	*/
	function acp_modules_global(&$fileinfo, $module_class)
	{
		global $phpbb_root_path, $phpEx;

		if ($module_class != 'mcp' && $module_class != 'stats')
		{
			$directory = $phpbb_root_path . 'includes/mods/' . $module_class . '/info/';
			$class = $module_class . '_karma_info';
	
			if (!class_exists($class))
			{
				include($directory . $module_class . '_karma.' . $phpEx);
			}
	
			// Get module title tag
			if (class_exists($class))
			{
				$c_class = new $class();
				$module_info = $c_class->module();
				$fileinfo[str_replace($module_class . '_', '', $module_info['filename'])] = $module_info;
			}
		}
	}

	/**
	* Edit path to module if we want to add karma module
	*/
	function acp_modules_single(&$directory, $module, $module_class)
	{
		if ($module == 'karma')
		{
			$directory = str_replace($module_class, 'mods/' . $module_class, $directory);
		}
	}

	/**
	* Adds karma's images to imageset editor
	*/
	function acp_styles_imageset(&$imageset_keys)
	{
		if (!$this->config['enabled'])
		{
			return;
		}

		$imageset_keys['ui'] = array_merge($imageset_keys['ui'], array('icon_karma_increase', 'icon_karma_decrease'));
	}

	/**
	* Adds karma information to user's overview page
	*/
	function acp_users_overview($user_row)
	{
		if (!$this->config['enabled'])
		{
			return;
		}

		global $template;

		$template->assign_vars(array(
			'USER_KARMA'		=> $user_row['user_karma'],
		));
	}

	/**
	* Adds karma's input information and template to users management page at ACP
	*/
	function acp_users_data(&$data, $user_row)
	{
		if (!$this->config['enabled'])
		{
			return;
		}

		global $template, $config, $user;

		$data = array_merge($data, array(
			'karma_enable'				=> request_var('karma_enable', (bool) $user_row['user_karma_enable']),
			'karma_notify_email'		=> request_var('karma_notify_email', (bool) $user_row['user_karma_notify_email']),
			'karma_notify_pm'			=> request_var('karma_notify_pm', (bool) $user_row['user_karma_notify_pm']),
			'karma_notify_jabber'		=> request_var('karma_notify_jabber', (bool) $user_row['user_karma_notify_jabber']),
			'karma_toplist'				=> request_var('karma_toplist', (bool) $user_row['user_karma_toplist']),
			'karma_toplist_users'		=> request_var('karma_toplist_users', (int) $user_row['user_karma_toplist_users']),
			'karma_comments_per_page'	=> request_var('karma_comments_per_page', (int) $user_row['user_karma_comments_per_page']),
			'karma_comments_self'		=> request_var('karma_comments_self', (bool) $user_row['user_karma_comments_self']),
			'karma_comments_sk'		=> request_var('karma_comments_sk', ($user_row['user_karma_comments_sortby_type']) ? $user_row['user_karma_comments_sortby_type'] : 't'),
			'karma_comments_sd'		=> request_var('karma_comments_sd', ($user_row['user_karma_comments_sortby_dir']) ? $user_row['user_karma_comments_sortby_dir'] : 'd'),
			'karma_comments_st'		=> request_var('karma_comments_st', ($user_row['user_karma_comments_show_days']) ? $user_row['user_karma_comments_show_days'] : 0),
		));

		// Comments ordering options
		$sort_dir_text = array('a' => $user->lang['ASCENDING'], 'd' => $user->lang['DESCENDING']);
		$limit_comments_days = array(0 => $user->lang['KARMA_ALL_COMMENTS'], 1 => $user->lang['1_DAY'], 7 => $user->lang['7_DAYS'], 14 => $user->lang['2_WEEKS'], 30 => $user->lang['1_MONTH'], 90 => $user->lang['3_MONTHS'], 180 => $user->lang['6_MONTHS'], 365 => $user->lang['1_YEAR']);

		$sort_by_comments_text = array('a' => $user->lang['AUTHOR'], 't' => $user->lang['KARMA_SORT_TIME'], 'p' => $user->lang['KARMA_SORT_POST'], 'o' => $user->lang['KARMA_SORT_TOPIC'], 'f' => $user->lang['KARMA_SORT_FORUM']);
		$sort_by_comments_sql = array('a' => 'u.username_clean', 't' => 'k.karma_time', 'p' => 'k.post_id', 'o' => 'k.topic_id', 'f' => 'k.forum_id');

		$s_limit_comments_days = '<select name="karma_comments_st">';
		foreach ($limit_comments_days as $day => $text)
		{
			$selected = ($data['karma_comments_st'] == $day) ? ' selected="selected"' : '';
			$s_limit_comments_days .= '<option value="' . $day . '"' . $selected . '>' . $text . '</option>';
		}
		$s_limit_comments_days .= '</select>';

		$s_sort_comments_key = '<select name="karma_comments_sk">';
		foreach ($sort_by_comments_text as $key => $text)
		{
			$selected = ($data['karma_comments_sk'] == $key) ? ' selected="selected"' : '';
			$s_sort_comments_key .= '<option value="' . $key . '"' . $selected . '>' . $text . '</option>';
		}
		$s_sort_comments_key .= '</select>';

		$s_sort_comments_dir = '<select name="karma_comments_sd">';
		foreach ($sort_dir_text as $key => $value)
		{
			$selected = ($data['karma_comments_sd'] == $key) ? ' selected="selected"' : '';
			$s_sort_comments_dir .= '<option value="' . $key . '"' . $selected . '>' . $value . '</option>';
		}
		$s_sort_comments_dir .= '</select>';

		$template->assign_vars(array(
			'S_ENABLE'			=> $data['karma_enable'],
			'S_NOTIFY_EMAIL'	=> $data['karma_notify_email'],
			'S_NOTIFY_PM'		=> $data['karma_notify_pm'],
			'S_NOTIFY_JABBER'	=> $data['karma_notify_jabber'],
			'S_TOPLIST'			=> $data['karma_toplist'],
			'TOPLIST_USERS'		=> $data['karma_toplist_users'],
			'COMMENTS_PER_PAGE'	=> $data['karma_comments_per_page'],
			'S_COMMENTS_SELF'	=> $data['karma_comments_self'],

			'S_COMMENTS_SORT_DAYS'		=> $s_limit_comments_days,
			'S_COMMENTS_SORT_KEY'		=> $s_sort_comments_key,
			'S_COMMENTS_SORT_DIR'		=> $s_sort_comments_dir,

			'S_KARMA_ENABLE'	=> true)
		);
	}

	/**
	* Check errors at information on users management page at ACP
	*/
	function acp_users_error(&$error, $data)
	{
		if (!$this->config['enabled'])
		{
			return;
		}

		$error = array_merge($error, validate_data($data, array(
					'karma_comments_sk'	=> array('string', false, 1, 1),
					'karma_comments_sd'	=> array('string', false, 1, 1),
		)));
	}

	/**
	* Adds karma's information to sql of users management page at ACP
	*/
	function acp_users_sql(&$sql_ary, $data)
	{
		if (!$this->config['enabled'])
		{
			return;
		}

		$sql_ary = array_merge($sql_ary, array(
			'user_karma_enable'				=> $data['karma_enable'],
			'user_karma_notify_email'		=> $data['karma_notify_email'],
			'user_karma_notify_pm'			=> $data['karma_notify_pm'],
			'user_karma_notify_jabber'		=> $data['karma_notify_jabber'],
			'user_karma_toplist'			=> $data['karma_toplist'],
			'user_karma_toplist_users'		=> $data['karma_toplist_users'],
			'user_karma_comments_per_page'	=> $data['karma_comments_per_page'],
			'user_karma_comments_self'		=> $data['karma_comments_self'],
			'user_karma_comments_sortby_type'		=> $data['karma_comments_sk'],
			'user_karma_comments_sortby_dir'		=> $data['karma_comments_sd'],
			'user_karma_comments_show_days'	=> $data['karma_comments_st'],
		));
	}

	/**
	* Hide karma modules, if they doesn't need
	*/
	function module_disable(&$modules)
	{
		// If karma mod doesn't installed, we don't do anything
		if (!$this->config['enabled'] && empty($this->version))
		{
			return;
		}

		foreach ($modules as $key => $row)
		{
			if (!$this->config['enabled'] && $row['module_class'] == 'ucp' && $row['module_basename'] == 'karma')
			{
				// Hide UCP module, if karma disabled
			  unset($modules[$key]);
			}
		}
	}

	/**
	* Load karma modules from mods subdirectory
	*/
	function module_load(&$module_path, $p_name, $p_class)
	{
		if ($p_name == 'karma')
		{
			$module_path = str_replace($p_class, 'mods/' . $p_class, $module_path);
		}
	}	

	/**
	* Adds karma's information to FAQ page
	*/
	function faq()
	{
		if (!$this->config['enabled'])
		{
			return;
		}

		global $user;
		$user->add_lang('mods/karma_faq');
	}

	/**
	* Adds karma's information to viewprofile page
	*/
	function memberlist_viewprofile($member, $foe = false, $friend = false)
	{
		$user_karma_disabled = ($this->config['enabled_ucp'] && !$member['user_karma_enable']);
		if (!$this->config['enabled'] || $user_karma_disabled)
		{
			return;
		}

		global $auth, $db, $user, $template, $phpbb_root_path, $phpEx;

		// Select last karma time for this user
		$sql = 'SELECT k.karma_time, k2.karma_time as karmaed
			FROM ' . KARMA_TABLE . ' k
			LEFT JOIN ' . KARMA_TABLE . ' k2
				ON (k2.user_id = ' . $member['user_id'] . '
					AND k2.poster_id = ' . $user->data['user_id'] . '
					AND k2.forum_id = 0
					AND k2.topic_id = 0
					AND k2.post_id = 0)
			WHERE k.user_id = ' . $member['user_id'] . '
				AND k.poster_id = ' . $user->data['user_id'] . '
			ORDER BY k.karma_time DESC';
		$result = $db->sql_query_limit($sql, 1);
		$row = $db->sql_fetchrow($result);

		$this->check_can_karma($member['user_id'], $row['karma_time'], $foe, $friend);

		$can_comments = (!$auth->acl_get('u_karma_view') || ($member['user_karma_comments_self'] && $member['user_id'] != $user->data['user_id'])) ? false : true;
		$can_comments = ($member['user_id'] == $user->data['user_id'] || $this->user_level == 'admin') ? true : $can_comments;

		$template->assign_vars(array(
			'KARMA'				=> ($this->config['power']) ? $member['user_karma_powered'] : $member['user_karma'],

			'KARMA_INCREASE_IMG'	=> $user->img('icon_karma_increase', $user->lang['IMG_ICON_KARMA_INCREASE']),
			'KARMA_DECREASE_IMG'	=> $user->img('icon_karma_decrease', $user->lang['IMG_ICON_KARMA_DECREASE']),

			'U_KARMA_INCREASE'	=> ($this->can_karma && !$user_karma_disabled && !$row['karmaed']) ? append_sid("{$phpbb_root_path}karma.$phpEx", 'u=' . $member['user_id'] . '&amp;mode=update&amp;action=increase') : '',
			'U_KARMA_DECREASE'	=> ($this->can_karma && !$user_karma_disabled && !$row['karmaed']) ? append_sid("{$phpbb_root_path}karma.$phpEx", 'u=' . $member['user_id'] . '&amp;mode=update&amp;action=decrease') : '',
			'U_KARMA_COMMENTS'	=> ($this->config['viewprofile'] && $this->config['comments'] && $can_comments) ? append_sid("{$phpbb_root_path}karma.$phpEx", 'mode=viewcomments&amp;u=' . $member['user_id']) : '',

			'S_KARMA'			=> true)
		);
	}

	/**
	* Add karma option to the dropdown in search
	*/
	function search_dropdown(&$sort_by_text)
	{
		if (!$this->config['enabled'])
		{
			return;
		}

		global $user;

		$sort_by_text['k'] = $user->lang['KARMA'];
	}

	/**
	* Add some SQL to the order by options in search
	*/
	function search_order_sql(&$sort_by_sql)
	{
		if (!$this->config['enabled'])
		{
			return;
		}

		global $show_results;

		$sort_by_sql['k'] = (($show_results == 'posts') ? 'p.post_karma_search' : 't.topic_karma_search') . (($this->config['power']) ? '_powered' : '');
	}

	/**
	* Update viewtopic's SQL-query for per user karma time work
	* This may need to be worked on for later versions of the Karma MOD.
	*/
	function viewtopic_sql(&$sql, $forum_id = 0, $topic_id = 0)
	{
		if (!$this->config['enabled'])
		{
			return;
		}

		global $user;

		$sql = str_replace('p.*', 'p.*, k.karma_time, k2.karma_time as karmaed', $sql);
		$sql = str_replace('WHERE', 'LEFT JOIN ' . KARMA_TABLE . ' k ON (k.poster_id = ' . $user->data['user_id'] . ' AND k.user_id = p.poster_id) LEFT JOIN ' . KARMA_TABLE . ' k2 ON (k2.poster_id = ' . $user->data['user_id'] . ' AND k2.post_id = p.post_id AND k2.user_id = p.poster_id) WHERE', $sql);
	}

	/**
	* Adds karmaed information to posts rowset
	*/
	function viewtopic_rowset(&$rowset, $row)
	{
		if (!$this->config['enabled'])
		{
			return;
		}

		$rowset['karmaed'] = (isset($row['karmaed'])) ? true : false;
		$rowset['post_karma'] = (int) ($this->config['power']) ? $row['post_karma_powered'] : $row['post_karma'];
	}

	/**
	* Adds karma's information to user's cache on viewtopic page
	*/
	function viewtopic_usercache(&$user_cache, $poster_id, $row)
	{
		if (!$this->config['enabled'])
		{
			return;
		}

		if ($poster_id == ANONYMOUS || !$this->config['enabled'])
		{
			$karma_cache = array(
				'karma'				=> 0,
				'karma_can'			=> false,
				'karma_enabled'		=> false,
				'karma_comments'	=> false,
			);
		}
		else
		{
			global $user, $auth, $forum_id;

			$this->check_can_karma($poster_id, $row['karma_time'], $row['foe'], $row['friend']);
			
			$can_comments = (!$auth->acl_get('f_karma_view', $forum_id) || !$auth->acl_get('u_karma_view') || ($row['user_karma_comments_self'] && $poster_id != $user->data['user_id'])) ? false : true;
			$can_comments = ($poster_id == $user->data['user_id'] || $this->user_level == 'admin') ? true : $can_comments;

			$karma_cache = array(
				'karma'				=> ($this->config['power']) ? $row['user_karma_powered'] : $row['user_karma'],
				'karma_can'			=> ($this->can_karma && $auth->acl_get('f_karma_can', $forum_id)) ? true : false,
				'karma_enabled'		=> (!$this->config['enabled_ucp'] || $row['user_karma_enable']) ? true : false,
				'karma_comments'	=> ($can_comments) ? true : false,
			);
		}
		$user_cache = array_merge($user_cache, $karma_cache);
	}

	/**
	* Adds karma's information to post on viewtopic page
	*/
	function viewtopic_postrow(&$postrow, $user_cache, $poster_id, $forum_id, $topic_id, $row)
	{
		if (!$this->config['enabled'])
		{
			return;
		}

		$post_id = $row['post_id'];
		$karmaed = $row['karmaed'];
		$post_karma = $row['post_karma'];

		global $phpbb_root_path, $phpEx, $user;

		$postrow = array_merge($postrow, array(
			'KARMA'				=> $user_cache['karma'],
			'POST_KARMA'  => $post_karma,

			'KARMA_INCREASE_IMG'	=> $user->img('icon_karma_increase', $user->lang['IMG_ICON_KARMA_INCREASE']),
			'KARMA_DECREASE_IMG'	=> $user->img('icon_karma_decrease', $user->lang['IMG_ICON_KARMA_DECREASE']),

			'U_KARMA_INCREASE'	=> ($user_cache['karma_can'] && $user_cache['karma_enabled'] && !$karmaed) ? append_sid("{$phpbb_root_path}karma.$phpEx", 'u=' . $poster_id . '&amp;f=' . $forum_id . '&amp;t=' . $topic_id . '&amp;p=' . $post_id . '&amp;mode=update&amp;action=increase') : '',
			'U_KARMA_DECREASE'	=> ($user_cache['karma_can'] && $user_cache['karma_enabled'] && !$karmaed) ? append_sid("{$phpbb_root_path}karma.$phpEx", 'u=' . $poster_id . '&amp;f=' . $forum_id . '&amp;t=' . $topic_id . '&amp;p=' . $post_id . '&amp;mode=update&amp;action=decrease') : '',
			'U_KARMA_COMMENTS'	=> ($user_cache['karma_enabled'] && $this->config['viewprofile'] && $this->config['comments'] && $user_cache['karma_comments'] && $user->data['user_id'] != ANONYMOUS) ? append_sid("{$phpbb_root_path}karma.$phpEx", 'mode=viewcomments&amp;u=' . $poster_id) : '',

			'S_KARMA_ENABLED'	=> $user_cache['karma_enabled'] && $this->config['enabled'])
		);
	}

	/**
	* Below placed functions which used by karma.php
	*
	*
	* Submit karma to db
	*/
	function submit_karma($data)
	{
		global $db, $user;

		$current_time = time();

		// Insert data to DB
		$sql_ary = array(
			'forum_id'			=> $data['forum_id'],
			'topic_id'			=> $data['topic_id'],
			'post_id'			=> $data['post_id'],
			'user_id'			=> $data['user_id'],
			'poster_id'			=> (int) $user->data['user_id'],
			'icon_id'			=> $data['icon_id'],
			'poster_ip'			=> $user->ip,
			'karma_time'		=> $current_time,
			// TODO: approved status for comments
			'karma_approved'	=> 1,
			'enable_bbcode'		=> $data['enable_bbcode'],
			'enable_smilies'	=> $data['enable_smilies'],
			'enable_magic_url'	=> $data['enable_urls'],
			'comment_text'		=> $data['comment'],
			'comment_checksum'	=> $data['comment_md5'],
			'bbcode_bitfield'	=> $data['bbcode_bitfield'],
			'bbcode_uid'		=> $data['bbcode_uid'],
			'karma_action'		=> ($data['action'] == 'decrease') ? '-' : '+',
			'karma_power'		=> $data['karma_power'],
		);

		$sql = 'INSERT INTO ' . KARMA_TABLE . ' ' . $db->sql_build_array('INSERT', $sql_ary);
		$db->sql_query($sql);

		// Update poster's karma
		$sql = 'UPDATE ' . USERS_TABLE . '
			SET user_karma = user_karma ' . (($data['action'] == 'decrease') ? '-' : '+')  . ' 1, user_karma_powered = user_karma_powered ' . (($data['action'] == 'decrease') ? '- ' : '+ ') . $data['karma_power'] . '
			WHERE user_id = ' . $data['user_id'];
		$db->sql_query($sql);

		// Now we update the posts & topics tables with a bit of information
		$sql = 'UPDATE ' . POSTS_TABLE . '
			SET post_karma = post_karma ' . (($data['action'] == 'decrease') ? '-' : '+') . ' 1, post_karma_powered = post_karma_powered ' . ($data['action'] == 'decrease' ? '- ' : '+ ') . (int) $data['karma_power'] . ', post_karma_count = post_karma_count + 1, post_karma_search = (post_karma / post_karma_count), post_karma_search_powered = (post_karma_powered / post_karma_count)
			WHERE post_id  = ' . (int) $data['post_id'];
		$db->sql_query($sql);

		$sql = 'UPDATE ' . TOPICS_TABLE . '
			SET topic_karma = topic_karma ' . (($data['action'] == 'decrease') ? '-' : '+') . ' 1, topic_karma_powered = topic_karma_powered ' . ($data['action'] == 'decrease' ? '- ' : '+ ') . (int) $data['karma_power'] . ', topic_karma_count = topic_karma_count + 1, topic_karma_search = ((topic_karma / topic_karma_count) / (topic_replies + 1)), topic_karma_search_powered = ((topic_karma_powered / topic_karma_count) / (topic_replies + 1))
			WHERE topic_id = ' . (int) $data['topic_id'];
		$db->sql_query($sql);

		// Send Notifications
		$this->user_notification($data);

		// User banning, based on karma
		$this->ban_user($data['user_id']);

		return;
	}

	/**
	* Notify users about karma's changes (via PM/email/jabber)
	*/
	function user_notification($data)
	{
		global $user, $db;

		$forum_id	= $data['forum_id'];
		$topic_id	= $data['topic_id'];
		$post_id	= $data['post_id'];
		$user_id	= $data['user_id'];

		$sql = 'SELECT user_karma_notify_pm, user_karma_notify_email, user_karma_notify_jabber
			FROM ' . USERS_TABLE . '
			WHERE user_id = ' . $user_id;
		$result = $db->sql_query($sql);
		$to_user = $db->sql_fetchrow($result);
		$notifications['pm'] = ($this->config['notify_pm'] && $to_user['user_karma_notify_pm']);
		$notifications['email'] = ($this->config['notify_email'] && $to_user['user_karma_notify_email']);
		$notifications['jabber'] = ($this->config['notify_jabber'] && $to_user['user_karma_notify_jabber']);

		$from_user_id = $user->data['user_id'];
		$from_username = $user->data['username'];
		// Select template for messenger, subject and message
		if ($data['action'] == 'increase')
		{
			$subject	= 'KARMA_NOTIFY_INCREASE_SUBJECT';
			$message	= 'KARMA_NOTIFY_INCREASE_MESSAGE';
			$notify_tpl	= 'karma_notify_increase';
			if ($this->config['anonym_increase'])
			{
				$message .= '_ANONYM';
				$notify_tpl .= '_anonym';
				$from_user_id = $data['user_id'];
				$from_username = $user->lang['KARMA_NOTIFY_HIDDEN_SENDER'];
			}
		}
		else
		{
			$subject	= 'KARMA_NOTIFY_DECREASE_SUBJECT';
			$message	= 'KARMA_NOTIFY_DECREASE_MESSAGE';
			$notify_tpl	= 'karma_notify_decrease';
			if ($this->config['anonym_decrease'])
			{
				$message .= '_ANONYM';
				$notify_tpl .= '_anonym';
				$from_user_id = $data['user_id'];
				$from_username = $user->lang['KARMA_NOTIFY_HIDDEN_SENDER'];
			}
		}
		if ($this->config['power_show'])
		{
			$message	.= '_POWERED';
			$notify_tpl	.= '_powered';
		}

		if ($notifications['pm'])
		{
			global $config, $phpbb_root_path, $phpEx;

			$subject = $user->lang[$subject];
			$message = sprintf($user->lang[$message], $user->data['username'], $data['karma_power']);
			$message .= (!empty($data['comment'])) ? "\r\n" . sprintf($user->lang['KARMA_NOTIFY_MESSAGE_COMMENTS'], $user->data['username']) . "\r\n\r\n" . $data['comment'] : '';
			$message .= "\r\n\r\n" . $this->generate_backlink($forum_id, $topic_id, $post_id, $user_id); 

			include($phpbb_root_path . 'includes/functions_privmsgs.' . $phpEx);

			$message_parser = new parse_message();

			$message = utf8_normalize_nfc($message);
			$options = '';
			generate_text_for_storage($message, $data['bbcode_uid'], $data['bbcode_bitfield'], $options, true, true, true);

			$icon_id = $data['icon_id'];

			// Always attach signature status
			$enable_sig		= (!$config['allow_sig'] || !$config['allow_sig_pm']) ? false : (($user->optionget('attachsig')) ? true : false);

			// Store message, sync counters
			$pm_data = array(
				'from_user_id'			=> $from_user_id,
				'from_user_ip'			=> $user->data['user_ip'],
				'from_username'			=> $from_username,
				'icon_id'				=> (int) $icon_id,
				'enable_sig'			=> (bool) $enable_sig,
				'enable_bbcode'			=> $data['enable_bbcode'],
				'enable_smilies'		=> $data['enable_smilies'],
				'enable_urls'			=> $data['enable_urls'],
				'bbcode_bitfield'		=> $data['bbcode_bitfield'],
				'bbcode_uid'			=> $data['bbcode_uid'],
				'author_id'			=> $user->data['user_id'],
				'message'				=> $message,
				'address_list'			=> array ('u' => array($data['user_id'] => 'to'))
			);
			unset($message_parser);

			submit_pm('post', $subject, $pm_data, false);
		}

		if ($notifications['email'] || $notifications['jabber'])
		{
			if ($notifications['email'] && $notifications['jabber'] && $config['email_enable'] && $config['jab_enable'])
			{
				$method = NOTIFY_BOTH;
			}
			else if ($notifications['email'] && $config['email_enable'])
			{
				$method = NOTIFY_EMAIL;
			}
			else if ($notifications['jabber'] && $config['jab_enable'])
			{
				$method = NOTIFY_IM;
			}
			else
			{
				return;
			}

			$comment = '';
			if ($this->config['comments'] && $data['comment'])
			{
				$comment = $data['comment'];
				// make list items visible as such
				if ($data['bbcode_uid'])
				{
					$comment = str_replace('[*:' . $data['bbcode_uid'] . ']', '&sdot;&nbsp;', $comment);
					// no BBCode in comment
					strip_bbcode($comment, $data['bbcode_uid']);
				}
				$comment = "\r\n" . $user->lang['KARMA_NOTIFY_MESSAGE_COMMENTS'] . "\r\n" . $comment . "\r\n";
			}

			// Now, we are able to really send out notifications
			global $db, $phpbb_root_path, $phpEx;

			include_once($phpbb_root_path . 'includes/functions_messenger.'.$phpEx);
			$messenger = new messenger();

			$messenger->template($notify_tpl, $user->data['user_lang']);

			$messenger->to($user->data['user_email'], $user->data['username']);
			$messenger->im($user->data['user_jabber'], $user->data['username']);

			$messenger->assign_vars(array(
				'USERNAME'		=> htmlspecialchars_decode($data['username']),
				'AUTHOR_NAME'	=> htmlspecialchars_decode($user->data['username']),
				'POWER'			=> $data['karma_power'],
				'BACKLINK'		=> $this->generate_backlink($forum_id, $topic_id, $post_id, $user_id),
				
				'COMMENT'		=> $comment,
			));

			$messenger->send($method);

			$messenger->save_queue();
		}
	}

	/**
	* Thus function generate backlinks for karma changes notifications
	*/
	function generate_backlink($forum_id = 0, $topic_id = 0, $post_id = 0, $user_id = 0)
	{
		global $user, $phpEx;

		$viewtopic = false;

		if ($post_id)
		{
			$message = $user->lang['KARMA_NOTIFY_BACKLINK_POST'];
			$viewtopic = true;
		}
		else if ($topic_id)
		{
			$message = $user->lang['KARMA_NOTIFY_BACKLINK_TOPIC'];
			$viewtopic = true;
		}
		else if ($forum_id)
		{
			// Actually, we can't be here.
			// But it's placed here for excluding possible errors. 
			$message = $user->lang['KARMA_NOTIFY_BACKLINK_FORUM'];
			$viewtopic = true;
		}
		else
		{
			$message = $user->lang['KARMA_NOTIFY_BACKLINK_PROFILE'];
		}

		if ($viewtopic)
		{
			$url = generate_board_url() . "/viewtopic.$phpEx?" . (($forum_id) ? "f=$forum_id&" : '') . (($topic_id) ? "t=$topic_id&" : '') . (($post_id) ? "p=$post_id#p$post_id" : '');
		}
		else
		{
			$url = generate_board_url() . "/memberlist.$phpEx?mode=viewprofile&u=$user_id";
		}

		$backlink = $message . $url;

		return $backlink;
	}

	/**
	* Prepare profile data
	* Used on viewcomments page
	* Copied from memberlist.php
	*/
	function show_profile($data)
	{
		global $config, $auth, $template, $user, $phpEx, $phpbb_root_path;

		$username = $data['username'];
		$user_id = $data['user_id'];

		$rank_title = $rank_img = $rank_img_src = '';
		get_user_rank($data['user_rank'], $data['user_posts'], $rank_title, $rank_img, $rank_img_src);

		if (!empty($data['user_allow_viewemail']) || $auth->acl_get('a_email'))
		{
			$email = ($config['board_email_form'] && $config['email_enable']) ? append_sid("{$phpbb_root_path}memberlist.$phpEx", 'mode=email&amp;u=' . $user_id) : (($config['board_hide_emails'] && !$auth->acl_get('a_email')) ? '' : 'mailto:' . $data['user_email']);
		}
		else
		{
			$email = '';
		}

		if ($config['load_onlinetrack'])
		{
			$update_time = $config['load_online_time'] * 60;
			$online = (time() - $update_time < $data['session_time'] && ((isset($data['session_viewonline'])) || $auth->acl_get('u_viewonline'))) ? true : false;
		}
		else
		{
			$online = false;
		}

		if ($data['user_allow_viewonline'] || $auth->acl_get('u_viewonline'))
		{
			$last_visit = (!empty($data['session_time'])) ? $data['session_time'] : $data['user_lastvisit'];
		}
		else
		{
			$last_visit = '';
		}

		$age = '';

		if ($data['user_birthday'])
		{
			list($bday_day, $bday_month, $bday_year) = array_map('intval', explode('-', $data['user_birthday']));

			if ($bday_year)
			{
				$now = getdate(time() + $user->timezone + $user->dst - date('Z'));

				$diff = $now['mon'] - $bday_month;
				if ($diff == 0)
				{
					$diff = ($now['mday'] - $bday_day < 0) ? 1 : 0;
				}
				else
				{
					$diff = ($diff < 0) ? 1 : 0;
				}

				$age = (int) ($now['year'] - $bday_year - $diff);
			}
		}

		// Dump it out to the template
		return array(
			'AGE'			=> $age,
			'RANK_TITLE'	=> $rank_title,
			'JOINED'		=> $user->format_date($data['user_regdate']),
			'VISITED'		=> (empty($last_visit)) ? ' - ' : $user->format_date($last_visit),
			'POSTS'			=> ($data['user_posts']) ? $data['user_posts'] : 0,
			'WARNINGS'		=> isset($data['user_warnings']) ? $data['user_warnings'] : 0,

			'USERNAME_FULL'		=> get_username_string('full', $user_id, $username, $data['user_colour']),
			'USERNAME'			=> get_username_string('username', $user_id, $username, $data['user_colour']),
			'USER_COLOR'		=> get_username_string('colour', $user_id, $username, $data['user_colour']),
			'U_VIEW_PROFILE'	=> get_username_string('profile', $user_id, $username, $data['user_colour']),

			'ONLINE_IMG'		=> (!$config['load_onlinetrack']) ? '' : (($online) ? $user->img('icon_user_online', 'ONLINE') : $user->img('icon_user_offline', 'OFFLINE')),
			'S_ONLINE'			=> ($config['load_onlinetrack'] && $online) ? true : false,
			'RANK_IMG'			=> $rank_img,
			'RANK_IMG_SRC'		=> $rank_img_src,
			'ICQ_STATUS_IMG'	=> (!empty($data['user_icq'])) ? '<img src="http://web.icq.com/whitepages/online?icq=' . $data['user_icq'] . '&amp;img=5" width="18" height="18" />' : '',
			'S_JABBER_ENABLED'	=> ($config['jab_enable']) ? true : false,

			'U_SEARCH_USER'	=> ($auth->acl_get('u_search')) ? append_sid("{$phpbb_root_path}search.$phpEx", "author_id=$user_id&amp;sr=posts") : '',
			'U_NOTES'		=> $auth->acl_getf_global('m_') ? append_sid("{$phpbb_root_path}mcp.$phpEx", 'i=notes&amp;mode=user_notes&amp;u=' . $user_id, true, $user->session_id) : '',
			'U_WARN'		=> $auth->acl_get('m_warn') ? append_sid("{$phpbb_root_path}mcp.$phpEx", 'i=warn&amp;mode=warn_user&amp;u=' . $user_id, true, $user->session_id) : '',
			'U_PM'			=> ($config['allow_privmsg'] && $auth->acl_get('u_sendpm') && ($data['user_allow_pm'] || $auth->acl_gets('a_', 'm_') || $auth->acl_getf_global('m_'))) ? append_sid("{$phpbb_root_path}ucp.$phpEx", 'i=pm&amp;mode=compose&amp;u=' . $user_id) : '',
			'U_EMAIL'		=> $email,
			'U_WWW'			=> (!empty($data['user_website'])) ? $data['user_website'] : '',
			'U_ICQ'			=> ($data['user_icq']) ? 'http://www.icq.com/people/webmsg.php?to=' . $data['user_icq'] : '',
			'U_AIM'			=> ($data['user_aim'] && $auth->acl_get('u_sendim')) ? append_sid("{$phpbb_root_path}memberlist.$phpEx", 'mode=contact&amp;action=aim&amp;u=' . $user_id) : '',
			'U_YIM'			=> ($data['user_yim']) ? 'http://edit.yahoo.com/config/send_webmesg?.target=' . $data['user_yim'] . '&amp;.src=pg' : '',
			'U_MSN'			=> ($data['user_msnm'] && $auth->acl_get('u_sendim')) ? append_sid("{$phpbb_root_path}memberlist.$phpEx", 'mode=contact&amp;action=msnm&amp;u=' . $user_id) : '',
			'U_JABBER'		=> ($data['user_jabber'] && $auth->acl_get('u_sendim')) ? append_sid("{$phpbb_root_path}memberlist.$phpEx", 'mode=contact&amp;action=jabber&amp;u=' . $user_id) : '',
			'LOCATION'		=> ($data['user_from']) ? $data['user_from'] : '',

			'USER_ICQ'			=> $data['user_icq'],
			'USER_AIM'			=> $data['user_aim'],
			'USER_YIM'			=> $data['user_yim'],
			'USER_MSN'			=> $data['user_msnm'],
			'USER_JABBER'		=> $data['user_jabber'],
			'USER_JABBER_IMG'	=> ($data['user_jabber']) ? $user->img('icon_contact_jabber', $data['user_jabber']) : '',

			'L_VIEWING_PROFILE'	=> sprintf($user->lang['VIEWING_PROFILE'], $username),
		);
	}

	/**
	* View history
	*/
	function view_history(&$history, &$history_count, $limit = 0, $offset = 0, $limit_days = 0, $sort_by = 'k.karma_time DESC')
	{
		global $config, $db, $user, $auth, $phpEx, $phpbb_root_path, $phpbb_admin_path;

		$poster_id_list = $is_auth = $is_mod = array();

		$profile_url = (defined('IN_ADMIN')) ? append_sid("{$phpbb_admin_path}index.$phpEx", 'i=users&amp;mode=overview') : append_sid("{$phpbb_root_path}memberlist.$phpEx", 'mode=viewprofile');

		$sql = "SELECT k.*, u.username, u.username_clean, u.user_colour
			FROM " . KARMA_TABLE . " k, " . USERS_TABLE . " u
			WHERE u.user_id = k.user_id
				" . (($limit_days) ? "AND k.karma_time >= $limit_days" : '') . "
			ORDER BY $sort_by";
		$result = $db->sql_query_limit($sql, $limit, $offset);

		$i = 0;
		$history = array();
		while ($row = $db->sql_fetchrow($result))
		{
			if ($row['poster_id'])
			{
				$poster_id_list[] = $row['poster_id'];
			}

			$history[$i] = array(
				'id'				=> $row['karma_id'],

				'poster_id'			=> $row['poster_id'],
				'poster_username'		=> '',
				'poster_username_full'=> '',

				'user_id'			=> $row['user_id'],
				'username'			=> $row['username'],
				'username_full'		=> get_username_string('full', $row['user_id'], $row['username'], $row['user_colour'], false, $profile_url),

				'ip'				=> $row['poster_ip'],
				'time'				=> $row['karma_time'],

				'action'				=> $row['karma_action'] . (($this->config['power']) ? $row['karma_power'] : '1'),
			);

			// Parse comment
			if (!empty($row['comment_text']))
			{
					// make list items visible as such
					if ($row['bbcode_uid'])
					{
						$row['comment_text'] = str_replace('[*:' . $row['bbcode_uid'] . ']', '&sdot;&nbsp;', $row['comment_text']);
						// no BBCode in comment
						strip_bbcode($row['comment_text'], $row['bbcode_uid']);
					}

					// If within the admin panel we do not censor text out
					if (defined('IN_ADMIN'))
					{
						$history[$i]['comment'] = bbcode_nl2br($row['comment_text']);
					}
					else
					{
						$history[$i]['comment'] = bbcode_nl2br(censor_text($row['comment_text']));
					}
			}

			$i++;
		}
		$db->sql_freeresult($result);

		if (sizeof($poster_id_list))
		{
			$poster_id_list = array_unique($poster_id_list);
			$poster_names_list = array();

			$sql = 'SELECT user_id, username, user_colour
				FROM ' . USERS_TABLE . '
				WHERE ' . $db->sql_in_set('user_id', $poster_id_list);
			$result = $db->sql_query($sql);

			while ($row = $db->sql_fetchrow($result))
			{
				$poster_names_list[$row['user_id']] = $row;
			}
			$db->sql_freeresult($result);

			foreach ($history as $key => $row)
			{
				if (!isset($poster_names_list[$row['poster_id']]))
				{
					continue;
				}

				$history[$key]['poster_username'] = $poster_names_list[$row['poster_id']]['username'];
				$history[$key]['poster_username_full'] = get_username_string('full', $row['poster_id'], $poster_names_list[$row['poster_id']]['username'], $poster_names_list[$row['poster_id']]['user_colour'], false, $profile_url);
			}
		}

		$sql = 'SELECT COUNT(k.karma_id) AS total_entries
			FROM ' . KARMA_TABLE . " k
			WHERE k.karma_time >= $limit_days";
		$result = $db->sql_query($sql);
		$history_count = (int) $db->sql_fetchfield('total_entries');
		$db->sql_freeresult($result);

		return;
	}

	/**
	* Generate sort selection fields
	*/
	function gen_sort_selects(&$limit_days, &$sort_by_text, &$sort_days, &$sort_key, &$sort_dir, &$s_limit_days, &$s_sort_key, &$s_sort_dir, &$u_sort_param, $def_st = false, $def_sk = false, $def_sd = false)
	{
		global $user;

		$sort_dir_text = array('a' => $user->lang['ASCENDING'], 'd' => $user->lang['DESCENDING']);

		$sorts = array(
			'st'	=> array(
				'key'		=> 'sort_days',
				'default'	=> $def_st,
				'options'	=> $limit_days,
				'output'	=> &$s_limit_days,
			),

			'sk'	=> array(
				'key'		=> 'sort_key',
				'default'	=> $def_sk,
				'options'	=> $sort_by_text,
				'output'	=> &$s_sort_key,
			),

			'sd'	=> array(
				'key'		=> 'sort_dir',
				'default'	=> $def_sd,
				'options'	=> $sort_dir_text,
				'output'	=> &$s_sort_dir,
			),
		);
		$u_sort_param  = '';

		foreach ($sorts as $name => $sort_ary)
		{
			$key = $sort_ary['key'];
			$selected = $$sort_ary['key'];

			// Check if the key is selectable. If not, we reset to the default or first key found.
			// This ensures the values are always valid. We also set $sort_dir/sort_key/etc. to the
			// correct value, else the protection is void. ;)
			if (!isset($sort_ary['options'][$selected]))
			{
				if ($sort_ary['default'] !== false)
				{
					$selected = $$key = $sort_ary['default'];
				}
				else
				{
					@reset($sort_ary['options']);
					$selected = $$key = key($sort_ary['options']);
				}
			}

			$sort_ary['output'] = '<select name="' . $name . '" id="' . $name . '">';
			foreach ($sort_ary['options'] as $option => $text)
			{
				$sort_ary['output'] .= '<option value="' . $option . '"' . (($selected == $option) ? ' selected="selected"' : '') . '>' . $text . '</option>';
			}
			$sort_ary['output'] .= '</select>';

			$u_sort_param .= ($selected !== $sort_ary['default']) ? ((strlen($u_sort_param)) ? '&amp;' : '') . "{$name}={$selected}" : '';
		}

		return;
	}
	
	/**
	* Ban user by minimum karma;
	*/
	function ban_user($user_id = ANONYMOUS)
	{
		// Ban by minimum karma is disabled
		if (!$this->config['ban'])
		{
			return;
		}

		// We don't have user id
		if ($user_id == ANONYMOUS)
		{
			return;
		}

		global $db, $user;

		// Catch username
		$sql = 'SELECT username_clean, user_karma, user_karma_powered
			FROM ' . USERS_TABLE . '
			WHERE user_id = ' . $user_id;
		$result = $db->sql_query($sql);
		$row = $db->sql_fetchrow($result);
		$clean_name = $row['username_clean'];
		$karma = ($this->config['power']) ? $row['user_karma'] : $row['user_karma_powered'];
		$db->sql_freeresult($result);

		// User doesn't have enought karma, so don't ban
		if ($karma > $this->config['ban_karma'])
		{
			return;
		}

		// You can't ban yourself
		if ($clean_name == $user->data['username_clean'])
		{
			return;
		}

		// Create a list of founder...
		$sql = 'SELECT user_id, user_email, username_clean
			FROM ' . USERS_TABLE . '
			WHERE user_type = ' . USER_FOUNDER;
		$result = $db->sql_query($sql);

		while ($row = $db->sql_fetchrow($result))
		{
			$founder[$row['user_id']] = $row['user_email'];
			$founder_names[$row['user_id']] = $row['username_clean'];
		}
		$db->sql_freeresult($result);

		// You can't ban founder
		if (in_array($clean_name, $founder_names))
		{
			return;
		}

		// Okay, all tests passed and we'll ban user
		$mode = 'ban_userid'; // Ban by user id
		$ban = $clean_name; // Banned user
		$ban_len = 0; // Permanent ban
		$ben_len_other = ''; // This field user, if baning not permanent
		$ban_exclude = ''; // We don't have excludes from banning
		$ban_reason = $this->config['ban_reason']; // Ban reason showed at ACP/MCP
		$ban_give_reason = $this->config['ban_give_reason']; // Ban reason showed to user

		// Ban!
		user_ban($mode, $ban, $ban_len, $ban_len_other, $ban_exclude, $ban_reason, $ban_give_reason);
	}
}

$karmamod = NULL;

?>