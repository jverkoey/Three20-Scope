<?php
/**
*
* @package karmamod (acp)
* @version $Id: acp_karma.php,v 27 2009/09/23 22:00:55 m157y Exp $
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

/**
* @package acp
*/
class acp_karma
{
	var $u_action;
	var $new_config = array();

	function main($id, $mode)
	{
		global $db, $user, $auth, $template;
		global $config, $phpbb_root_path, $phpbb_admin_path, $phpEx;

		$error = $notify = array();
		$submit = (isset($_POST['submit'])) ? true : false;
		$action = request_var('action', '');

		switch ($mode)
		{
			case 'updater':
				$l_title = 'ACP_KARMA_VERSION_CHECK';
			break;

			case 'history':
				$l_title = 'ACP_KARMA_HISTORY';
			break;

			case 'stats':
				$l_title = 'ACP_KARMA_STATS';
			break;

			case 'karma':
			default:
				$l_title = 'ACP_KARMA_CONFIG';
			break;
		}

		$this->tpl_name = 'acp_karma';
		$this->page_title = $l_title;

		$template->assign_vars(array(
			'L_TITLE'			=> $user->lang[$l_title],
			'L_TITLE_EXPLAIN'	=> $user->lang[$l_title . '_EXPLAIN'],
			'U_ACTION'			=> $this->u_action)
		);

		switch ($mode)
		{
			case 'updater':
				global $karmamod;

				$user->add_lang('install');

				// Get current and latest version
				$errstr = '';
				$errno = 0;

				$info = get_remote_file('m157y.miranda.im', '/phpbb/updatecheck', 'karmamod.txt', $errstr, $errno);

				if ($info === false)
				{
					trigger_error($errstr, E_USER_WARNING);
				}

				$info = explode("\n", $info);
				$latest_version = trim($info[0]);

				$announcement_url = trim($info[1]);

				$up_to_date = (version_compare(str_replace('rc', 'RC', strtolower($karmamod->version)), str_replace('rc', 'RC', strtolower($latest_version)), '<')) ? false : true;

				// We need beta versions?
				$beta_version = false;
				$beta_announcement_url = false;
				if ($karmamod->config['updater_beta'])
				{
					// Get current and latest version
					$errstr = '';
					$errno = 0;
	
					$info = get_remote_file('m157y.miranda.im', '/phpbb/updatecheck', 'karmamod_beta.txt', $errstr, $errno);
	
					if ($info === false)
					{
						trigger_error($errstr, E_USER_WARNING);
					}
	
					$info = explode("\n", $info);
					$beta_version = trim($info[0]);
	
					$beta_announcement_url = trim($info[1]);
				}

				$template->assign_vars(array(
					'S_KARMA_UP_TO_DATE'		=> $up_to_date,
					'S_KARMA_UPDATER'				=> true,
					'S_KARMA_BETA'					=> ($beta_version) ? true : false,

					'KARMA_LATEST_VERSION'	=> $latest_version,
					'KARMA_CURRENT_VERSION'	=> $karmamod->version,
					'KARMA_BETA_VERSION'		=> $beta_version,

					'U_KARMA_BETA_ANNOUNCEMENT' => $beta_announcement_url,

					'KARMA_UPDATE_INSTRUCTIONS'	=> sprintf($user->lang['ACP_KARMA_VERSION_UPDATE_INSTRUCTIONS'], $announcement_url),
				));
			break;

			case 'history':
				global $karmamod;

				// Set up general vars
				$start		= request_var('start', 0);
				$deletemark = (!empty($_POST['delmarked'])) ? true : false;
				$deleteall	= (!empty($_POST['delall'])) ? true : false;
				$marked		= request_var('mark', array(0));

				// Sort keys
				$sort_days	= request_var('st', 0);
				$sort_key	= request_var('sk', 't');
				$sort_dir	= request_var('sd', 'd');

				// Delete entries if requested and able
				if (($deletemark || $deleteall) && $auth->acl_get('a_clearlogs'))
				{
					if (confirm_box(true))
					{
						$where_sql = '';

						if ($deletemark && sizeof($marked))
						{
							$sql_in = array();
							foreach ($marked as $mark)
							{
								$sql_in[] = $mark;
							}
							$where_sql = ' WHERE ' . $db->sql_in_set('karma_id', $sql_in);
							unset($sql_in);
						}

						if ($where_sql || $deleteall)
						{
              // Update users' karma values
							if ($where_sql)
							{
								$changes_users = array();
								$changes_posts = array();
								$changes_topics = array();
								$sql = 'SELECT *
									FROM ' . KARMA_TABLE .
									$where_sql;
								$result = $db->sql_query($sql);
								while ($row = $db->sql_fetchrow($result))
								{
									$user_id = $row['user_id'];
									$post_id = $row['post_id'];
									$topic_id = $row['topic_id'];

									// Create user_id's array, if not exists
									if (!isset($changes_users[$user_id]))
									{
										$changes_users[$user_id] = array('powered' => 0, 'simple' => 0);
									}
									// Create post_id's array, if not exists
									if (!isset($changes_posts[$post_id]))
									{
										$changes_posts[$post_id] = array('powered' => 0, 'simple' => 0, 'count' => 0);
									}
									// Create topic_id's array, if not exists
									if (!isset($changes_topics[$topic_id]))
									{
										$changes_topics[$topic_id] = array('powered' => 0, 'simple' => 0, 'count' => 0);
									}

									// Parse users
									$changes_users[$user_id]['powered'] = $changes_users[$user_id]['powered'] + ($row['karma_action'].$row['karma_power']);
									$changes_users[$user_id]['simple'] = $changes_users[$user_id]['simple'] + ($row['karma_action'].'1');

									// Parse posts
									$changes_posts[$post_id]['powered'] = $changes_posts[$post_id]['powered'] + ($row['karma_action'].$row['karma_power']);
									$changes_posts[$post_id]['simple'] = $changes_posts[$post_id]['simple'] + ($row['karma_action'].'1');
									$changes_posts[$post_id]['count']++;

									// Parse topics
									$changes_topics[$topic_id]['powered'] = $changes_topics[$topic_id]['powered'] + ($row['karma_action'].$row['karma_power']);
									$changes_topics[$topic_id]['simple'] = $changes_topics[$topic_id]['simple'] + ($row['karma_action'].'1');
									$changes_topics[$topic_id]['count']++;
								}
								$db->sql_freeresult($result);

								// Update users
								foreach ($changes_users as $user_id => $changes)
								{
									$sql = 'UPDATE ' . USERS_TABLE . '
										SET user_karma = user_karma - ' . $changes['simple'] . ', user_karma_powered = user_karma_powered - ' . $changes['powered'] . '
										WHERE user_id = ' . $user_id;
									$db->sql_query($sql);
								}

								// Update posts
								foreach ($changes_posts as $post_id => $changes)
								{
									$sql = 'UPDATE ' . POSTS_TABLE . '
										SET post_karma = post_karma - ' . $changes['simple'] . ', post_karma_powered = post_karma_powered - ' . $changes['powered'] . ', post_karma_count = post_karma_count - ' . $changes['count'] . ', post_karma_search = (post_karma / post_karma_count), post_karma_search_powered = (post_karma_powered / post_karma_count)
										WHERE post_id = ' . $post_id;
									$db->sql_query($sql);
								}

								// Update topics
								foreach ($changes_topics as $topic_id => $changes)
								{
									$sql = 'UPDATE ' . TOPICS_TABLE . '
										SET topic_karma = topic_karma - ' . $changes['simple'] . ', topic_karma_powered = topic_karma_powered - ' . $changes['powered'] . ', topic_karma_count = topic_karma_count - ' . $changes['count'] . ', topic_karma_search = ((topic_karma / topic_karma_count) / (topic_replies + 1)), topic_karma_search_powered = ((topic_karma_powered / topic_karma_count) / (topic_replies + 1))
										WHERE topic_id = ' . $topic_id;
									$db->sql_query($sql);
								}
							}
							else
							{
								// Update users
								$sql = 'UPDATE ' . USERS_TABLE . '
									SET user_karma = 0, user_karma_powered = 0';
								$db->sql_query($sql);

								// Update posts
								$sql = 'UPDATE ' . POSTS_TABLE . '
									SET post_karma = 0, post_karma_powered = 0, post_karma_count = 0, post_karma_search = 0, post_karma_search_powered = 0';
								$db->sql_query($sql);

								// Update topics
								$sql = 'UPDATE ' . TOPICS_TABLE . '
									SET topic_karma = 0, topic_karma_powered = 0, topic_karma_count = 0, topic_karma_search = 0, topic_karma_search_powered = 0';
								$db->sql_query($sql);
							}

							// Delete from karma table
							$sql = 'DELETE FROM ' . KARMA_TABLE .
								$where_sql;
							$db->sql_query($sql);

							add_log('admin', 'KARMA_LOG_CLEAR');
						}
					}
					else
					{
						confirm_box(false, $user->lang['CONFIRM_OPERATION'], build_hidden_fields(array(
							'start'		=> $start,
							'delmarked'	=> $deletemark,
							'delall'	=> $deleteall,
							'mark'		=> $marked,
							'st'		=> $sort_days,
							'sk'		=> $sort_key,
							'sd'		=> $sort_dir,
							'i'			=> $id,
							'mode'		=> $mode,
							'action'	=> $action))
						);
					}
				}

				// Sorting
				$limit_days = array(0 => $user->lang['ALL_ENTRIES'], 1 => $user->lang['1_DAY'], 7 => $user->lang['7_DAYS'], 14 => $user->lang['2_WEEKS'], 30 => $user->lang['1_MONTH'], 90 => $user->lang['3_MONTHS'], 180 => $user->lang['6_MONTHS'], 365 => $user->lang['1_YEAR']);
				$sort_by_text = array('u' => $user->lang['SORT_USERNAME'], 't' => $user->lang['SORT_DATE'], 'i' => $user->lang['SORT_IP'], 'a' => $user->lang['SORT_ACTION']);
				$sort_by_sql = array('u' => 'u.username_clean', 't' => 'k.karma_time', 'i' => 'k.poster_ip', 'a' => 'k.karma_action');

				$s_limit_days = $s_sort_key = $s_sort_dir = $u_sort_param = '';
				gen_sort_selects($limit_days, $sort_by_text, $sort_days, $sort_key, $sort_dir, $s_limit_days, $s_sort_key, $s_sort_dir, $u_sort_param);

				// Define where and sort sql for use in displaying logs
				$sql_where = ($sort_days) ? (time() - ($sort_days * 86400)) : 0;
				$sql_sort = $sort_by_sql[$sort_key] . ' ' . (($sort_dir == 'd') ? 'DESC' : 'ASC');

				// Grab history data
				$history_data = array();
				$history_count = 0;
				$karmamod->view_history($history_data, $history_count, $config['topics_per_page'], $start, $sql_where, $sql_sort);

				foreach ($history_data as $row)
				{
					$data = array();

					$template->assign_block_vars('history', array(
						'USERNAME'			=> $row['username_full'],
						'POSTER_USERNAME'	=> $row['poster_username_full'],

						'IP'				=> $row['ip'],
						'DATE'				=> $user->format_date($row['time']),
						'ACTION'			=> $row['action'],
						'COMMENT'			=> (!empty($row['comment'])) ? $row['comment'] : '',
						'ID'				=> $row['id'],
					));
				}

				$template->assign_vars(array(
					'U_ACTION'		=> $this->u_action,

					'S_ON_PAGE'		=> on_page($history_count, $config['topics_per_page'], $start),
					'PAGINATION'	=> generate_pagination($this->u_action . "&amp;$u_sort_param", $history_count, $config['topics_per_page'], $start, true),

					'S_LIMIT_DAYS'	=> $s_limit_days,
					'S_SORT_KEY'	=> $s_sort_key,
					'S_SORT_DIR'	=> $s_sort_dir,
					'S_CLEARHISTORY'	=> $auth->acl_get('a_clearlogs'),

					'S_ERROR'			=> (sizeof($error)) ? true : false,
					'ERROR_MSG'			=> implode('<br />', $error),

					'S_KARMA_HISTORY'	=> true
				));
			break;

			case 'stats':
				$template->assign_vars(array(
					'S_ERROR'			=> (sizeof($error)) ? true : false,
					'ERROR_MSG'			=> implode('<br />', $error),

					'S_KARMA_STATS'		=> true
				));
			break;

			case 'karma':
			default:
				$display_vars = array(
					'legend1'					=> 'ACP_KARMA_SETTINGS',
					'karma_enabled'				=> array('lang' => 'ACP_KARMA_ENABLED',				'validate' => 'bool',	'type' => 'radio:yes_no',	'explain' => true),
					'karma_enabled_ucp'			=> array('lang' => 'ACP_KARMA_ENABLED_UCP',			'validate' => 'bool',	'type' => 'radio:yes_no',	'explain' => true),
					'karma_zebra'				=> array('lang' => 'ACP_KARMA_ZEBRA',				'validate' => 'bool', 'type' => 'radio:yes_no', 'explain' => false),
					'karma_anonym_increase'	=> array('lang' => 'ACP_KARMA_ANONYM_INCREASE',			'validate' => 'bool', 'type' => 'radio:yes_no', 'explain' => true),
					'karma_anonym_decrease'	=> array('lang' => 'ACP_KARMA_ANONYM_DECREASE',			'validate' => 'bool', 'type' => 'radio:yes_no', 'explain' => true),
					'karma_power'				=> array('lang' => 'ACP_KARMA_POWER',				'validate' => 'bool',	'type' => 'radio:yes_no',	'explain' => false),
					'karma_power_max'			=> array('lang' => 'ACP_KARMA_POWER_MAX',			'validate' => 'int',	'type' => 'text:3:4',		'explain' => false),
					'karma_time'				=> array('lang' => 'ACP_KARMA_TIME',				'validate' => 'string',	'type' => 'text:3:4',		'explain' => true,	'append' => ' ' . $user->lang['ACP_KARMA_APPEND_TIME']),
					'karma_posts'				=> array('lang' => 'ACP_KARMA_POSTS',				'validate' => 'int',	'type' => 'text:3:4',		'explain' => true,	'append' => ' ' . $user->lang['ACP_KARMA_APPEND_POSTS']),
					'karma_minimum'			=> array('lang' => 'ACP_KARMA_MINIMUM',			'validate' => 'int',	'type' => 'text:3:4',		'explain' => true),
					'karma_per_day'				=> array('lang'	=> 'ACP_KARMA_PER_DAY',				'validate' => 'int',	'type' => 'text:3:4',		'explain' => true,	'append' => ' ' . $user->lang['ACP_KARMA_APPEND_TIMES']),
					'karma_comments'			=> array('lang' => 'ACP_KARMA_COMMENTS',			'validate' => 'bool',	'type' => 'radio:yes_no',	'explain' => true),
					'karma_comments_reqd'		=> array('lang' => 'ACP_KARMA_COMMENTS_REQD',		'validate' => 'bool',	'type' => 'radio:yes_no',	'explain' => true),
					'karma_viewprofile'			=> array('lang' => 'ACP_KARMA_VIEWPROFILE',			'validate' => 'bool',	'type' => 'radio:yes_no',	'explain' => true),
					'karma_comments_per_page'	=> array('lang' => 'ACP_KARMA_COMMENTS_PER_PAGE',	'validate' => 'int',	'type' => 'text:3:4',		'explain' => false,	'append' => ' ' . $user->lang['ACP_KARMA_APPEND_COMMENTS']),
					'karma_notify_pm'			=> array('lang' => 'ACP_KARMA_NOTIFY_PM',			'validate' => 'bool',	'type' => 'radio:yes_no',	'explain' => true),
					'karma_notify_email'		=> array('lang' => 'ACP_KARMA_NOTIFY_EMAIL',		'validate' => 'bool',	'type' => 'radio:yes_no',	'explain' => true),
					'karma_notify_jabber'		=> array('lang' => 'ACP_KARMA_NOTIFY_JABBER',		'validate' => 'bool',	'type' => 'radio:yes_no',	'explain' => true),
//					'karma_drafts'				=> array('lang' => 'ACP_KARMA_DRAFTS',				'validate' => 'bool',	'type' => 'radio:yes_no',	'explain' => true),
					'karma_icons'				=> array('lang' => 'ACP_KARMA_ICONS',				'validate' => 'bool',	'type' => 'radio:yes_no',	'explain' => true),
					'karma_toplist'				=> array('lang' => 'ACP_KARMA_TOPLIST',				'validate' => 'bool',	'type' => 'radio:yes_no',	'explain' => true),
					'karma_toplist_users'		=> array('lang' => 'ACP_KARMA_TOPLIST_USERS',		'validate' => 'int',	'type' => 'text:3:4',		'explain' => true),
					'karma_ban'				=> array('lang' => 'ACP_KARMA_BAN',				'validate' => 'bool',	'type' => 'radio:yes_no',	'explain' => true),
					'karma_ban_value'		=> array('lang' => 'ACP_KARMA_BAN_VALUE',		'validate' => 'int',	'type' => 'text:3:4',		'explain' => true),
					'karma_ban_reason'		=> array('lang' => 'ACP_KARMA_BAN_REASON',		'validate' => 'string',	'type' => 'text:40:255',		'explain' => true),
					'karma_ban_give_reason'		=> array('lang' => 'ACP_KARMA_BAN_REASON_GIVE',		'validate' => 'string',	'type' => 'text:40:255',		'explain' => true),
					'karma_updater_beta'	=> array('lang' => 'ACP_KARMA_UPDATER_BETA',	'validate' => 'bool',	'type' => 'radio:yes_no',	'explain' => false),
				);

				$this->new_config = $config;
				$cfg_array = (isset($_REQUEST['config'])) ? utf8_normalize_nfc(request_var('config', array('' => ''), true)) : $this->new_config;
				$error = array();

				// We validate the complete config if whished
				validate_config_vars($display_vars, $cfg_array, $error);

				// Do not write values if there is an error
				if (sizeof($error))
				{
					$submit = false;
				}

				// We go through the display_vars to make sure no one is trying to set variables he/she is not allowed to...
				foreach ($display_vars as $config_name => $null)
				{
					if (!isset($cfg_array[$config_name]) || strpos($config_name, 'legend') !== false)
					{
						continue;
					}

					$this->new_config[$config_name] = $config_value = $cfg_array[$config_name];

					if ($submit)
					{
						set_config($config_name, $config_value);
					}
				}

				if ($submit)
				{
					add_log('admin', 'KARMA_LOG_CONFIG');

					trigger_error($user->lang['ACP_KARMA_CONFIG_UPDATED'] . adm_back_link($this->u_action));
				}

				$template->assign_vars(array(
					'S_ERROR'			=> (sizeof($error)) ? true : false,
					'ERROR_MSG'			=> implode('<br />', $error),

					'S_KARMA_CONFIG'	=> true
				));

				// Output relevant page
				foreach ($display_vars as $config_key => $vars)
				{
					if (!is_array($vars) && strpos($config_key, 'legend') === false)
					{
						continue;
					}

					if (strpos($config_key, 'legend') !== false)
					{
						$template->assign_block_vars('options', array(
							'S_LEGEND'		=> true,
							'LEGEND'		=> (isset($user->lang[$vars])) ? $user->lang[$vars] : $vars
						));

						continue;
					}

					$type = explode(':', $vars['type']);

					$l_explain = '';
					if ($vars['explain'] && isset($vars['lang_explain']))
					{
						$l_explain = (isset($user->lang[$vars['lang_explain']])) ? $user->lang[$vars['lang_explain']] : $vars['lang_explain'];
					}
					else if ($vars['explain'])
					{
						$l_explain = (isset($user->lang[$vars['lang'] . '_EXPLAIN'])) ? $user->lang[$vars['lang'] . '_EXPLAIN'] : '';
					}

					$template->assign_block_vars('options', array(
						'KEY'			=> $config_key,
						'TITLE'			=> (isset($user->lang[$vars['lang']])) ? $user->lang[$vars['lang']] : $vars['lang'],
						'S_EXPLAIN'		=> $vars['explain'],
						'TITLE_EXPLAIN'	=> $l_explain,
						'CONTENT'		=> build_cfg_template($type, $config_key, $this->new_config, $config_key, $vars),
					));
				
					unset($display_vars[$config_key]);
				}
			break;
		}
	}
}

?>