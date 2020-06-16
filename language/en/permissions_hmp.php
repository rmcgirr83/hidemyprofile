<?php
/**
 *
 * @package - Hide My Profile
 *
 * @copyright (c) 2020 Rich McGirr (RMcGirr83)
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
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
	'ACL_U_HIDEMYPROFILE'	=> 'Can hide their profile',
));
