<?php
/** 
*
* @package karmamod (ucp)
* @version $Id: ucp_karma.php,v 8 2009/05/14 18:16:17 m157y Exp $
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
* ucp_karma
* Configure user's settings of Karma MOD
* @package karmamod (ucp)
*/
class ucp_karma
{
	var $u_action;

	function main($id, $mode)
	{
		global $karmamod, $config, $db, $user, $auth, $template, $phpbb_root_path, $phpEx;

		$submit = (isset($_POST['submit'])) ? true : false;
		$error = $data = array();
		$s_hidden_fields = '';

		$data = array(
			'enable'					=> ($karmamod->config['enabled']) ? request_var('enable', (bool) $karmamod->config['user_enabled']) : (bool) $karmamod->config['enabled'],
			'notify_email'				=> ($karmamod->config['notify_email']) ? request_var('notify_email', (bool) $karmamod->config['user_notify_email']) : (bool) $karmamod->config['notify_email'],
			'notify_pm'					=> ($karmamod->config['notify_pm']) ? request_var('notify_pm', (bool) $karmamod->config['user_notify_pm']) : (bool) $karmamod->config['notify_pm'],
			'notify_jabber'				=> ($karmamod->config['notify_jabber']) ? request_var('notify_jabber', (bool) $karmamod->config['user_notify_jabber']) : (bool) $karmamod->config['notify_jabber'],
			'toplist'					=> request_var('toplist', (bool) $karmamod->config['toplist']),
			'toplist_users'		=> request_var('toplist_users', (int) $karmamod->config['toplist_users']),
			'comments_per_page'	=> request_var('comments_per_page', (int) $user->data['user_karma_comments_per_page']),
			'comments_self' => request_var('comments_self', (bool) $karmamod->config['comments_self']),
			'karma_comments_sk'		=> request_var('comments_sk', (!empty($user->data['user_karma_comments_sortby_type'])) ? $user->data['user_karma_comments_sortby_type'] : 't'),
			'karma_comments_sd'		=> request_var('comments_sd', (!empty($user->data['user_karma_comments_sortby_dir'])) ? $user->data['user_karma_comments_sortby_dir'] : 'd'),
			'karma_comments_st'		=> request_var('comments_st', (!empty($user->data['user_karma_comments_show_days'])) ? $user->data['user_karma_comments_show_days'] : 0),
		);

		if ($submit)
		{
			if ($karmamod->config['comments'])
			{
				// Check that comments sort orders has only one symbol at value
				$error = validate_data($data, array(
					'karma_comments_sk'	=> array('string', false, 1, 1),
					'karma_comments_sd'	=> array('string', false, 1, 1),

				));
			}

			if (!sizeof($error))
			{
				$sql_ary = array(
					'user_karma_enable'				=> $data['enable'],
					'user_karma_notify_email'		=> $data['notify_email'],
					'user_karma_notify_pm'			=> $data['notify_pm'],
					'user_karma_notify_jabber'		=> $data['notify_jabber'],
					'user_karma_toplist'			=> $data['toplist'],
					'user_karma_toplist_users'		=> $data['toplist_users'],
					'user_karma_comments_per_page'	=> $data['comments_per_page'],
					'user_karma_comments_self'		=> $data['comments_self'],
					'user_karma_comments_sortby_type'		=> $data['karma_comments_sk'],
					'user_karma_comments_sortby_dir'		=> $data['karma_comments_sd'],
					'user_karma_comments_show_days'	=> $data['karma_comments_st'],

				);

				$sql = 'UPDATE ' . USERS_TABLE . '
					SET ' . $db->sql_build_array('UPDATE', $sql_ary) . '
					WHERE user_id = ' . $user->data['user_id'];
				$db->sql_query($sql);

				meta_refresh(3, $this->u_action);
				$message = $user->lang['UCP_KARMA_UPDATED'] . '<br /><br />' . sprintf($user->lang['RETURN_UCP'], '<a href="' . $this->u_action . '">', '</a>');
				trigger_error($message);
			}

			// Replace "error" strings with their real, localised form
			$error = preg_replace('#^([A-Z_]+)$#e', "(!empty(\$user->lang['\\1'])) ? \$user->lang['\\1'] : '\\1'", $error);
		}

		// Comments ordering options
		$sort_dir_text = array('a' => $user->lang['ASCENDING'], 'd' => $user->lang['DESCENDING']);
		$limit_comments_days = array(0 => $user->lang['KARMA_ALL_COMMENTS'], 1 => $user->lang['1_DAY'], 7 => $user->lang['7_DAYS'], 14 => $user->lang['2_WEEKS'], 30 => $user->lang['1_MONTH'], 90 => $user->lang['3_MONTHS'], 180 => $user->lang['6_MONTHS'], 365 => $user->lang['1_YEAR']);

		$sort_by_comments_text = array('a' => $user->lang['AUTHOR'], 't' => $user->lang['KARMA_SORT_TIME'], 'p' => $user->lang['KARMA_SORT_POST'], 'o' => $user->lang['KARMA_SORT_TOPIC'], 'f' => $user->lang['KARMA_SORT_FORUM']);
		$sort_by_comments_sql = array('a' => 'u.username_clean', 't' => 'k.karma_time', 'p' => 'k.post_id', 'o' => 'k.topic_id', 'f' => 'k.forum_id');

		$s_limit_comments_days = '<select name="comments_st">';
		foreach ($limit_comments_days as $day => $text)
		{
			$selected = ($data['karma_comments_st'] == $day) ? ' selected="selected"' : '';
			$s_limit_comments_days .= '<option value="' . $day . '"' . $selected . '>' . $text . '</option>';
		}
		$s_limit_comments_days .= '</select>';

		$s_sort_comments_key = '<select name="comments_sk">';
		foreach ($sort_by_comments_text as $key => $text)
		{
			$selected = ($data['karma_comments_sk'] == $key) ? ' selected="selected"' : '';
			$s_sort_comments_key .= '<option value="' . $key . '"' . $selected . '>' . $text . '</option>';
		}
		$s_sort_comments_key .= '</select>';

		$s_sort_comments_dir = '<select name="comments_sd">';
		foreach ($sort_dir_text as $key => $value)
		{
			$selected = ($data['karma_comments_sd'] == $key) ? ' selected="selected"' : '';
			$s_sort_comments_dir .= '<option value="' . $key . '"' . $selected . '>' . $value . '</option>';
		}
		$s_sort_comments_dir .= '</select>';

		$template->assign_vars(array(
			'ERROR'				=> (sizeof($error)) ? implode('<br />', $error) : '',

			'S_ENABLE'			=> $data['enable'],
			'S_NOTIFY_EMAIL'	=> $data['notify_email'],
			'S_NOTIFY_PM'		=> $data['notify_pm'],
			'S_NOTIFY_JABBER'	=> $data['notify_jabber'],
			'S_TOPLIST'			=> $data['toplist'],
			'S_COMMENTS_SELF'			=> ($karmamod->config['comments_self']) ? true : false,
			'TOPLIST_USERS'		=> $data['toplist_users'],
			'COMMENTS_PER_PAGE'	=> $data['comments_per_page'],

			'S_COMMENTS_SORT_DAYS'		=> $s_limit_comments_days,
			'S_COMMENTS_SORT_KEY'		=> $s_sort_comments_key,
			'S_COMMENTS_SORT_DIR'		=> $s_sort_comments_dir,

			'S_ENABLE_SELECT'		=> ($karmamod->config['enabled_ucp']) ? true : false,
			'S_NOTIFY_EMAIL_SELECT'	=> ($karmamod->config['notify_email'] && $config['email_enable']) ? true : false,
			'S_NOTIFY_PM_SELECT'	=> ($karmamod->config['notify_pm'] && $config['allow_privmsg']) ? true : false,
			'S_NOTIFY_JABBER_SELECT'=> ($karmamod->config['notify_jabber'] && $config['jab_enable']) ? true : false,
			'S_TOPLIST_SELECT'		=> ($karmamod->config['toplist']) ? true : false,
			'S_COMMENTS_SELECT'		=> ($karmamod->config['comments']) ? true : false,
		));

		$template->assign_vars(array(
			'L_TITLE'			=> $user->lang['UCP_KARMA'],

			'S_HIDDEN_FIELDS'	=> $s_hidden_fields,
			'S_UCP_ACTION'		=> $this->u_action)
		);

		$this->tpl_name = 'karma_ucp';
		$this->page_title = 'UCP_KARMA';
	}
}

?>