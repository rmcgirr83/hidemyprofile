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

class initial_schema extends \phpbb\db\migration\migration
{
	public function effectively_installed()
	{
		return $this->db_tools->sql_column_exists($this->table_prefix . 'users', 'user_hmp');
	}

	static public function depends_on()
	{
		return array('\phpbb\db\migration\data\v31x\v314rc1');
	}

	public function update_schema()
	{
		return array(
			'add_columns'	=> array(
				$this->table_prefix . 'users'	=> array(
					'user_hmp'	=> array('UINT', 0),
				),
			),
		);
	}

	public function revert_schema()
	{
		return array(
			'drop_columns' => array(
				$this->table_prefix . 'users'	=> array(
					'user_hmp',
				),
			),
		);
	}
}
