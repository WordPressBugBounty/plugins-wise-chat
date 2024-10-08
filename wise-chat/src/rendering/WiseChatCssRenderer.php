<?php

/**
 * Wise Chat CSS styles rendering.
 *
 * @author Kainex <contact@kainex.pl>
 */
class WiseChatCssRenderer {
	
	/**
	* @var WiseChatOptions
	*/
	private $options;
	
	/**
	* @var string
	*/
	private $containerId;
	
	/**
	* @var array
	*/
	private $definitions;

	/**
	* @var array
	*/
	private $mediaDefinitions;
	
	public function __construct() {
		$this->options = WiseChatOptions::getInstance();
	}
	
	/**
	* Returns CSS styles definition for the plugin.
	*
	* @param string $containerId ID of the chat HTML container
	*
	* @return string HTML source
	*/
	public function getCssDefinition($containerId) {
		$sidebarMode = $this->options->getIntegerOption('mode', 0) === 1;
		$this->containerId = $containerId;
		$this->definitions = array();
		$this->mediaDefinitions = array();

		$this->addDefinition('.wcBody .wcMessagesArea .wcTabsContainer', 'background_color_chat', 'background-color');
		$this->addDefinition('.wcBody .wcBrowserArea', 'background_color_chat', 'background-color');
		$this->addDefinition('.wcDesktop .wcBrowser', 'background_color_chat', 'background-color');
		$this->addDefinition('.wcMobile .wcTabs', 'background_color_chat', 'background-color');
		$this->addDefinition('.wcSidebar .wcColumn .wcContent.wcBrowserContent .wcCustomizations', 'background_color_chat', 'background-color');

		$this->addDefinition('.wcBody .wcMessagesArea .wcTabsContainer .wcTabs .wcTab .wcName', 'text_color_chat', 'color');
		$this->addDefinition('.wcDesktop .wcBrowser *', 'text_color_chat', 'color');

		if ($this->options->isOptionNotEmpty('text_size_chat')) {
			$this->addDefinition('', 'text_size_chat', 'font-size');
			$this->addRawDefinition('*', 'font-size', 'inherit');
		}


		$this->addDefinition('.wcChannel .wcMessages', 'background_color', 'background-color');
		$this->addDefinition('.wcChannel .wcMessages .wcMessage', 'background_color', 'background-color');
		$this->addDefinition('.wcChannel .wcMessages .wcMessage .wcContent', 'background_color', 'background-color');
		$this->addLengthDefinition('.wcChannel .wcMessages .wcMessage .wcRowBody .wcContent .wcImage.wcTenorGIF', 'gifs_size', 'max-width');
		$this->addLengthDefinition('.wcChannel .wcMessages .wcMessage .wcRowBody .wcContent .wcImage.wcTenorGIF', 'gifs_size', 'max-height');
		$this->addDefinition('.wcMessages *', 'text_color', 'color');

		$this->addDefinition('.wcMessage .wcUser', 'text_color_user', 'color');
		$this->addDefinition('.wcMessage.wcWpUser .wcUser', 'text_color_logged_user', 'color');

		$this->addDefinition('.wcChannelInput', 'background_color_input', 'background-color');
		$this->addDefinition('.wcDesktop .wcBody .wcMessagesArea .wcCustomizations', 'background_color_input', 'background-color');

		$this->addDefinition('.wcChannelInput *', 'text_color_input_field', 'color');
		$this->addDefinition('.wcDesktop .wcBody .wcMessagesArea .wcCustomizations *', 'text_color_input_field', 'color');


		$this->addDefinition('.wcBody .wcBrowserArea', 'background_color_users_list', 'background-color');
		$this->addDefinition('.wcDesktop .wcBrowser', 'background_color_users_list', 'background-color');
		$this->addDefinition('.wcDesktop .wcBrowser *', 'text_color_users_list', 'color');

		if ($this->options->isOptionNotEmpty('text_size_users_list')) {
			$this->addDefinition('.wcDesktop .wcBrowser', 'text_size_users_list', 'font-size');
			$this->addRawDefinition('.wcDesktop .wcBrowser *', 'font-size', 'inherit');
		}

		if ($this->options->isOptionNotEmpty('text_size')) {
			$this->addDefinition('.wcChannel .wcMessages', 'text_size', 'font-size');
			$this->addRawDefinition('.wcChannel .wcMessages *', 'font-size', 'inherit');
		}

		if ($sidebarMode) {
			$this->addRawDefinition('', '--wc-z-index', $this->options->getIntegerOption('fb_z_index', 200000));
			$this->addRawDefinition('.popup-overlay.wcPopup-overlay', 'z-index', ($this->options->getIntegerOption('fb_z_index', 200000) + 10).' !important', '#popup-root');
			$this->addRawDefinition('.popup-content.wcPopup-content', 'z-index', ($this->options->getIntegerOption('fb_z_index', 200000) + 10).' !important', '#popup-root');

			if ($this->options->isOptionNotEmpty('fb_users_list_top_offset')) {
				$this->addLengthDefinition('.wcSidebar', 'fb_users_list_top_offset', 'top');
			}
			if ($this->options->isOptionNotEmpty('fb_bottom_offset')) {
				if ($this->options->isOptionNotEmpty('fb_bottom_offset_threshold')) {
					$this->addMediaDefinition('only screen and (max-width: '.$this->options->getIntegerOption('fb_bottom_offset_threshold').'px)', '.wcSidebar', 'bottom', $this->options->getIntegerOption('fb_bottom_offset').'px');
				} else {
					$this->addLengthDefinition('.wcSidebar', 'fb_bottom_offset', 'bottom');
				}
			}
			if ($this->options->isOptionNotEmpty('fb_browser_width')) {
				$this->addLengthDefinition('.wcSidebar.wcDesktop .wcColumn.wcBrowserColumn', 'fb_browser_width', 'width');
			}
			if ($this->options->isOptionNotEmpty('fb_channel_height')) {
				$this->addLengthDefinition('.wcSidebar.wcDesktop .wcColumn .wcContent.wcChannelContent', 'fb_channel_height', 'max-height');
				$this->addLengthDefinition('.wcSidebar.wcMobile .wcColumn .wcMobileContainer', 'fb_channel_height', 'max-height');
			}
			if ($this->options->isOptionNotEmpty('fb_channel_width')) {
				$this->addLengthDefinition('.wcSidebar.wcDesktop .wcColumn .wcContent.wcChannelContent', 'fb_channel_width', 'width');
				$this->addLengthDefinition('.wcSidebar.wcDesktop .wcColumn .wcContent.wcAuthContent', 'fb_channel_width', 'width');
			}
		} else {
			$this->addLengthDefinition('', 'chat_width', 'width');
			$this->addRawDefinition('', 'height', $this->options->getOption('chat_height', '500px'));
			$this->addLengthDefinition('.wcClassic.wcDesktop .wcBody .wcMessagesArea .wcGrid .wcGridChannel', 'classic_grid_height', 'height');
			$this->addUsersListWidthDefinition();
		}
		
		return $this->getDefinitions();
	}

	/**
	 * @param string $mediaQuery
	 * @param string $selector
	 * @param string $property
	 * @param string|integer $value
	 * @param string|null $rootSelector
	 */
	private function addMediaDefinition($mediaQuery, $selector, $property, $value, $rootSelector = null) {
		if ($rootSelector === null) {
			$rootSelector = '#'.$this->containerId;
		}
		$this->mediaDefinitions[$mediaQuery][trim($rootSelector.' '.$selector)][] = sprintf("%s: %s;", $property, $value);
	}

	/**
	* Returns custom CSS styles definition for the plugin.
	*
	* @return string HTML source
	*/
	public function getCustomCssDefinition() {
		if ($this->options->isOptionNotEmpty('custom_styles')) {
			return sprintf("<style type='text/css'>\n%s\n</style>", $this->options->getOption('custom_styles'));
		}
		
		return '';
	}
	
	/**
	* Adds a single style definition.
	*
	* @param string $cssSelector
	* @param string $property
	* @param string $cssProperty
	*/
	private function addDefinition($cssSelector, $property, $cssProperty) {
		if ($this->options->isOptionNotEmpty($property)) {
			$this->addRawDefinition($cssSelector, $cssProperty, $this->options->getOption($property));
		}
	}

	/**
	 * Adds a raw style definition.
	 *
	 * @param string $cssSelector
	 * @param string $property
	 * @param string $value
	 * @param string|null $rootSelector
	 */
	private function addRawDefinition($cssSelector, $property, $value, $rootSelector = null) {
		if ($rootSelector === null) {
			$rootSelector = '#'.$this->containerId;
		}
		$fullCssSelector = sprintf("%s %s", $rootSelector, $cssSelector);
		$this->definitions[$fullCssSelector][] = sprintf("%s: %s;", $property, $value);
	}
	
	/**
	* Adds single length style definition.
	*
	* @param string $cssSelector
	* @param string $lengthProperty
	* @param string $cssProperty
	* @param boolean $acceptOnlyPxUnit
	*/
	private function addLengthDefinition($cssSelector, $lengthProperty, $cssProperty, $acceptOnlyPxUnit = false) {
		if ($this->options->isOptionNotEmpty($lengthProperty)) {
			$value = $this->options->getOption($lengthProperty);
			if ($acceptOnlyPxUnit) {
				$value = str_replace('%', '', $value);
			}
			if (preg_match('/^\d+$/', $value)) {
				$value .= 'px';
			}
			if (preg_match('/^\d+((px)|%)$/', $value)) {
				$this->addRawDefinition($cssSelector, $cssProperty, $value);
			}
		}
	}

	private function addUsersListWidthDefinition() {
		if ($this->options->isOptionNotEmpty('users_list_width')) {
			$width = $this->options->getIntegerOption('users_list_width');
			if ($width > 1 && $width < 99) {
				$this->addRawDefinition('.wcClassic.wcDesktop .wcBody .wcBrowserArea', 'min-width', $width.'%');
				$this->addRawDefinition('.wcClassic.wcDesktop .wcBody .wcBrowserArea', 'flex-basis', $width.'%');
			}
		}
	}
	
	/**
	* Returns rendered styles definition. 
	*
	* @return string HTML source
	*/
	private function getDefinitions() {
		$html = '';
		foreach ($this->definitions as $cssSelector => $stylesList) {
			$html .= "$cssSelector { ".implode(" ", $stylesList)." }\n";
		}

		foreach ($this->mediaDefinitions as $mediaQuery => $selectors) {
			$html .= "@media $mediaQuery {\n";

			foreach ($selectors as $cssSelector => $stylesList) {
				$html .= "$cssSelector { ".implode(" ", $stylesList)." }\n";
			}

			$html .= "}\n";
		}
		
		return sprintf('<style type="text/css">%s</style>', $html);
	}
}