<?php
/** 
*
* karma [English]
*
* @package karmamod
* @version $Id: karma.php,v 68 2009/09/23 09:15:11 m157y Exp $
* @copyright (c) 2007, 2009 David Lawson, m157y, A_Jelly_Doughnut
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

// DEVELOPERS PLEASE NOTE
//
// All language files should use UTF-8 as their encoding and the files must not contain a BOM.
//
// Placeholders can now contain order information, e.g. instead of
// 'Page %s of %s' you can (and should) write 'Page %1$s of %2$s', this allows
// translators to re-order the output of data while ensuring it remains correct
//
// You do not need this where single placeholders are used, e.g. 'Message %d' is fine
// equally where a string contains only two placeholders which are used to wrap text
// in a url you again do not need to specify an order e.g., 'Click %sHERE%s' is fine
//
// Some characters you may want to copy&paste:
// ’ » “ ” …
//

$lang = array_merge($lang, array(
	// [+] ACP variables
	'ACP_KARMA'							=> 'Karma MOD',
	'ACP_KARMA_CONFIG'					=> 'Karma MOD Configuration',
	'ACP_KARMA_CONFIG_EXPLAIN'			=> 'You can change the basic settings for the Karma MOD.',
	'ACP_KARMA_HISTORY'					=> 'Karma MOD History',
	'ACP_KARMA_HISTORY_EXPLAIN'			=> 'This is a list of all karma changes on this board.',
	'ACP_KARMA_STATS'					=> 'Karma MOD Statistics',
	'ACP_KARMA_STATS_EXPLAIN'			=> 'You can see statistics of karma changes and average statistics for users of your board here.',

	'ACP_KARMA_ANONYM_DECREASE'				=> 'Anonymous decrease karma',
	'ACP_KARMA_ANONYM_DECREASE_EXPLAIN'		=> 'If enabled, only administrators can see karma decreases.',
	'ACP_KARMA_ANONYM_INCREASE'				=> 'Anonymous increase karma',
	'ACP_KARMA_ANONYM_INCREASE_EXPLAIN'		=> 'If enabled, only administrators can see karma increases.',
	'ACP_KARMA_APPEND_COMMENTS'			=> 'comments',
	'ACP_KARMA_APPEND_POSTS'			=> 'posts',
	'ACP_KARMA_APPEND_TIME'				=> 'hours',
	'ACP_KARMA_APPEND_TIMES'			=> 'times',
	'ACP_KARMA_BAN'								=> 'Automatically ban',
	'ACP_KARMA_BAN_EXPLAIN'				=> 'Karma MOD can ban by karma minumum',
	'ACP_KARMA_BAN_VALUE'					=> 'Ban karma value',
	'ACP_KARMA_BAN_VALUE_EXPLAIN'	=> 'When user reach this karma, he will be banned',
	'ACP_KARMA_BAN_REASON'				=> 'Ban reason',
	'ACP_KARMA_BAN_REASON_EXPLAIN'	=> 'This text will be showed at ACP/MCP',
	'ACP_KARMA_BAN_REASON_GIVE'		=> 'Reason shown to the banned',
	'ACP_KARMA_BAN_REASON_GIVE_EXPLAIN'	=> 'This text will be showed for banned user',
	'ACP_KARMA_BETA_VERSION'		=> 'Latest beta version',
	'ACP_KARMA_COMMENTS'				=> 'Enable comments',
	'ACP_KARMA_COMMENTS_EXPLAIN'		=> 'If enabled, users can leave comments to explaing why they karmaed the user.',
	'ACP_KARMA_COMMENTS_REQD'			=> 'Comments required',
	'ACP_KARMA_COMMENTS_REQD_EXPLAIN'	=> 'If enabled, a user must leave a comment when giving karma',
	'ACP_KARMA_COMMENTS_PER_PAGE'		=> 'Comments per page',
	'ACP_KARMA_CONFIG_UPDATED'			=> 'Karma MOD’s configuration updated successfully.',
	'ACP_KARMA_DRAFTS'					=> 'Enable drafts',
	'ACP_KARMA_DRAFTS_EXPLAIN'			=> 'If enabled, comments will support drafts, like PMs and posts.',
	'ACP_KARMA_ENABLED'					=> 'Enable karma',
	'ACP_KARMA_ENABLED_EXPLAIN'			=> 'If disabled, all karma functions will be disabled and karma will not be displayed anywhere.',
	'ACP_KARMA_ENABLED_UCP'				=> 'Enable karma disabling',
	'ACP_KARMA_ENABLED_UCP_EXPLAIN'		=> 'If enabled, users can disable karma for themselves',
	'ACP_KARMA_ICONS'					=> 'Enable icons',
	'ACP_KARMA_ICONS_EXPLAIN'			=> 'Enable icons for comments, like topic icons',
	'ACP_KARMA_MINIMUM'						=> 'Needed karma',
	'ACP_KARMA_MINIMUM_EXPLAIN'			=> 'After a user reaches this karma count, the user can karma',
	'ACP_KARMA_NOTIFY_EMAIL'			=> 'Enable email notifications',
	'ACP_KARMA_NOTIFY_EMAIL_EXPLAIN'	=> 'If enabled, each user can disable it personaly via UCP',
	'ACP_KARMA_NOTIFY_PM'				=> 'Enable PM notifications',
	'ACP_KARMA_NOTIFY_PM_EXPLAIN'		=> 'If enabled, each user can disable it personaly via UCP',
	'ACP_KARMA_NOTIFY_JABBER'			=> 'Enable jabber notifications',
	'ACP_KARMA_NOTIFY_JABBER_EXPLAIN'	=> 'If enabled, each user can disable it personaly via UCP',
	'ACP_KARMA_PER_DAY'					=> 'Limit per day karmas by',
	'ACP_KARMA_PER_DAY_EXPLAIN'			=> 'Number of karma changes for a single user, zero to disable.',
	'ACP_KARMA_POSTS'					=> 'Needed posts',
	'ACP_KARMA_POSTS_EXPLAIN'			=> 'After a user reaches this post count, the user can karma',
	'ACP_KARMA_POWER'					=> 'Enable karma power',
	'ACP_KARMA_POWER_MAX'				=> 'Maximum karma power',
	'ACP_KARMA_POWER_SHOW'				=> 'Show karma power',
	'ACP_KARMA_POWER_SHOW_EXPLAIN'		=> 'If disabled, only administrators can see karma power.',
	'ACP_KARMA_REMOVE_INSTALL'			=> 'Please delete, move or rename the Karma MOD’s install directory before you use your board.',
	'ACP_KARMA_SETTINGS'				=> 'Karma MOD Settings',
	'ACP_KARMA_TIME'					=> 'Karma time',
	'ACP_KARMA_TIME_EXPLAIN'			=> 'How much time users must wait until they may give karma again',
	'ACP_KARMA_TOPLIST'					=> 'Enable toplist',
	'ACP_KARMA_TOPLIST_EXPLAIN'			=> 'If enabled, top list will be displayed on index page',
	'ACP_KARMA_TOPLIST_USERS'			=> 'Number of users in toplist',
	'ACP_KARMA_TOPLIST_USERS_EXPLAIN'	=> 'Number of users which will be displayed in toplist on index page, must be more than 0',
	'ACP_KARMA_UPDATER_BETA'			=> 'Check beta version updates',
	'ACP_KARMA_VERSION_CHECK'			=> 'Karma MOD Version check',
	'ACP_KARMA_VERSION_CHECK_MENU'					=> 'Check the Karma MOD for updates',
	'ACP_KARMA_VERSION_CHECK_EXPLAIN'	=> 'Checks to see if the version of the Karma MOD you are currently running is up to date.',
	'ACP_KARMA_VERSION_NOT_UP_TO_DATE_ACP'=> 'Your version of the Karma MOD is not up to date.<br />Below you will find a link to the release announcement for the latest version.',
	'ACP_KARMA_VERSION_UP_TO_DATE_ACP'	=> 'Your installation is up to date, no updates are available for your version of Karma MOD. You do not need to update your installation.',
	'ACP_KARMA_VERSION_UPDATE_INSTRUCTIONS'			=> '<h1>Release announcement</h1>

		<p>Please read <a href="%1$s" title="%1$s"><strong>the release announcement for the latest version</strong></a> before you continue your update process, it may contain useful information. It also contains full download links as well as the change log.</p>',
	'ACP_KARMA_VIEWPROFILE'				=> 'Enable view comments',
	'ACP_KARMA_VIEWPROFILE_EXPLAIN'		=> 'If enabled, view comments section will be displayed on viewprofile page',
	'ACP_KARMA_ZEBRA'							=> 'Enable karma for friends & foes',

	'KARMA_LOG_CONFIG'			=> '<strong>Altered the Karma MOD settings</strong>',
	'KARMA_LOG_CLEAR'				=> '<strong>Cleared the Karma MOD history</strong>',

	'IMG_ICON_KARMA_DECREASE'			=> 'Karma decrease',
	'IMG_ICON_KARMA_INCREASE'			=> 'Karma increase',

	'acl_f_karma_can'		=> array('lang' => 'Can karma users', 'cat' => 'misc'),
	'acl_f_karma_view'		=> array('lang' => 'Can view karma comments', 'cat' => 'misc'),
	'acl_u_karma_can'		=> array('lang' => 'Can karma users', 'cat' => 'misc'),
	'acl_u_karma_view'		=> array('lang' => 'Can view karma comments', 'cat' => 'misc'),
	// [-] ACP variables

	// [+] Install variables
	'INSTALL_KARMA_ADMIN_ONLY'					=> 'Sorry, but only users with administrator privileges can install or update the Karma MOD',
	'INSTALL_KARMA_CAT_KARMA'					=> 'Overview',
	'INSTALL_KARMA_CAT_INSTALL'					=> 'Install',
	'INSTALL_KARMA_CAT_UPDATE'					=> 'Update',
	'INSTALL_KARMA_CONGRATS'					=> 'Congratulations!',
	'INSTALL_KARMA_CONGRATS_EXPLAIN'			=> '<p>You have now successfully installed the Karma MOD %1$s. Clicking the button below will take you to your Administration Control Panel (ACP). Take some time to examine the options available to you. Remember that help is available online via the <a href="http://www.phpbb.com/community/viewtopic.php?f=70&t=559069">support topic on phpBB.com</a>.</p><p><strong>Please now delete, move or rename the “install_karma” directory before you use your board.</strong></p>',
	'INSTALL_KARMA_CONGRATS_UPDATE'				=> 'Congratulations!',
	'INSTALL_KARMA_CONGRATS_UPDATE_EXPLAIN'		=> '<p>You have now successfully updated the Karma MOD to %1$s version. Clicking the button below will take you to your Administration Control Panel (ACP). Take some time to examine the options available to you. Remember that help is available online via the <a href="http://www.phpbb.com/community/viewtopic.php?f=70&t=559069">support topic on phpBB.com</a>.</p><p><strong>Please now delete, move or rename the “install_karma” directory before you use your board.</strong></p>',
	'INSTALL_KARMA_DB_USED'						=> ', currently used',
	'INSTALL_KARMA_FILES_REQUIRED'				=> 'Files and Directories',
	'INSTALL_KARMA_FILES_REQUIRED_EXPLAIN'		=> '<strong>Required</strong> - In order to function correctly the Karma MOD needs to be able to access to certain files or directories. If you see “Not Found” you need to copy from the Karma MOD’s distributive the relevant file or directory.',
	'INSTALL_KARMA_INTRO'						=> 'Introduction',
	'INSTALL_KARMA_INTRO_BODY'					=> 'Here it is possible to install or update the Karma MOD onto your phpBB installation.</p><p>In order to proceed, you will need administrator acount. You will not be able to continue without it.</p>

	<p>Karma MOD for phpBB3 supports the following databases:</p>
	<ul>
		<li>MySQL 3.23 or above (MySQLi supported)</li>
		<li>PostgreSQL 7.3+</li>
		<li>SQLite 2.8.2+</li>
		<li>Firebird 2.0+</li>
		<li>MS SQL Server 2000 or above (directly or via ODBC)</li>
		<li>Oracle</li>
	</ul>
	
	<p>',
	'INSTALL_KARMA_INTRO_BODY_INSTALL'			=> 'Here it is possible to install the Karma MOD onto your phpBB installation.</p><p>In order to proceed, you will need administrator acount. You will not be able to continue without it.</p>

	<p>Karma MOD for phpBB3 supports the following databases:</p>
	<ul>
		<li>MySQL 3.23 or above (MySQLi supported)</li>
		<li>PostgreSQL 7.3+</li>
		<li>SQLite 2.8.2+</li>
		<li>Firebird 2.0+</li>
		<li>MS SQL Server 2000 or above (directly or via ODBC)</li>
		<li>Oracle</li>
	</ul>
	
	<p>',
	'INSTALL_KARMA_INTRO_BODY_UPDATE'			=> 'Here it is possible to update the Karma MOD on your phpBB installation.</p><p>In order to proceed, you will need administrator acount. You will not be able to continue without it.',
	'INSTALL_KARMA_PHPBB_DRAFTS'				=> 'Drafts is enabled',
	'INSTALL_KARMA_PHPBB_DRAFTS_EXPLAIN'		=> '<strong>Optional</strong> - This setting is optional, however comment’s drafts will not work without it.',
	'INSTALL_KARMA_PHPBB_EMAIL'					=> 'Email functions is enabled',
	'INSTALL_KARMA_PHPBB_EMAIL_EXPLAIN'			=> '<strong>Optional</strong> - This setting is optional, however email notifications will not work without it.',
	'INSTALL_KARMA_PHPBB_JABBER'				=> 'Jabber functions is enabled',
	'INSTALL_KARMA_PHPBB_JABBER_EXPLAIN'		=> '<strong>Optional</strong> - This setting is optional, however jabber notifications will not work without it.',
	'INSTALL_KARMA_PHPBB_PRIVMSGS'				=> 'PMs is enabled',
	'INSTALL_KARMA_PHPBB_PRIVMSGS_EXPLAIN'		=> '<strong>Optional</strong> - This setting is optional, however PM notifications will not work without it.',
	'INSTALL_KARMA_PHPBB_SETTINGS'				=> 'phpBB version and settings',
	'INSTALL_KARMA_PHPBB_SETTINGS_EXPLAIN'		=> '<strong>Required</strong> - You must be running at least version 3.0.4 of phpBB in order to install the Karma MOD.',
	'INSTALL_KARMA_PHPBB_VERSION_REQD'			=> 'phpBB version >= 3.0.4',
	'INSTALL_KARMA_REQUIREMENTS_TITLE'			=> 'Installation compatibility',
	'INSTALL_KARMA_REQUIREMENTS_EXPLAIN'		=> 'Before proceeding with the full installation the Karma MOD will carry out some tests on your phpBB3 installation to ensure that you are able to install and run the Karma MOD. Please ensure you read through the results thoroughly and do not proceed until all the required tests are passed. If you wish to use any of the features depending on the optional tests, you should ensure that these tests are passed also.',
	'INSTALL_KARMA_REQUIREMENTS_UPDATE_TITLE'	=> 'Update compatibility',
	'INSTALL_KARMA_REQUIREMENTS_UPDATE_EXPLAIN'	=> 'Before proceeding with the update, the Karma MOD will carry out some tests on your phpBB3 installation to ensure that you are able to update yoir installation of the Karma MOD. Please ensure you read through the results thoroughly and do not proceed until all the required tests are passed. If you wish to use any of the features depending on the optional tests, you should ensure that these tests are passed also.',
	'INSTALL_KARMA_SETTINGS'					=> 'Karma MOD version',
	'INSTALL_KARMA_SETTINGS_EXPLAIN'			=> '<strong>Required</strong> - You must be running at least version 3.0.4 of phpBB in order to update the Karma MOD.',
	'INSTALL_KARMA_STAGE_ADMINISTRATOR'			=> 'Administrator details',
	'INSTALL_KARMA_STAGE_CREATE_TABLE'			=> 'Create database tables',
	'INSTALL_KARMA_STAGE_CREATE_TABLE_EXPLAIN'	=> 'The database tables used by Karma MOD have been created and populated with some initial data. Proceed to the next screen to finish installing Karma MOD.',
	'INSTALL_KARMA_STAGE_FINAL'					=> 'Final stage',
	'INSTALL_KARMA_STAGE_INTRO'					=> 'Introduction',
	'INSTALL_KARMA_STAGE_REQUIREMENTS'			=> 'Requirements',
	'INSTALL_KARMA_STAGE_UPDATE'				=> 'Update stage',
	'INSTALL_KARMA_STAGE_UPDATE_EXPLAIN'		=> 'The database tables used by the Karma MOD have been updated and populated with some missed data. Proceed to the next screen to finish updating Karma MOD.',
	'INSTALL_KARMA_SUB_INTRO'					=> 'Introduction',
	'INSTALL_KARMA_SUB_LICENSE'					=> 'License',
	'INSTALL_KARMA_SUB_SUPPORT'					=> 'Support',
	'INSTALL_KARMA_SUPPORT'						=> 'Support',
	'INSTALL_KARMA_SUPPORT_BODY'				=> 'During the beta phase limited support will be given at <a href="http://www.phpbb.com/community/viewtopic.php?f=70&t=559069">the Karma MOD’s topic ont the phpBB 3.0.x “MODs in Development” forum</a>. We will provide answers to general setup questions, configuration problems and support for determining common problems mostly related to bugs. We also allow discussions about modifications and customization code/style.</p>',
	'INSTALL_KARMA_UDPATE_START'				=> 'Start update',
	'INSTALL_KARMA_UDPATE_TEST'					=> 'Test again',
	'INSTALL_KARMA_VERSION'						=> 'Karma MOD version',
	'INSTALL_KARMA_VERSION_CURRENT'				=> 'Karma MOD current version',
	'INSTALL_KARMA_VERSION_NEED_UPDATE'			=> 'Karma MOD need update',

	'LOG_KARMA_INSTALLED'						=> '<strong>Installed Karma MOD %s</strong>',
	'LOG_KARMA_UPDATED'							=> '<strong>Updated Karma MOD from version %1$s to version %2$s</strong>',
	// [-] Install variables

	// [+] Global variables
	'KARMA'							=> 'Karma',
	'KARMA_ALL_COMMENTS'	=> 'All comments',
	'KARMA_ALREADY_KARMAED_POST'	=> 'You have already given karma to this post.',
	'KARMA_CAN_NOT_KARMA_ZEBRA'		=> 'You cannot karma your friends or foes.',
	'KARMA_CAN_NOT_MINIMUM'		=> 'Sorry, but you haven’t reached enough karma points for karmaing other users.',
	'KARMA_CAN_NOT_POSTS'			=> 'Sorry, but you haven’t reached the required post count for karmaing.',
	'KARMA_CAN_NOT_YET'				=> 'Sorry, but you are not allowed to karma yet.',
	'KARMA_COMMENT'					=> 'Comment',
	'KARMA_COMMENTS'				=> 'Comments',
	'KARMA_COMMENTS_DISABLED'		=> 'Karma comments disabled on this board',
	'KARMA_COMMENTS_EXPLAIN'		=> 'All comments for this user’s karma posted below.',
	'KARMA_COMMENTS_SELF_ONLY'	=> 'Sorry, but you are not allowed to see the karma of this user.',
	'KARMA_DECREASE'				=> 'Decrease user’s karma',
	'KARMA_DECREASE_CONFIRM'		=> 'Are you sure you want to decrease the karma of the selected user?',
	'KARMA_EXPLAIN'				=> 'Here you can explain, or write a reason why you are increasing or decreasing this user’s karma',
	'KARMA_ICON'					=> 'Comment icon',
	'KARMA_INCREASE'				=> 'Increase user’s karma',
	'KARMA_INCREASE_CONFIRM'		=> 'Are you sure you want to increase the karma of the selected user?',
	'KARMA_LIMITED_PER_DAY_TIME'	=> 'You can karma only 1 time per day',
	'KARMA_LIMITED_PER_DAY_TIMES'	=> 'You can karma only %1$s times per day',
	'KARMA_MOD_DISABLED'			=> 'Sorry, but the Karma MOD is currently disabled.',
	'KARMA_NO_COMMENTS'				=> 'No comments for user’s karma',
	'KARMA_NO_CURRENT_USER'			=> 'Karma change disabled for this user',
	'KARMA_NO_ICON'					=> 'No comment icon',
	'KARMA_NO_KARMA_MODE'			=> 'No karma mode specified.',
	'KARMA_NO_SELF'					=> 'You are not allowed to karma yourself.',
	'KARMA_NOTIFY_HIDDEN_SENDER' => 'Hidden',
	'KARMA_NOTIFY_INCREASE_SUBJECT'	=> 'Your karma has increased',
	'KARMA_NOTIFY_INCREASE_MESSAGE'	=> 'User %1$s has increased your karma.',
	'KARMA_NOTIFY_INCREASE_MESSAGE_ANONYM'	=> 'Someone has increased your karma.',
	'KARMA_NOTIFY_INCREASE_MESSAGE_POWERED'	=> 'User %1$s has increased your karma with %2$d power.',
	'KARMA_NOTIFY_INCREASE_MESSAGE_POWERED_ANONYM'	=> 'Someone has increased your karma with %2$d power.',
	'KARMA_NOTIFY_DECREASE_SUBJECT'	=> 'Your karma has decreased',
	'KARMA_NOTIFY_DECREASE_MESSAGE'	=> 'User %1$s has decreased your karma',
	'KARMA_NOTIFY_DECREASE_MESSAGE_ANONYM'	=> 'Someone has decreased your karma',
	'KARMA_NOTIFY_DECREASE_MESSAGE_POWERED'	=> 'User %1$s has decreased your karma with %2$d power.',
	'KARMA_NOTIFY_DECREASE_MESSAGE_POWERED_ANONYM'	=> 'Someone has decreased your karma with %2$d power.',
	'KARMA_NOTIFY_MESSAGE_COMMENTS'	=> '%1$s left comment:',
	'KARMA_NOTIFY_BACKLINK_FORUM'	=> 'Comment was left for this forum: ',
	'KARMA_NOTIFY_BACKLINK_POST'	=> 'Comment was left for this post: ',
	'KARMA_NOTIFY_BACKLINK_PROFILE'	=> 'Comment was left for your profile: ',
	'KARMA_NOTIFY_BACKLINK_TOPIC'	=> 'Comment was left for this topic: ',
	'KARMA_POWER'					=> 'Karma power',
	'KARMA_RETURN_VIEWPROFILE'		=> '%sReturn to the profile last visited%s',
	'KARMA_SORT_FORUM'					=> 'Karmaed forum',
	'KARMA_SORT_POST'						=> 'Karmaed post',
	'KARMA_SORT_TIME'						=> 'Comment time',
	'KARMA_SORT_TOPIC'					=> 'Karmaed topic',
	'KARMA_SUCCESSFULLY_DECREASED'	=> 'You successfully decreased the karma of this user.',
	'KARMA_SUCCESSFULLY_INCREASED'	=> 'You successfully increased the karma of this user.',
	'KARMA_TOPLIST'					=> 'Karma toplist',
	'KARMA_TOPLIST_EXPLAIN'			=> 'Users with most karma',
	'KARMA_VIEW_COMMENTS'			=> 'View karma comments',
	'KARMA_VIEW_USER_COMMENT'		=> '1 comment',
	'KARMA_VIEW_USER_COMMENTS'		=> '%d comments',
	'KARMA_USER_COMMENTS'			=> 'View comments to user’s karma',
	'KARMA_USER_PROFILE'			=> 'User’s profile',
	// [-] Global variables

	// [+] UCP variables
	'TOO_LARGE_KARMA_COMMENTS_PER_PAGE'	=> 'The value of “comments per page” is too large.',
	'TOO_LARGE_KARMA_TOPLIST_USERS'		=> 'The value of “toplist users” is too large.',
	'TOO_SMALL_KARMA_COMMENTS_PER_PAGE'	=> 'The value of “comments per page” is too small.',
	'TOO_SMALL_KARMA_TOPLIST_USERS'		=> 'The value of “toplist users” is too small.',

	'UCP_KARMA'							=> 'Edit karma settings',
	'UCP_KARMA_COMMENTS_PER_PAGE'		=> 'Comments per page',
	'UCP_KARMA_COMMENTS_SELF'			=> 'Show comments only for me',
	'UCP_KARMA_ENABLE'					=> 'Enable karma',
	'UCP_KARMA_ENABLE_EXPLAIN'			=> 'If enabled, users can karma you',
	'UCP_KARMA_NOTIFY_EMAIL'			=> 'Notify via email',
	'UCP_KARMA_NOTIFY_EMAIL_EXPLAIN'	=> 'If enabled, you will receive email notifications about karma changes',
	'UCP_KARMA_NOTIFY_JABBER'			=> 'Notify via jabber',
	'UCP_KARMA_NOTIFY_JABBER_EXPLAIN'	=> 'If enabled, you will receive jabber notifications about karma changes',
	'UCP_KARMA_NOTIFY_PM'				=> 'Notify via PM',
	'UCP_KARMA_NOTIFY_PM_EXPLAIN'		=> 'If enabled, you will receive PM notifications about karma changes',
	'UCP_KARMA_TOPLIST'					=> 'Toplist on index',
	'UCP_KARMA_TOPLIST_EXPLAIN'			=> 'If enabled, you can see a toplist of users with the most karma on index page',
	'UCP_KARMA_TOPLIST_USERS'			=> 'Users in toplist',
	'UCP_KARMA_TOPLIST_USERS_APPEND'	=> 'users',
	'UCP_KARMA_TOPLIST_USERS_EXPLAIN'	=> 'Number of users in toplist',
	'UCP_KARMA_UPDATED'					=> 'Your karma’s settings have been updated.',
	'UCP_KARMA_VIEW_COMMENTS_DAYS'		=> 'Display comments from previous days',
	'UCP_KARMA_VIEW_COMMENTS_DIR'			=> 'Display comment order direction',
	'UCP_KARMA_VIEW_COMMENTS_KEY'			=> 'Display comments ordering by',
	// [-] UCP variables
));

?>