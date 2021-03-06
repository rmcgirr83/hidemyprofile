<?php
/**
 *
 * @package - Hide My Profile
 *
 * @copyright (c) 2020 Rich McGirr (RMcGirr83)
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace rmcgirr83\hidemyprofile\migrations;

class initial_data extends \phpbb\db\migration\migration
{
	static public function depends_on()
	{
		return array('\phpbb\db\migration\data\v31x\v3110');
	}

	/* Permission not set - to be assigned on a per group/user basis */
	public function update_data()
	{
		return array(
			array('permission.add', array('u_hidemyprofile')),
		);
	}
}
