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

use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
* Event listener
*/
class listener implements EventSubscriberInterface
{
	/** @var \phpbb\auth\auth */
	protected $auth;

	/** @var \phpbb\language\language */
	protected $language;

	/** @var \phpbb\request\request */
	protected $request;

	/** @var \phpbb\template\template */
	protected $template;

	/** @var \phpbb\user */
	protected $user;

	/**
	* Constructor
	*
	* @param \phpbb\auth\auth
	* @param \phpbb\language\language
	* @param \phpbb\request\request $request
	* @param \phpbb\template\template $template
	* @param \phpbb\user $user
	* @return \rmcgirr83\hidemyprofile\event\listener
	* @access public
	*/
	public function __construct(
		\phpbb\auth\auth $auth,
		\phpbb\language\language $language,
		\phpbb\request\request $request,
		\phpbb\template\template $template,
		\phpbb\user $user)
	{
		$this->auth = $auth;
		$this->language = $language;
		$this->request = $request;
		$this->template = $template;
		$this->user = $user;
	}

	static public function getSubscribedEvents()
	{
		return array(
			'core.permissions'						=>	'hidemyprofile_permissions',
			'core.memberlist_view_profile'			=> 'hidemyprofile_check',
			'core.ucp_prefs_personal_data'			=> 'ucp_prefs_get_data',
			'core.ucp_prefs_personal_update_data'	=> 'ucp_prefs_set_data',
		);
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

		$permissions += array(
			'u_hidemyprofile' => array(
				'lang'	=> 'ACL_U_HIDEMYPROFILE',
				'cat'	=> 'profile',
			),
		);

		$event['permissions'] = $permissions;
	}

	/**
	 * We'll check to see if user can view this profile or not
	 * if not show a message stating so
	 *
	 *
	 * @event	object $event	The event object
	 * @return	null
	 * @access	public
	 */
	public function hidemyprofile_check($event)
	{
		$member = $event['member'];

		// if admin or mod, or permission to hide is no longer allowed, allow the view
		if ($this->auth->acl_get('a_') || $this->auth->acl_get('m_') || $this->auth->acl_getf_global('m_') || !$this->auth->acl_get_list($member['user_id'], 'u_hidemyprofile', false))
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
	* Get user's option and display it in UCP Prefs View page
	*
	* @param	object	$event	The event object
	* @return	null
	* @access	public
	*/
	public function ucp_prefs_get_data($event)
	{

		// if the user is not allowed to set
		if (!$this->auth->acl_get('u_hidemyprofile'))
		{
			return;
		}

		// Request the user option vars and add them to the data array
		$event['data'] = array_merge($event['data'], array(
			'hmp'	=> $this->request->variable('hmp', (int) $this->user->data['user_hmp']),
		));

		$this->language->add_lang('hidemyprofile', 'rmcgirr83/hidemyprofile');

		// Output the data vars to the template (except on form submit)
		if (!$event['submit'])
		{
			$this->template->assign_vars(array(
				'S_UCP_HMP'	=> true,
				'HMP'	=> $event['data']['hmp'],
			));
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
		$event['sql_ary'] = array_merge($event['sql_ary'], array(
			'user_hmp' => $event['data']['hmp'],
		));
	}
}
