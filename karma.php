<?php
/** 
*
* @package karmamod
* @version $Id: karma.php,v 42 2009/10/05 00:25:12 m157y Exp $
* @copyright (c) 2007, 2009 David Lawson, m157y, A_Jelly_Doughnut
* @license http://opensource.org/licenses/gpl-license.php GNU Public License 
*
*/

/**
* @ignore
*/
define('IN_PHPBB', true);
$phpbb_root_path = './';
$phpEx = substr(strrchr(__FILE__, '.'), 1);
include($phpbb_root_path . 'common.' . $phpEx);

// Start session management
$user->session_begin();
$auth->acl($user->data);
$user->setup('posting');

// Initial var setup
$forum_id	= request_var('f', 0);
$topic_id	= request_var('t', 0);
$post_id	= request_var('p', 0);
$user_id	= request_var('u', ANONYMOUS);
$username	= request_var('un', '', true);

$mode		= request_var('mode', '');
$action		= request_var('action', '');
$start		= request_var('start', 0);

$submit		= (isset($_POST['post'])) ? true : false;
$preview	= (isset($_POST['preview'])) ? true : false;
$cancel		= (isset($_POST['cancel']) && !isset($_POST['save'])) ? true : false;

$default_sort_days	= (!empty($user->data['user_karma_comments_show_days'])) ? $user->data['user_karma_comments_show_days'] : 0;
$default_sort_key	= (!empty($user->data['user_karma_comments_sortby_type'])) ? $user->data['user_karma_comments_sortby_type'] : 't';
$default_sort_dir	= (!empty($user->data['user_karma_comments_sortby_dir'])) ? $user->data['user_karma_comments_sortby_dir'] : 'd';

$sort_days	= request_var('st', $default_sort_days);
$sort_key	= request_var('sk', $default_sort_key);
$sort_dir	= request_var('sd', $default_sort_dir);

// Check whether the mod is enabled
if (!$karmamod->config['enabled'])
{
	trigger_error('KARMA_MOD_DISABLED');
}

// Check our mode...
if (!in_array($mode, array('', 'viewcomments', 'update')))
{
	trigger_error('KARMA_NO_KARMA_MODE');
}

if (!$user->data['is_registered'])
{
	// TODO: Add better lang string here
	trigger_error('NOT_AUTHORISED');
}

// Do we have a user id or username?
if ($user_id == ANONYMOUS && !$username)
{
	trigger_error('NO_USER');
}

// Do we have a registered user?
$sql = 'SELECT *
	FROM ' . USERS_TABLE . '
	WHERE ' . (($username) ? "username_clean = '" . $db->sql_escape(utf8_clean_string($username)) . "'" : "user_id = $user_id") . '
		AND user_id <> ' . ANONYMOUS . '
		AND (user_type = ' . USER_NORMAL . '
			OR user_type = ' . USER_FOUNDER . ')';
$result = $db->sql_query($sql);
$member = $db->sql_fetchrow($result);
$db->sql_freeresult($result);

if (!$member)
{
	trigger_error('NO_USER');
}

$user_id	= (int) $member['user_id'];
$username	= (string) $member['username'];

// We need to check if the module 'zebra' is accessible
$zebra_enabled = false;

if ($user->data['user_id'] != $user_id)
{
	include_once($phpbb_root_path . 'includes/functions_module.' . $phpEx);
	$module = new p_master();
	$module->list_modules('ucp');
	$module->set_active('zebra');

	$zebra_enabled = ($module->active_module === false) ? false : true;

	unset($module);
}

switch ($mode)
{
	case 'viewcomments':
// TODO for viewcomments:
// comments approve status
// mcp 'details' support
		// Check enabled comments or not
		if (!$karmamod->config['comments'] || !$karmamod->config['viewprofile'])
		{
			trigger_error('KARMA_COMMENTS_DISABLED');
		}

		// Block all users, if karma can be viewed only by user
		if ($member['user_karma_comments_self'] && $user->data['user_id'] != $user_id && $karmamod->user_level != 'admin')
		{
			trigger_error('KARMA_COMMENTS_SELF_ONLY');
		}

		// User can't see karma
		if (!$auth->acl_get('u_karma_view') && $user->data['user_id'] != $user_id && $karmamod->user_level != 'admin')
		{
			trigger_error('KARMA_COMMENTS_SELF_ONLY');
		}

		// User can't see karma at this forum
		if (!empty($forum_id) && !$auth->acl_get('f_karma_view', $forum_id) && $user->data['user_id'] != $user_id && $karmamod->user_level != 'admin')
		{
			trigger_error('KARMA_COMMENTS_SELF_ONLY');
		}

		include($phpbb_root_path . 'includes/functions_display.' . $phpEx);
		$user->add_lang(array('memberlist', 'groups'));

		$page_title = $user->lang['KARMA_VIEW_COMMENTS'];
		$template_html = 'karma_view.html';

		// a_user admins and founder are able to view inactive users and bots to be able to manage them more easily
		// Normal users are able to see at least users having only changed their profile settings but not yet reactivated.
		if (!$auth->acl_get('a_user') && $user->data['user_type'] != USER_FOUNDER)
		{
			if ($member['user_type'] == USER_IGNORE)
			{
				trigger_error('NO_USER');
			}
			else if ($member['user_type'] == USER_INACTIVE && $member['user_inactive_reason'] != INACTIVE_PROFILE)
			{
				trigger_error('NO_USER');
			}
		}

		// Do the SQL thang
		$sql = 'SELECT g.group_id, g.group_name, g.group_type
			FROM ' . GROUPS_TABLE . ' g, ' . USER_GROUP_TABLE . " ug
			WHERE ug.user_id = $user_id
				AND g.group_id = ug.group_id" . ((!$auth->acl_gets('a_group', 'a_groupadd', 'a_groupdel')) ? ' AND g.group_type <> ' . GROUP_HIDDEN : '') . '
				AND ug.user_pending = 0
			ORDER BY g.group_type, g.group_name';
		$result = $db->sql_query($sql);

		$group_options = '';
		while ($row = $db->sql_fetchrow($result))
		{
			$group_options .= '<option value="' . $row['group_id'] . '"' . (($row['group_id'] == $member['group_id']) ? ' selected="selected"' : '') . '>' . (($row['group_type'] == GROUP_SPECIAL) ? $user->lang['G_' . $row['group_name']] : $row['group_name']) . '</option>';
		}
		$db->sql_freeresult($result);

		// What colour is the zebra
		$sql = 'SELECT friend, foe
			FROM ' . ZEBRA_TABLE . "
			WHERE zebra_id = $user_id
				AND user_id = {$user->data['user_id']}";

		$result = $db->sql_query($sql);
		$row = $db->sql_fetchrow($result);
		$foe = ($row['foe']) ? true : false;
		$friend = ($row['friend']) ? true : false;
		$db->sql_freeresult($result);

		if ($config['load_onlinetrack'])
		{
			$sql = 'SELECT MAX(session_time) AS session_time, MIN(session_viewonline) AS session_viewonline
				FROM ' . SESSIONS_TABLE . "
				WHERE session_user_id = $user_id";
			$result = $db->sql_query($sql);
			$row = $db->sql_fetchrow($result);
			$db->sql_freeresult($result);

			$member['session_time'] = (isset($row['session_time'])) ? $row['session_time'] : 0;
			$member['session_viewonline'] = (isset($row['session_viewonline'])) ? $row['session_viewonline'] :	0;
			unset($row);
		}

		if ($config['load_user_activity'])
		{
			display_user_activity($member);
		}

		// Do the relevant calculations
		$memberdays = max(1, round((time() - $member['user_regdate']) / 86400));
		$posts_per_day = $member['user_posts'] / $memberdays;
		$percentage = ($config['num_posts']) ? min(100, ($member['user_posts'] / $config['num_posts']) * 100) : 0;


		if ($member['user_sig'])
		{
			$member['user_sig'] = censor_text($member['user_sig']);
			$member['user_sig'] = bbcode_nl2br($member['user_sig']);

			if ($member['user_sig_bbcode_bitfield'])
			{
				include_once($phpbb_root_path . 'includes/bbcode.' . $phpEx);
				$bbcode = new bbcode();
				$bbcode->bbcode_second_pass($member['user_sig'], $member['user_sig_bbcode_uid'], $member['user_sig_bbcode_bitfield']);
			}

			$member['user_sig'] = smiley_text($member['user_sig']);
		}

		$poster_avatar = get_user_avatar($member['user_avatar'], $member['user_avatar_type'], $member['user_avatar_width'], $member['user_avatar_height']);

		$template->assign_vars($karmamod->show_profile($member));

		// Custom Profile Fields
		$profile_fields = array();
		if ($config['load_cpf_viewprofile'])
		{
			include_once($phpbb_root_path . 'includes/functions_profile_fields.' . $phpEx);
			$cp = new custom_profile();
			$profile_fields = $cp->generate_profile_fields_template('grab', $user_id);
			$profile_fields = (isset($profile_fields[$user_id])) ? $cp->generate_profile_fields_template('show', false, $profile_fields[$user_id]) : array();
		}

		$template->assign_vars(array(
			'POSTS_DAY'		=> sprintf($user->lang['POST_DAY'], $posts_per_day),
			'POSTS_PCT'		=> sprintf($user->lang['POST_PCT'], $percentage),

			'OCCUPATION'	=> (!empty($member['user_occ'])) ? censor_text($member['user_occ']) : '',
			'INTERESTS'		=> (!empty($member['user_interests'])) ? censor_text($member['user_interests']) : '',
			'SIGNATURE'		=> $member['user_sig'],

			'AVATAR_IMG'	=> $poster_avatar,
			'PM_IMG'		=> $user->img('icon_contact_pm', $user->lang['SEND_PRIVATE_MESSAGE']),
			'EMAIL_IMG'		=> $user->img('icon_contact_email', $user->lang['EMAIL']),
			'WWW_IMG'		=> $user->img('icon_contact_www', $user->lang['WWW']),
			'ICQ_IMG'		=> $user->img('icon_contact_icq', $user->lang['ICQ']),
			'AIM_IMG'		=> $user->img('icon_contact_aim', $user->lang['AIM']),
			'MSN_IMG'		=> $user->img('icon_contact_msnm', $user->lang['MSNM']),
			'YIM_IMG'		=> $user->img('icon_contact_yahoo', $user->lang['YIM']),
			'JABBER_IMG'	=> $user->img('icon_contact_jabber', $user->lang['JABBER']),
			'SEARCH_IMG'	=> $user->img('icon_user_search', $user->lang['SEARCH']),

			'S_PROFILE_ACTION'	=> append_sid("{$phpbb_root_path}memberlist.$phpEx", 'mode=group'),
			'S_GROUP_OPTIONS'	=> $group_options,
			'S_CUSTOM_FIELDS'	=> (isset($profile_fields['row']) && sizeof($profile_fields['row'])) ? true : false,

			'U_VIEWPROFILE'			=> append_sid("{$phpbb_root_path}memberlist.$phpEx", 'mode=viewprofile&amp;u=' . $user_id),
			'U_USER_ADMIN'			=> ($auth->acl_get('a_user')) ? append_sid("{$phpbb_root_path}adm/index.$phpEx", 'i=users&amp;mode=overview&amp;u=' . $user_id, true, $user->session_id) : '',
			'U_SWITCH_PERMISSIONS'	=> ($auth->acl_get('a_switchperm') && $user->data['user_id'] != $user_id) ? append_sid("{$phpbb_root_path}ucp.$phpEx", "mode=switch_perm&amp;u={$user_id}") : '',

			'S_ZEBRA'			=> ($user->data['user_id'] != $user_id && $zebra_enabled) ? true : false,
			'U_ADD_FRIEND'		=> (!$friend) ? append_sid("{$phpbb_root_path}ucp.$phpEx", 'i=zebra&amp;add=' . urlencode(htmlspecialchars_decode($member['username']))) : '',
			'U_ADD_FOE'			=> (!$foe) ? append_sid("{$phpbb_root_path}ucp.$phpEx", 'i=zebra&amp;mode=foes&amp;add=' . urlencode(htmlspecialchars_decode($member['username']))) : '',
			'U_REMOVE_FRIEND'	=> ($friend) ? append_sid("{$phpbb_root_path}ucp.$phpEx", 'i=zebra&amp;remove=1&amp;usernames[]=' . $user_id) : '',
			'U_REMOVE_FOE'		=> ($foe) ? append_sid("{$phpbb_root_path}ucp.$phpEx", 'i=zebra&amp;remove=1&amp;mode=foes&amp;usernames[]=' . $user_id) : '',
		));

		if (!empty($profile_fields['row']))
		{
			$template->assign_vars($profile_fields['row']);
		}

		if (!empty($profile_fields['blockrow']))
		{
			foreach ($profile_fields['blockrow'] as $field_data)
			{
				$template->assign_block_vars('custom_fields', $field_data);
			}
		}

		// Inactive reason/account?
		if ($member['user_type'] == USER_INACTIVE)
		{
			$user->add_lang('acp/common');

			$inactive_reason = $user->lang['INACTIVE_REASON_UNKNOWN'];

			switch ($member['user_inactive_reason'])
			{
				case INACTIVE_REGISTER:
					$inactive_reason = $user->lang['INACTIVE_REASON_REGISTER'];
				break;

				case INACTIVE_PROFILE:
					$inactive_reason = $user->lang['INACTIVE_REASON_PROFILE'];
				break;

				case INACTIVE_MANUAL:
					$inactive_reason = $user->lang['INACTIVE_REASON_MANUAL'];
				break;

				case INACTIVE_REMIND:
					$inactive_reason = $user->lang['INACTIVE_REASON_REMIND'];
				break;
			}

			$template->assign_vars(array(
				'S_USER_INACTIVE'		=> true,
				'USER_INACTIVE_REASON'	=> $inactive_reason)
			);
		}

		// Comment ordering options
		$limit_days = array(0 => $user->lang['KARMA_ALL_COMMENTS'], 1 => $user->lang['1_DAY'], 7 => $user->lang['7_DAYS'], 14 => $user->lang['2_WEEKS'], 30 => $user->lang['1_MONTH'], 90 => $user->lang['3_MONTHS'], 180 => $user->lang['6_MONTHS'], 365 => $user->lang['1_YEAR']);

		$sort_by_text = array('a' => $user->lang['AUTHOR'], 't' => $user->lang['KARMA_SORT_TIME'], 'p' => $user->lang['KARMA_SORT_POST'], 'o' => $user->lang['KARMA_SORT_TOPIC'], 'f' => $user->lang['KARMA_SORT_FORUM']);
		$sort_by_sql = array('a' => 'u.username_clean', 't' => 'k.karma_time', 'p' => 'k.post_id', 'o' => 'k.topic_id', 'f' => 'k.forum_id');

		$s_limit_days = $s_sort_key = $s_sort_dir = $u_sort_param = '';

		gen_sort_selects($limit_days, $sort_by_text, $sort_days, $sort_key, $sort_dir, $s_limit_days, $s_sort_key, $s_sort_dir, $u_sort_param, $default_sort_days, $default_sort_key, $default_sort_dir);

		// Send sort selects to template
		$template->assign_vars(array(
			'S_SELECT_SORT_DIR' 	=> $s_sort_dir,
			'S_SELECT_SORT_KEY' 	=> $s_sort_key,
			'S_SELECT_SORT_DAYS' 	=> $s_limit_days,
		));

		// Obtain correct post count and ordering SQL if user has
		// requested anything different
		if ($sort_days)
		{
			$min_comment_time = time() - ($sort_days * 86400);
			$limit_comments_time = "AND k.karma_time >= $min_comment_time ";
		}
		else
		{
			$limit_comments_time = '';
		}

// TODO:
// comments approve status
		// Obtain correct comments count
		$sql = 'SELECT COUNT(*) AS num_comments
			FROM ' . KARMA_TABLE . " k
			WHERE k.user_id = $user_id
				$limit_comments_time";
		$result = $db->sql_query($sql);
		$total_comments = (int) $db->sql_fetchfield('num_comments');
		$db->sql_freeresult($result);

		if ($total_comments > 0)
		{
			// Load bbcode class
			include_once($phpbb_root_path . 'includes/bbcode.' . $phpEx);

			// Grab icons
			$icons = $cache->obtain_icons();

			// If we've got a hightlight set pass it on to pagination.
			$pagination = generate_pagination(append_sid("{$phpbb_root_path}karma.$phpEx", "mode=viewcomments&amp;u=$user_id" . ((strlen($u_sort_param)) ? "&amp;$u_sort_param" : '')), $total_comments, $karmamod->config['comments_per_page'], $start);

			// Send pagination to template
			$template->assign_vars(array(
				'PAGINATION' 	=> $pagination,
				'PAGE_NUMBER' 	=> on_page($total_comments, $karmamod->config['comments_per_page'], $start),
				'TOTAL_COMMENTS'=> ($total_comments == 1) ? $user->lang['KARMA_VIEW_USER_COMMENT'] : sprintf($user->lang['KARMA_VIEW_USER_COMMENTS'], $total_comments))
			);

// TODO:
// comments approve status
			$sql = $db->sql_build_query('SELECT', array(
				'SELECT'	=> 'u.username, u.user_id, u.user_colour, k.*',

				'FROM'		=> array(
					USERS_TABLE		=> 'u',
					KARMA_TABLE		=> 'k',
				),

				'WHERE'		=> "k.user_id = $user_id
					AND u.user_id = k.poster_id
					$limit_comments_time",
				'ORDER_BY'	=> $sort_by_sql[$sort_key] . ' ' . (($sort_dir == 'd') ? 'DESC' : 'ASC'),
				
			));
			$result = $db->sql_query_limit($sql, $karmamod->config['comments_per_page'], $start);

			while ($row = $db->sql_fetchrow($result))
			{
				$power = '';
				if ($karmamod->config['power_show'] || $karmamod->user_level == 'admin')
				{
					$power = $row['karma_action'] . $row['karma_power'];
				}

				$poster_id	= $row['poster_id'];
				$comment	= censor_text($row['comment_text']);

				// Parse bbcode
				if ($row['bbcode_bitfield'])
				{
					$bbcode = new bbcode(base64_encode($row['bbcode_bitfield']));
					$bbcode->bbcode_second_pass($comment, $row['bbcode_uid'], $row['bbcode_bitfield']);
				}

				$comment = bbcode_nl2br($comment);
				$comment = smiley_text($comment, !$config['allow_smilies']);

				if ($karmamod->user_level != 'admin' && (($karmamod->config['anonym_increase'] && $row['karma_action'] == '+') || ($karmamod->config['anonym_decrease'] && $row['karma_action'] == '-')))
				{
					$author_full = $user->lang['KARMA_NOTIFY_HIDDEN_SENDER'];
					$author_colour = $user->lang['KARMA_NOTIFY_HIDDEN_SENDER'];
					$author = $user->lang['KARMA_NOTIFY_HIDDEN_SENDER'];
					$u_author = '';
				}
				else
				{
					$author_full = get_username_string('full', $poster_id, $row['username'], $row['user_colour']);
					$author_colour = get_username_string('colour', $poster_id, $row['username'], $row['user_colour']);
					$author = get_username_string('username', $poster_id, $row['username'], $row['user_colour']);
					$u_author = get_username_string('profile', $poster_id, $row['username'], $row['user_colour']);
				}

				$template->assign_block_vars('karma_comments_row', array(
					'POST_AUTHOR_FULL'		=> $author_full,
					'POST_AUTHOR_COLOUR'	=> $author_colour,
					'POST_AUTHOR'			=> $author,
					'U_POST_AUTHOR'			=> $u_author,

					'ICON_IMG'			=> (!empty($row['icon_id'])) ? $icons[$row['icon_id']]['img'] : '',
					'ICON_IMG_WIDTH'	=> (!empty($row['icon_id'])) ? $icons[$row['icon_id']]['width'] : '',
					'ICON_IMG_HEIGHT'	=> (!empty($row['icon_id'])) ? $icons[$row['icon_id']]['height'] : '',
					'KARMA_POWER'		=> $power,
					'MINI_POST_IMG'		=> $user->img('icon_post_target', $user->lang['KARMA_COMMENT']),
					'POST_DATE'			=> $user->format_date($row['karma_time']),
					'COMMENT'			=> $comment,
					'COMMENT_ID'		=> $row['karma_id'],

					'U_MINI_POST'		=> ($row['post_id']) ? append_sid("{$phpbb_root_path}viewtopic.$phpEx", 'p=' . $row['post_id']) . '#p' . $row['post_id'] : '',)
	// TODO:
	// mcp 'details' support
	//				'U_MCP_DETAILS'		=> ($auth->acl_get('m_info', $forum_id)) ? append_sid("{$phpbb_root_path}mcp.$phpEx", 'i=main&amp;mode=post_details&amp;f=' . $forum_id . '&amp;p=' . $row['post_id'], true, $user->session_id) : '',)
				);
			}
		}
	break;

	case 'update':
		// User disabled karma?
		if ($karmamod->config['enabled_ucp'] && !$member['user_karma_enable'])
		{
			trigger_error('KARMA_NO_CURRENT_USER');
		}
		unset($member);

		// Disabled karma by phpbb's permissions system for user
		if (!$auth->acl_get('u_karma_can'))
		{
			trigger_error('KARMA_NO_CURRENT_USER');
		}

		// Disabled karma by phpbb's permissions system by forum
		if (!empty($forum_id) && !$auth->acl_get('f_karma_can', $forum_id))
		{
			trigger_error('KARMA_NO_CURRENT_USER');
		}

		// The selected user is not the user logged in?
		if ($user->data['user_id'] == $user_id)
		{
			trigger_error('KARMA_NO_SELF');
		}

		// Karmas per day checker
		if ($karmamod->config['per_day'])
		{
			$sql = 'SELECT COUNT(*) as karmas_per_day
				FROM ' . KARMA_TABLE . '
				WHERE poster_id = ' . $user->data['user_id'] . '
					AND karma_time > ' . (time() - 86400);
			$result = $db->sql_query($sql);
			$row = $db->sql_fetchrow($result);
			if ($row['karmas_per_day'] >= $karmamod->config['per_day'])
			{
				$message_lang = ($karmamod->config['per_day'] > 1) ? $user->lang['KARMA_LIMITED_PER_DAY_TIMES'] : $user->lang['KARMA_LIMITED_PER_DAY_TIME'];
				$message = sprintf($message_lang, $karmamod->config['per_day']);
				trigger_error($message);
			}
			unset($row);
		}

		// Minimum post limit
		if ($user->data['user_posts'] < $karmamod->config['posts'])
		{
			trigger_error('KARMA_CAN_NOT_POSTS');
		}

		// Enough karma?
		if ($karmamod->user_level != 'admin')
		{
			if ($karmamod->config['power'] && ($user->data['user_karma_powered'] < $karmamod->config['minimum']))
			{
				trigger_error('KARMA_CAN_NOT_MINIMUM');
			}
			else if (!$karmamod->config['power'] && ($user->data['user_karma'] < $karmamod->config['minimum']))
			{
				trigger_error('KARMA_CAN_NOT_MINIMUM');
			}
		}

		if ($post_id)
		{
			// See if user has already karmaed this post
			$sql = 'SELECT post_id
				FROM ' . KARMA_TABLE . '
				WHERE post_id = ' . $post_id . '
					AND poster_id = ' . $user->data['user_id'];
			$result = $db->sql_query($sql);
			if ($row = $db->sql_fetchrow($result))
			{
				trigger_error('KARMA_ALREADY_KARMAED_POST');
			}
		}

		// Is this user friend/foe?
		if (!$karmamod->config['zebra'] && $zebra_enabled && $karmamod->user_level != 'admin')
		{
			$sql = 'SELECT *
				FROM ' . ZEBRA_TABLE . '
				WHERE user_id = ' . (int) $user->data['user_id'] . "
					AND zebra_id = $user_id";
			$result = $db->sql_query($sql);
			if ($row = $db->sql_fetchrow($result))
			{
				trigger_error('KARMA_CAN_NOT_KARMA_ZEBRA');
			}
		}

		// Select last karma time for this user
		$sql = 'SELECT karma_time
			FROM ' . KARMA_TABLE . '
			WHERE user_id = ' . $user_id . '
				AND poster_id = ' . $user->data['user_id'] . '
			ORDER BY karma_time DESC';
		$result = $db->sql_query_limit($sql, 1);
		if ($row = $db->sql_fetchrow($result))
		{
			if (($karmamod->current_time - $row['karma_time']) < ($karmamod->config['time'] * 3600))
			{
				trigger_error('KARMA_CAN_NOT_YET');
			}
		}

		$page_title = ($action == 'increase') ? $user->lang['KARMA_INCREASE'] : $user->lang['KARMA_DECREASE'];
		$template_html = 'karma_body.html';

		// Request variables
		$comment	= ($karmamod->config['comments']) ? utf8_normalize_nfc(request_var('message', '', true)) : '';
		$icon_id	= request_var('icon', 0);
		$karma_power = request_var('power', 0);

		// Find current user's (karma giver's) "karma power"
		// this is an arbitrary kind of calculation.  Long time members, or members of
		// staff award or subtract up to five points
		if ($auth->acl_getf_global('m_') || $karmamod->user_level == 'admin')
		{
			$max_karma_power = $karmamod->config['power_max'];
		}
		else
		{
			$days_registered = (int) max(2, round(($karmamod->current_time - $user->data['user_regdate']) / 86400));

			$max_karma_power = round($user->data['user_karma_powered'] / log($days_registered));

			if ($max_karma_power > $karmamod->config['power_max'])
			{
				$max_karma_power = $karmamod->config['power_max'];
			}
			else if ($max_karma_power < 1)
			{
				$max_karma_power = 1;
			}
		}

		// Karma power can't be more then max value and at least 1
		if ($karma_power > $max_karma_power || !$karmamod->config['power'])
		{
			$karma_power = $max_karma_power;
		}
		else if ($karma_power < 1)
		{
			$karma_power = 1;
		}

		// Karma comments disable, we'll always karma with max power
		if (!$karmamod->config['comments'])
		{
			$karma_power = $max_karma_power;
		}

		// Include posting functions
		include($phpbb_root_path . 'includes/functions_posting.' . $phpEx);
		include($phpbb_root_path . 'includes/functions_display.' . $phpEx);
		include($phpbb_root_path . 'includes/message_parser.' . $phpEx);

		$message_parser = new parse_message();
		$error = array();

		$s_icons = false;
		// Icons enabled for this forum?
		if ($karmamod->config['icons'])
		{
			$s_icons = posting_gen_topic_icons('', $icon_id);
		}

		// HTML, BBCode, Smilies, Images and Flash status
		$bbcode_status	= ($config['allow_bbcode']) ? true : false;
		$smilies_status	= ($bbcode_status && $config['allow_smilies']) ? true : false;
		$img_status		= ($bbcode_status) ? true : false;
		$url_status		= ($config['allow_post_links']) ? true : false;
		$flash_status	= ($bbcode_status && $config['allow_post_flash']) ? true : false;

		if ($submit || confirm_box(true) || $preview)
		{
			$enable_bbcode	= (!$bbcode_status || isset($_POST['disable_bbcode'])) ? false : true;
			$enable_smilies	= (!$smilies_status || isset($_POST['disable_smilies'])) ? false : true;
			$enable_urls	= (isset($_POST['disable_magic_url'])) ? false : true;
		}
		else
		{
			$enable_bbcode	= ($bbcode_status && $user->optionget('bbcode')) ? true : false;
			$enable_smilies	= ($smilies_status && $user->optionget('smilies')) ? true : false;
			$enable_urls	= true;
		}

		if ($karmamod->config['comments'] && ($submit || $preview || confirm_box(true)))
		{
			// Load message for parse
			$message_parser->message = $comment;

			// Comments required?
			$parser_mode = ($karmamod->config['comments_reqd']) ? 'post' : 'sig';

			// Parse message
			$message_parser->parse($enable_bbcode, $enable_urls, $enable_smilies, $img_status, $flash_status, true, $url_status, true, $parser_mode);

			if (sizeof($message_parser->warn_msg))
			{
				$error[] = implode('<br />', $message_parser->warn_msg);
			}
		}

		// Update DB and show 'successfully karmaed' message
		if (confirm_box(true) && !$error)
		{
			if ($karmamod->config['comments'])
			{
				$comment 			= $message_parser->message;
				$bbcode_bitfield	= $message_parser->bbcode_bitfield;
				$bbcode_uid			= $message_parser->bbcode_uid;
				// Grab md5 'checksum' of new comment
				$comment_md5 		= md5($message_parser->message);
			}
			else
			{
				$comment			= '';
				$bbcode_bitfield	= '';
				$bbcode_uid			= '';
				$comment_md5		= '';
			}

			$data = array(
				'forum_id'			=> (int) $forum_id,
				'topic_id'			=> (int) $topic_id,
				'post_id'			=> (int) $post_id,
				'user_id'			=> (int) $user_id,
				'icon_id'			=> (int) $icon_id,
				'username'			=> (string) $username,
				'enable_bbcode'		=> (bool) $enable_bbcode,
				'enable_urls'		=> (bool) $enable_urls,
				'enable_smilies'	=> (bool) $enable_smilies,
				'mode'				=> (string) $mode,
				'action'			=> (string) $action,
				'comment'			=> $comment,
				'comment_md5'		=> (string) $comment_md5,
				'bbcode_bitfield'	=> $bbcode_bitfield,
				'bbcode_uid'		=> $bbcode_uid,
				'karma_power'		=> $karma_power,
			);

			unset($message_parser);

			$karmamod->submit_karma($data);

			/**
			 * Select return message and generate return url
			 * Generated by:
			 * - topic id to specified topic, to specified post, if post id avaible :)
			 * - forum id to specified forum
			 * - user id to specified profile page
			 */
			if ($topic_id || $post_id)
			{
				$back_url = append_sid("{$phpbb_root_path}viewtopic.$phpEx", (($forum_id) ? 'f=' . $forum_id . '&amp;' : '') . (($topic_id) ? 't=' . $topic_id . '&amp;' : '') . (($post_id) ? 'p=' . $post_id . '#p' . $post_id : ''));
				$l_return = $user->lang['RETURN_TOPIC'];
			}
			else if ($forum_id)
			{
				$back_url = append_sid("{$phpbb_root_path}viewforum.$phpEx", 'f=' . $forum_id);
				$l_return = $user->lang['RETURN_FORUM'];
			}
			else
			{
				$back_url = append_sid("{$phpbb_root_path}memberlist.$phpEx", 'mode=viewprofile&amp;u=' . $user_id);
				$l_return = $user->lang['KARMA_RETURN_VIEWPROFILE'];
			}

			meta_refresh(3, $back_url);

			$message = (($action == 'increase') ? $user->lang['KARMA_SUCCESSFULLY_INCREASED'] : $user->lang['KARMA_SUCCESSFULLY_DECREASED']) . '<br /><br />';
			$message .= sprintf($l_return, '<a href="' . $back_url . '">', '</a>');
			trigger_error($message);
		}
		else if (($karmamod->config['comments'] && !$error && $submit) || !$karmamod->config['comments'])
		{
			// Show confirm box
			$s_hidden_fields = array(
				'mode'				=> $mode,
				'action'			=> $action,
				'f'					=> $forum_id,
				't'					=> $topic_id,
				'p'					=> $post_id,
				'u'					=> $user_id,
				'icon'				=> $icon_id,
				'un'				=> $username,
				'message'			=> $comment,
				'power'			=> $karma_power,
			);
			if (!$enable_bbcode)
			{
				$s_hidden_fields['disable_bbcode'] = true;
			}
			if (!$enable_smilies)
			{
				$s_hidden_fields['disable_smilies'] = true;
			}
			if (!$enable_urls)
			{
				$s_hidden_fields['disable_magic_url'] = true;
			}
			
			$s_hidden_fields = build_hidden_fields($s_hidden_fields);

			confirm_box(false, (($action == 'increase') ? 'KARMA_INCREASE' : 'KARMA_DECREASE'), $s_hidden_fields);
		}

		// Preview
		if (!sizeof($error) && $preview && $comment)
		{
			$preview_message = $message_parser->format_display($bbcode_status, $url_status, $smilies_status, false);

			$template->assign_vars(array(
				'PREVIEW_MESSAGE'		=> $preview_message,

				'S_DISPLAY_PREVIEW'		=> true)
			);
		}

		// Generate smiley listing
		generate_smilies('inline', $forum_id);

		// Page title & action URL, include session_id for security purpose
		$s_action = append_sid("{$phpbb_root_path}karma.$phpEx", "mode=$mode&amp;action=$action", true, $user->session_id);

		$s_hidden_fields = '<input type="hidden" name="u" value="' . $user_id . '" />';
		$s_hidden_fields .= ($forum_id) ? '<input type="hidden" name="f" value="' . $forum_id . '" />' : '';
		$s_hidden_fields .= ($topic_id) ? '<input type="hidden" name="t" value="' . $topic_id . '" />' : '';
		$s_hidden_fields .= ($post_id) ? '<input type="hidden" name="p" value="' . $post_id . '" />' : '';

		// Generate selector of karma power
		$s_karma_power = '<select name="power" id="power" title="' . $user->lang['KARMA_POWER'] . '">';
		for ($i = $max_karma_power; $i > 0; $i--)
		{
			$s_karma_power .= '<option value="' . $i . '">' . $i . '</option>';
		}
		$s_karma_power .= '</select>';

		// Show comments page
		$template->assign_vars(array(
			'L_POST_A'				=> (($action == 'increase') ? $user->lang['KARMA_INCREASE'] : $user->lang['KARMA_DECREASE']),

			'MESSAGE'				=> $comment,
			'BBCODE_STATUS'			=> ($bbcode_status) ? sprintf($user->lang['BBCODE_IS_ON'], '<a href="' . append_sid("{$phpbb_root_path}faq.$phpEx", 'mode=bbcode') . '">', '</a>') : sprintf($user->lang['BBCODE_IS_OFF'], '<a href="' . append_sid("{$phpbb_root_path}faq.$phpEx", 'mode=bbcode') . '">', '</a>'),
			'IMG_STATUS'			=> ($img_status) ? $user->lang['IMAGES_ARE_ON'] : $user->lang['IMAGES_ARE_OFF'],
			'FLASH_STATUS'			=> ($flash_status) ? $user->lang['FLASH_IS_ON'] : $user->lang['FLASH_IS_OFF'],
			'SMILIES_STATUS'		=> ($smilies_status) ? $user->lang['SMILIES_ARE_ON'] : $user->lang['SMILIES_ARE_OFF'],
			'URL_STATUS'			=> ($bbcode_status && $url_status) ? $user->lang['URL_IS_ON'] : $user->lang['URL_IS_OFF'],
			'MINI_POST_IMG'			=> $user->img('icon_post_target', $user->lang['POST']),
			'POST_DATE'				=> $user->format_date($karmamod->current_time),
			'ERROR'					=> (sizeof($error)) ? implode('<br />', $error) : '',

			'S_SHOW_ICONS'			=> $s_icons,
			'S_BBCODE_ALLOWED'		=> $bbcode_status,
			'S_BBCODE_CHECKED'		=> (!$enable_bbcode) ? ' checked="checked"' : '',
			'S_SMILIES_ALLOWED'		=> $smilies_status,
			'S_SMILIES_CHECKED'		=> (!$enable_smilies) ? ' checked="checked"' : '',
			'S_LINKS_ALLOWED'		=> $url_status,
			'S_MAGIC_URL_CHECKED'	=> (!$enable_urls) ? ' checked="checked"' : '',

			'S_BBCODE_IMG'			=> $img_status,
			'S_BBCODE_URL'			=> $url_status,
			'S_BBCODE_FLASH'		=> $flash_status,
			'S_BBCODE_QUOTE'		=> true,

			'S_COMMENTS_NOT_REQD'	=> (!$karmamod->config['comments_reqd']) ? true : false,
			'S_KARMA_ACTION'		=> $s_action,
			'S_HIDDEN_FIELDS'		=> $s_hidden_fields,

			'S_KARMA_POWER_SELECT'	=> $s_karma_power,
			'S_KARMA_POWER'	=> ($karmamod->config['power']) ? true : false,
		));
	break;

	default:
		trigger_error('NO_MODE');
	break;
}

// Output the page
page_header($page_title);

$template->set_filenames(array(
	'body' => $template_html)
);
make_jumpbox(append_sid("{$phpbb_root_path}viewforum.$phpEx"), $forum_id);

page_footer();

?>