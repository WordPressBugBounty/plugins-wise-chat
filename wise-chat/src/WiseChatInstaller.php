<?php

/**
 * WiseChat installer.
 *
 * @author Kainex <contact@kainex.pl>
 */
class WiseChatInstaller {

	const GOLD_ENGINE_MU_PLUGIN = '0-wise-chat-engine.php';
	private static $forceDefaultOptions = [];

	public static function getUsersTable() {
		global $wpdb;

		return $wpdb->prefix.'wise_chat_users';
	}

	public static function getMessagesTable() {
		global $wpdb;
		
		return $wpdb->prefix.'wise_chat_messages';
	}
	
	public static function getBansTable() {
		global $wpdb;
		
		return $wpdb->prefix.'wise_chat_bans';
	}

	public static function getKicksTable() {
		global $wpdb;
		return $wpdb->prefix.'wise_chat_kicks';
	}
	
	public static function getActionsTable() {
		global $wpdb;
		
		return $wpdb->prefix.'wise_chat_actions';
	}
	
	public static function getChannelUsersTable() {
		global $wpdb;
		
		return $wpdb->prefix.'wise_chat_channel_users';
	}
	
	public static function getChannelsTable() {
		global $wpdb;
		
		return $wpdb->prefix.'wise_chat_channels';
	}

	public static function getPendingChatsTable() {
		global $wpdb;

		return $wpdb->prefix.'wise_chat_pending_chats';
	}

	/**
	 * Plugin's activation action. Creates database structure (if does not exist), upgrades database structure and
	 * initializes options. Supports WordPress multisite.
	 *
	 * @param boolean $networkWide True if it is a network activation - if so, run the activation function for each blog id
	 */
	public static function activate($networkWide) {
		global $wpdb;

		if (self::registerEngine()) {
			self::$forceDefaultOptions = ['ajax_engine' => 'gold'];
		}

		$role = get_role('administrator');
		$role->add_cap(WiseChatSettings::CAPABILITY, true);

		if (function_exists('is_multisite') && is_multisite()) {
			if ($networkWide) {
				$oldBlogID = $wpdb->blogid;
				$blogIDs = $wpdb->get_col("SELECT blog_id FROM $wpdb->blogs");
				foreach ($blogIDs as $blogID) {
					switch_to_blog($blogID);
					self::doActivation();
				}
				switch_to_blog($oldBlogID);
				return;
			}
		}
		self::doActivation();
	}

	/**
	 * Executed when admin creates a site in mutisite installation.
	 *
	 * @param integer $blogID
	 * @param integer $userID
	 * @param string $domain
	 * @param string $path
	 * @param string $siteID
	 * @param mixed $meta
	 */
	public static function newBlog($blogID, $userID, $domain, $path, $siteID, $meta) {
		global $wpdb;

		if (is_plugin_active_for_network(WISE_CHAT_SLUG.'/wise-chat-core.php')) {
			$oldBlogID = $wpdb->blogid;
			switch_to_blog($blogID);
			self::doActivation();
			switch_to_blog($oldBlogID);
		}
	}

	/**
	 * Executed when admin deletes a site in mutisite installation.
	 *
	 * @param int $blogID Blog ID
	 * @param bool $drop True if blog's table should be dropped. Default is false.
	 */
	public static function deleteBlog($blogID, $drop) {
		global $wpdb;

		$oldBlogID = $wpdb->blogid;
		switch_to_blog($blogID);
		self::doUninstall('deleteblog_'.$blogID);
		switch_to_blog($oldBlogID);
	}

	private static function doActivation() {
		global $wpdb, $user_level, $sac_admin_user_level;
		
		if ($user_level < $sac_admin_user_level) {
			return;
		}

		$charsetCollate = $wpdb->get_charset_collate();

		$tableName = self::getUsersTable();
		$sql = "CREATE TABLE ".$tableName." (
				id bigint(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
				wp_id bigint(11),
				name text NOT NULL,
				session_id text NOT NULL,
				avatar_url text,
				ip text,
				created bigint(11) DEFAULT '0' NOT NULL,
				data text
		) $charsetCollate;";
		require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
		dbDelta($sql);
		
		$tableName = self::getMessagesTable();
		$sql = "CREATE TABLE ".$tableName." (
				id mediumint(7) NOT NULL AUTO_INCREMENT PRIMARY KEY,
				time bigint(11) DEFAULT '0' NOT NULL, 
				admin boolean not null default 0,
				user tinytext NOT NULL,
				user_id bigint(11),
				chat_user_id bigint(11),
				avatar_url text,
				chat_recipient_id bigint(11),
				channel text NOT NULL,
				text text NOT NULL, 
				ip text NOT NULL
		) $charsetCollate;";
		require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
		dbDelta($sql);
		// drop column: channel_user_id
		
		// remove legacy messages:
		$wpdb->get_results('DELETE FROM '.$tableName.' WHERE text = "__user_ping";');
		
		$tableName = self::getBansTable();
		$sql = "CREATE TABLE " . $tableName . " (
				id mediumint(7) NOT NULL AUTO_INCREMENT PRIMARY KEY, 
				time bigint(11) DEFAULT '0' NOT NULL,
				created bigint(11) DEFAULT '0' NOT NULL,
				ip text NOT NULL
		) $charsetCollate;";
		require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
		dbDelta($sql);

		$tableName = self::getKicksTable();
		$sql = "CREATE TABLE " . $tableName . " (
				id mediumint(7) NOT NULL AUTO_INCREMENT PRIMARY KEY,
				last_user_name text NOT NULL,
				created bigint(11) DEFAULT '0' NOT NULL,
				ip text NOT NULL
		) $charsetCollate;";
		require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
		dbDelta($sql);
		
		$tableName = self::getActionsTable();
		$sql = "CREATE TABLE " . $tableName . " (
				id mediumint(7) NOT NULL AUTO_INCREMENT PRIMARY KEY,
				time bigint(11) DEFAULT '0' NOT NULL,
				user_id bigint(11),
				command text NOT NULL
		) $charsetCollate;";
		require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
		dbDelta($sql);
		// drop column: user
		
		$tableName = self::getChannelUsersTable();
		$sql = "CREATE TABLE " . $tableName . " (
				id mediumint(7) NOT NULL AUTO_INCREMENT PRIMARY KEY,
				channel_id bigint(11),
				user_id bigint(11),
				active boolean not null default 1,
				last_activity_time bigint(11) DEFAULT '0' NOT NULL
		) $charsetCollate;";
		require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
		dbDelta($sql);
		// drop column: user, channel, session_id, ip
		
		$tableName = self::getChannelsTable();
		$sql = "CREATE TABLE " . $tableName . " (
				id mediumint(7) NOT NULL AUTO_INCREMENT PRIMARY KEY,
				name text NOT NULL,
				password text
		) $charsetCollate;";
		require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
		dbDelta($sql);

		$tableName = self::getPendingChatsTable();
		$sql = "CREATE TABLE " . $tableName . " (
				id mediumint(7) NOT NULL AUTO_INCREMENT PRIMARY KEY,
				channel_id bigint(11),
				user_id bigint(11),
				recipient_id bigint(11),
				message_id bigint(11),
				time bigint(11) DEFAULT '0' NOT NULL,
				checked boolean not null default 0
		) $charsetCollate;";
		require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
		dbDelta($sql);
		
		// set default options after installation:
		/** @var WiseChatSettings $settings */
		$settings = WiseChatContainer::get('WiseChatSettings');
		$settings->setDefaultSettings(self::$forceDefaultOptions);

		self::upgradeMessagesTableCharset();
	}

	private static function upgradeMessagesTableCharset()
	{
		global $wpdb;

		$convert = false;
		if (method_exists($wpdb, 'get_col_charset')) {
			$convert = $wpdb->get_col_charset(self::getMessagesTable(), 'text') === 'utf8';
		} else {
			$results = $wpdb->get_results(sprintf("SHOW CREATE TABLE %s;", self::getMessagesTable()), ARRAY_N);
			if (count($results) > 0 && count($results[0]) > 0) {
				$convert = preg_match("/CHARSET=utf8 /m", $results[0][1]) > 0;
			}
		}

		if ($convert && $wpdb->charset !== 'utf8') {
			$charsetCollate = '';

			if (!empty($wpdb->charset)) {
				$charsetCollate = "CHARACTER SET ".$wpdb->charset;
			}
			if (!empty($wpdb->collate)) {
				$charsetCollate .= " COLLATE ".$wpdb->collate;
			}

			if ($charsetCollate) {
				$wpdb->query(sprintf('ALTER TABLE %s CONVERT TO %s;', self::getMessagesTable(), $charsetCollate));
			}
		}
	}

	/**
	 * Plugin's deactivation action.
	 */
	public static function deactivate() {
		global $wpdb, $user_level, $sac_admin_user_level;

		$role = get_role('administrator');
		$role->remove_cap(WiseChatSettings::CAPABILITY);
		
		if ($user_level < $sac_admin_user_level) {
			return;
		}
	}

	/**
	 * Plugin's uninstall action. Deletes all database tables and plugin's options.
	 * Supports WordPress multisite.
	 */
	public static function uninstall() {
		global $wpdb;

		if (function_exists('is_multisite') && is_multisite()) {
			$oldBlogID = $wpdb->blogid;
			$blogIDs = $wpdb->get_col("SELECT blog_id FROM $wpdb->blogs");
			foreach ($blogIDs as $blogID) {
				switch_to_blog($blogID);
				self::doUninstall();
			}
			switch_to_blog($oldBlogID);
			return;
		}
		self::doUninstall();
	}

	private static function doUninstall($refererCheck = null) {
		if (!current_user_can('activate_plugins')) {
			return;
		}
		if ($refererCheck !== null) {
			check_admin_referer($refererCheck);
		}
        
        global $wpdb;
		
		// remove all messages and related images:
        /** @var WiseChatMessagesService $messagesService */
		$messagesService = WiseChatContainer::get('services/WiseChatMessagesService');
        $messagesService->deleteAll();
		
		$tableName = self::getMessagesTable();
		$sql = "DROP TABLE IF EXISTS {$tableName};";
		$wpdb->query($sql);
		
		$tableName = self::getBansTable();
		$sql = "DROP TABLE IF EXISTS {$tableName};";
		$wpdb->query($sql);

		$tableName = self::getKicksTable();
		$sql = "DROP TABLE IF EXISTS {$tableName};";
		$wpdb->query($sql);

		$tableName = self::getActionsTable();
		$sql = "DROP TABLE IF EXISTS {$tableName};";
		$wpdb->query($sql);
		
		$tableName = self::getChannelUsersTable();
		$sql = "DROP TABLE IF EXISTS {$tableName};";
		$wpdb->query($sql);
		
		$tableName = self::getChannelsTable();
		$sql = "DROP TABLE IF EXISTS {$tableName};";
		$wpdb->query($sql);

		$tableName = self::getUsersTable();
		$sql = "DROP TABLE IF EXISTS {$tableName};";
		$wpdb->query($sql);
		
		WiseChatOptions::getInstance()->dropAllOptions();

		self::unregisterEngine();
	}

	/**
	 * Registers MU plugin to support Gold engine.
	 *
	 * @return bool
	 */
	public static function registerEngine() {
		$engineContent = file_get_contents(dirname(__FILE__).DIRECTORY_SEPARATOR.'setup'.DIRECTORY_SEPARATOR.self::GOLD_ENGINE_MU_PLUGIN);
		$engineContent = str_replace(['{{PLUGIN_DIR}}', '{{PLUGIN_NAME}}'], [WISE_CHAT_ROOT, WISE_CHAT_NAME], $engineContent);
		$mustUsePluginDir = rtrim(WPMU_PLUGIN_DIR, '/');
		$mustUsePluginPath = $mustUsePluginDir.'/'.self::GOLD_ENGINE_MU_PLUGIN;
		try {
			if (file_exists($mustUsePluginPath) && md5($engineContent) === md5_file($mustUsePluginPath)) {
				return true;
			}

			if (!is_dir($mustUsePluginDir)) {
				$dirMade = @mkdir($mustUsePluginDir);
				if (!$dirMade) {
					$error = error_get_last();
					error_log(sprintf('Unable to create engine directory: %s', $error['message']));
					return false;
				}
			}

			if (!is_writable($mustUsePluginDir)) {
				error_log('MU-plugin directory is not writable.');
				return false;
			}

			$loaderWritten = @file_put_contents($mustUsePluginPath, $engineContent);
			if (!$loaderWritten) {
				$error = error_get_last();
				error_log(sprintf('Unable to install the engine: %s', $error['message']));
				return false;
			}
		} catch (Exception $e) {
			error_log('Unable to install the engine: '.$e->getMessage());
		}

		return true;
    }

	/**
	 * Removes the engine.
	 */
    public static function unregisterEngine() {
		try {
			$mustUsePluginDir = rtrim(WPMU_PLUGIN_DIR, '/');
			$mustUsePluginPath = $mustUsePluginDir.'/'.self::GOLD_ENGINE_MU_PLUGIN;

			if (!file_exists($mustUsePluginPath)) {
				return;
			}

			$removed = @unlink($mustUsePluginPath);

			if (!$removed) {
				$error = error_get_last();
				error_log(sprintf('Unable to remove the engine: %s', $error['message']));
			}
		} catch (Exception $e) {
			error_log('Unable to delete the engine: '.$e->getMessage());
		}
	}
}