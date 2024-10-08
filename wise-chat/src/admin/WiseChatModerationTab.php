<?php 

/**
 * Wise Chat admin moderation settings tab class.
 *
 * @author Kainex <contact@kainex.pl>
 */
class WiseChatModerationTab extends WiseChatAbstractTab {

	private $rights = array(
		'approve' => 'Approve messages',
		'edit' => 'Edit messages',
		'delete' => 'Delete messages',
		'mute' => 'Mute users',
		'ban' => 'Ban users',
		'spam' => 'Report spam messages',
	);

	public function getFields() {
		return array(
			array('_section', 'Moderators - Users', 'Grant moderation rights to individual users. Moderation buttons are visible when moving the cursor in front of a message in the chat window. In the mobile version the buttons become visible after tapping a message.'),
			array('moderators', 'Moderators List', 'moderatorsCallback', 'void'),
			array('moderator_add', 'Add a Moderator', 'moderatorAddCallback', 'void'),

			array('_section', 'Own Messages Moderation',
				'Grant rights to moderate own messages.'
			),
			array('enable_edit_own_messages', 'Edit Own Messages', 'booleanFieldCallback', 'boolean'),
			array('enable_delete_own_messages', 'Delete Own Messages', 'booleanFieldCallback', 'boolean'),

			array('_section', 'Moderators - User Roles',
				'Grant moderation rights to user roles. Moderation buttons are visible when moving the cursor in front of a message in the chat window. In the mobile version the buttons become visible after tapping a message.'
			),
			array(
				'permission_approve_message_role', 'Approve Action', 'checkboxesCallback', 'multivalues',
				'User roles allowed to approve pending messages when Pending Messages feature is on (see below).<br />Alternatively, assign "wise_chat_approve_message" capability to any custom role.<br />',
				self::getRoles()
			),
			array('enable_approval_confirmation', 'Approve Action Confirmation', 'booleanFieldCallback', 'boolean',
				'Displays a confirmation message after clicking the approval button.'
			),
			array(
				'permission_edit_message_role', 'Edit Action', 'checkboxesCallback', 'multivalues',
				'User roles allowed to edit messages.<br /> Alternatively, assign "wise_chat_edit_message" capability to any custom role.', self::getRoles()
			),

			array('enable_reply_to_messages', 'Reply-To Action', 'booleanFieldCallback', 'boolean'),
			array(
				'permission_delete_message_role', 'Delete Action', 'checkboxesCallback', 'multivalues',
				'User roles allowed to delete messages.<br /> Alternatively, assign "wise_chat_delete_message" capability to any custom role.', self::getRoles()
			),

			array(
				'permission_ban_user_role', 'Mute Action', 'checkboxesCallback', 'multivalues',
				'User roles allowed to mute users.<br /> Alternatively, assign "wise_chat_mute_user" capability to any custom role.', self::getRoles()
			),
			array('moderation_ban_duration', 'Mute Duration', 'stringFieldCallback', 'integer', 'Empty field means that the user is muted for 1440 minutes (1 day).'),
			array(
				'permission_kick_user_role', 'Ban Action', 'checkboxesCallback', 'multivalues',
				'User roles allowed to ban users.<br /> Alternatively, assign "wise_chat_ban_user" capability to any custom role.', self::getRoles()
			),

			array(
				'permission_spam_report_role', 'Spam Report Action', 'checkboxesCallback', 'multivalues',
				'User roles allowed to report spam messages.<br /> Alternatively, assign "wise_chat_spam_report" capability to any custom role.', self::getRoles()
			),
			array(
				'spam_report_enable_all', 'Spam Report Action For All', 'booleanFieldCallback', 'boolean',
				'Enables spam reporting button for all (including anonymous users).'
			),

			array('_section', 'Spam Reporting Notification',
				'Notification e-mail sent to admin when Report Spam button is clicked.'
			),
			array('spam_report_recipient', 'Recipient', 'stringFieldCallback', 'string'),
			array('spam_report_subject', 'Subject', 'stringFieldCallback', 'string'),
			array('spam_report_content', 'Content', 'multilineFieldCallback', 'multilinestring', 'Available variables: {url}, {channel}, {message}, {message-user}, {message-user-ip}, {report-user}, {report-user-ip}'),

			array('_section', 'Pending Messages',
				'After enabling this feature all posted messages are hidden until they are manually approved using Approve button (enable corresponding moderation permissions in the section above).'
			),
			array(
				'new_messages_hidden', 'Enable', 'booleanFieldCallback', 'boolean',
				'All new messages are hidden. They will become visible as soon as they are manually approved.'
			),
			array(
				'show_hidden_messages_roles', 'Show Hidden Messages For', 'checkboxesCallback', 'multivalues',
				'Shows hidden messages for selected user roles.', self::getRoles()
			),
			array(
				'no_hidden_messages_roles', 'Don\'t Hide Messages For', 'checkboxesCallback', 'multivalues',
				'Prevents from hiding messages for selected user roles.', self::getRoles()
			),
			array(
				'approving_messages_mode', 'Approving Messages Mode', 'selectCallback', 'string',
				'Determines what date and time to set for hidden messages that have been approved.', self::getPendingMessagesApprovalModes()
			),
		);
	}
	
	public function getDefaultValues() {
		return array(
			'enable_approval_confirmation' => 1,
			'permission_approve_message_role' => 'administrator',
			'permission_edit_message_role' => 'administrator',
			'permission_delete_message_role' => 'administrator',
			'permission_ban_user_role' => 'administrator',
			'permission_kick_user_role' => 'administrator',
			'enable_edit_own_messages' => 0,
			'enable_reply_to_messages' => 1,
			'enable_delete_own_messages' => 0,
			'moderation_ban_duration' => 1440,
			'spam_report_enable_all' => 1,
			'permission_spam_report_role' => 'administrator',
			'spam_report_recipient' => get_option('admin_email'),
			'spam_report_subject' => '[Wise Chat] Spam Report',
			'spam_report_content' => "Wise Chat Spam Report\n\n".
				'Channel: {channel}'."\n".
				'Message: {message}'."\n".
				'Posted by: {message-user}'."\n".
				'Posted from IP: {message-user-ip}'."\n\n".
				"--\n".
				'This e-mail was sent by {report-user} from {url}'."\n".
				'{report-user-ip}',
			'new_messages_hidden' => 0,
			'approving_messages_mode' => 1,
			'show_hidden_messages_roles' => 'administrator',
			'no_hidden_messages_roles' => 'administrator',
		);
	}
	
	public function getParentFields() {
		return array(
			'show_hidden_messages_roles' => 'new_messages_hidden',
			'no_hidden_messages_roles' => 'new_messages_hidden',
			'approving_messages_mode' => 'new_messages_hidden',
		);
	}

	public function getProFields() {
        return array(
            'enable_edit_own_messages', 'enable_reply_to_messages', 'enable_approval_confirmation', 'permission_approve_message_role',
	        'permission_edit_message_role', 'approving_messages_mode', 'show_hidden_messages_roles', 'no_hidden_messages_roles',
	        'new_messages_hidden', 'enable_delete_own_messages'
        );
    }

	public function getRoles() {
		$editableRoles = array_reverse(get_editable_roles());
		$rolesOptions = array();

		foreach ($editableRoles as $role => $details) {
			$name = translate_user_role($details['name']);
			$rolesOptions[esc_attr($role)] = $name;
		}
	
		return $rolesOptions;
	}

	public function getPendingMessagesApprovalModes() {
		return array(
			1 => 'Date and time of the message',
			2 => 'Date and time of the approval'
		);
	}

	public function deleteModeratorAction() {
		if (!current_user_can(WiseChatSettings::CAPABILITY) || !wp_verify_nonce($_GET['nonce'], 'deleteModerator')) {
			return;
		}
		if (!isset($_GET['index'])) {
			return;
		}

		$index = intval($_GET['index']);
		$accessUsers = (array) $this->options->getOption('moderators', array());
		if ($index < count($accessUsers)) {
			unset($accessUsers[$index]);
			$this->options->setOption('moderators', array_values($accessUsers));
			$this->options->saveOptions();
			$this->addMessage('The moderator has been removed from the access list');
		}
	}

	public function addModeratorAction() {
		if (!current_user_can(WiseChatSettings::CAPABILITY) || !wp_verify_nonce($_GET['nonce'], 'addModerator')) {
			return;
		}

		$addModeratorUserLogin = trim($_GET['addModeratorUserLogin']);
		$addModeratorRights = array_filter(explode(',', trim($_GET['addModeratorRights'])));
		if (!$addModeratorUserLogin) {
			$this->addErrorMessage('Please specify the user login');
		} else if (count($addModeratorRights) === 0) {
			$this->addErrorMessage('Please specify the user login');
		} else {
			$wpUser = $this->usersDAO->getWpUserByLogin($addModeratorUserLogin);
			if ($wpUser === null) {
				$this->addErrorMessage('The user login is not correct');
			} else {
				$accessUsers = (array) $this->options->getOption('moderators', array());
				$accessUsers[] = array('userId' => $wpUser->ID, 'rights' => $addModeratorRights);
				$this->options->setOption('moderators', $accessUsers);
				$this->options->saveOptions();

				$this->addMessage("The moderator has been added");
			}
		}
	}

	public function moderatorsCallback() {
		$url = admin_url("options-general.php?page=".WiseChatSettings::MENU_SLUG);
		$users = (array) $this->options->getOption('moderators', array());

		$html = "<div style='height: 150px; overflow-y: auto; border: 1px solid #aaa; padding: 5px;'>";
		if (count($users) == 0) {
			$html .= '<small>No moderators were added yet</small>';
		} else {
			$html .= '<table class="wp-list-table widefat fixed striped users wcCondensedTable">';
			$html .= '<tr><th>User Login</th><th>Display Name</th><th>User Can</th><th style="width:90px"></th></tr>';
			foreach ($users as $userKey => $userDetails) {
				$userID = $userDetails['userId'];
				$rights = $userDetails['rights'];
				$rightsMapped = array();
				foreach ($this->rights as $slug => $name) {
					if (in_array($slug, $rights)) {
						$rightsMapped[] = $name;
					}
				}
				$rightsMappedJoined = implode(', ', $rightsMapped);
				$deleteURL = $url . '&wc_action=deleteModerator&tab=moderation&index=' . $userKey.'&nonce='.wp_create_nonce('deleteModerator');
				$deleteLink = "<a href='{$deleteURL}' onclick='return confirm(\"Are you sure you want to delete the moderator?\")'>Delete</a><br />";
				$user = $this->usersDAO->getWpUserByID(intval($userID));
				if ($user !== null) {
					$html .= sprintf("<tr><td>%s</td><td>%s</td><td>%s</td><td>%s</td></tr>", $user->user_login, $user->display_name, $rightsMappedJoined, $deleteLink);
				} else {
					$html .= sprintf("<tr><td colspan='2'>Unknown user</td><td>%s</td></td><td>%s</td></tr>", $rightsMappedJoined, $deleteLink);
				}
			}
			$html .= '</table>';
		}
		$html .= "</div>";
		print($html);

		$this->printProFeatureNotice();
	}

	public function moderatorAddCallback() {

		$checkboxes = array();
		foreach ($this->rights as $slug => $name) {
			$checkboxes[] = sprintf('<label><input type="checkbox" disabled value="%s" id="addModerator-%s" name="addModerator-%s" class="wc-add-moderator-right" />%s</label>', $slug, $slug, $slug, $name);
		}

		printf(
			'<input type="text" value="" placeholder="User Login" disabled class="wcUserLoginHint wc-add-moderator-user-login" style="margin-bottom: 10px;" /><br />%s<br />'.
			'<a class="button-secondary wc-add-moderator-button" href="" disabled title="Adds user to the moderators list" style="margin-top: 10px;">Add</a>',
			implode('<br/>', $checkboxes)
		);

		$this->printProFeatureNotice();
	}
}