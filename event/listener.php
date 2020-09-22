<?php
/**
 *
 * @package - Hide My Profile
 *
 * @copyright (c) 2020 Rich McGirr (RMcGirr83)
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace rmcgirr83\hidemyprofile\event;

use phpbb\auth\auth;
use phpbb\db\driver\driver_interface;
use phpbb\language\language;
use phpbb\request\request;
use phpbb\template\template;
use phpbb\user;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
* Event listener
*/
class listener implements EventSubscriberInterface
{
	/** @var auth */
	protected $auth;

	/** @var \phpbb\db\driver\driver_interface */
	protected $db;

	/** @var language */
	protected $language;

	/** @var request */
	protected $request;

	/** @var array phpBB tables */
	protected $tables;

	/** @var template */
	protected $template;

	/** @var user */
	protected $user;

	/**
	* Constructor
	*
	* @param \phpbb\auth\auth					$auth			Auth object
	* @param \phpbb\db\driver\driver_interface	$db				Database object
	* @param \phpbb\language\language			$language		Language object
	* @param \phpbb\request\request 			$request		Request object
	* @param array								$tables			phpBB db tables
	* @param \phpbb\template\template 			$template		Template object
	* @param \phpbb\user 						$user			User object
	* @return \rmcgirr83\hidemyprofile\event\listener
	* @access public
	*/
	public function __construct(
		auth $auth,
		driver_interface $db,
		language $language,
		request $request,
		array $tables,
		template $template,
		user $user)
	{
		$this->auth = $auth;
		$this->db = $db;
		$this->language = $language;
		$this->request = $request;
		$this->tables = $tables;
		$this->template = $template;
		$this->user = $user;
	}

	/**
	* Assign functions defined in this class to event listeners in the core
	*
	* @return array
	* @static
	* @access public
	*/
	static public function getSubscribedEvents()
	{
		return array(
			'core.acp_extensions_run_action_after'	=>	'acp_extensions_run_action_after',
			'core.permissions'						=>	'hidemyprofile_permissions',
			'core.memberlist_view_profile'			=> 'memberlist_view_profile',
			'core.ucp_prefs_personal_data'			=> 'ucp_prefs_get_data',
			'core.ucp_prefs_personal_update_data'	=> 'ucp_prefs_set_data',
		);
	}

	/* Display additional metadata in extension details
	*
	* @param $event			event object
	* @param return null
	* @access public
	*/
	public function acp_extensions_run_action_after($event)
	{
		if ($event['ext_name'] == 'rmcgirr83/hidemyprofile' && $event['action'] == 'details')
		{
			$this->language->add_lang('hidemyprofile', $event['ext_name']);
			$this->template->assign_var('S_BUY_ME_A_BEER_HMP', true);
		}
	}

	/**
	 * Permission's language file is automatically loaded
	 *
	 * @event	object $event	The event object
	 * @return	null
	 * @access	public
	 */
	public function hidemyprofile_permissions($event)
	{
		$permissions = $event['permissions'];

		$permissions['u_hidemyprofile'] = [
				'lang'	=> 'ACL_U_HIDEMYPROFILE',
				'cat'	=> 'profile',
		];

		$event['permissions'] = $permissions;
	}

	/**
	 * We'll check to see if user can view this profile or not
	 * if not show a message stating so
	 *
	 * @event	object $event	The event object
	 * @return	null
	 * @access	public
	 */
	public function memberlist_view_profile($event)
	{
		$member = $event['member'];

		$is_friend = in_array($this->user->data['user_id'], $this->get_friends($member['user_id']));

		// If admin or mod, or a friend of the user...allow the view
		$is_allowed = ($this->auth->acl_gets('a_', 'm_') || $this->auth->acl_getf_global('m_') || $is_friend) ? true : false;

		// if is_allowed or permission to hide is no longer allowed, allow the view
		if ($is_allowed || !$this->auth->acl_get_list($member['user_id'], 'u_hidemyprofile', false))
		{
			return;
		}

		// if the user viewing isn't the user themselves display a notice
		if ($member['user_hmp'] && $this->user->data['user_id'] != $member['user_id'])
		{
			trigger_error('NOT_AUTHORISED');
		}
	}

	/**
	* Allow friends to view the profile
	*
	* @param	member_id		$member_id	The members user_id
	* @return	array
	* @access	private
	*/
	private function get_friends($member_id)
	{
		$sql = 'SELECT zebra_id
			FROM ' . $this->tables['zebra'] . '
			WHERE user_id = ' . (int) $member_id . '
			AND friend = 1';
		$result = $this->db->sql_query($sql);

		$friends = [];
		while ($row = $this->db->sql_fetchrow($result))
		{
			$friends[$row['zebra_id']] = $row['zebra_id'];
		}
		$this->db->sql_freeresult($result);

		return $friends;
	}

	/**
	* Get user's option and display it in UCP Prefs View page
	*
	* @param	object	$event	The event object
	* @return	null
	* @access	public
	*/
	public function ucp_prefs_get_data($event)
	{

		// Request the user option vars and add them to the data array
		$event['data'] = array_merge($event['data'], [
			'hmp'	=> $this->request->variable('hmp', (int) $this->user->data['user_hmp']),
		]);

		// Output the data vars to the template (except on form submit)
		if (!$event['submit'] && $this->auth->acl_get('u_hidemyprofile'))
		{
			$this->language->add_lang('hidemyprofile', 'rmcgirr83/hidemyprofile');

			$this->template->assign_vars([
				'S_UCP_HMP'	=> true,
				'HMP'	=> $event['data']['hmp'],
			]);
		}
	}

	/**
	* Add user's hidemyprofile option state into the sql_array
	*
	* @param	object	$event	The event object
	* @return	null
	* @access	public
	*/
	public function ucp_prefs_set_data($event)
	{
		$event['sql_ary'] = array_merge($event['sql_ary'], [
			'user_hmp' => $event['data']['hmp'],
		]);
	}
}
