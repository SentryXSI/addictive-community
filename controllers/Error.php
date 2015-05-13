<?php

## -------------------------------------------------------
#  ADDICTIVE COMMUNITY
## -------------------------------------------------------
#  Created by Brunno Pleffken Hosti
#  http://github.com/brunnopleffken/addictive-community
#
#  File: Error.php
#  Release: v1.0.0
#  Copyright: (c) 2015 - Addictive Software
## -------------------------------------------------------

class Error extends Application
{
	/**
	 * --------------------------------------------------------------------
	 * VIEW: ERROR PAGE
	 * --------------------------------------------------------------------
	 */
	public function Main()
	{
		// Get type of error
		$type = Html::Request("t");

		// Error list
		$errors = array(
			'not_allowed' => array(
				Html::Notification(i18n::Translate("E_MESSAGE_NOT_ALLOWED"), "warning", true), "login"
			),
			'validated' => array(
				Html::Notification(i18n::Translate("E_MESSAGE_VALIDATED"), "success", true), "login"
			),
			'protected_room' => array(
				Html::Notification(i18n::Translate("E_MESSAGE_PROTECTED"), "failure", true), "protected"
			)
		);

		// Is this an error, or just a notification message? Change title!
		if(strpos($errors[$type][0], "success")) {
			$title = i18n::Translate("E_SUCCESS_TITLE");
		}
		else {
			$title = i18n::Translate("E_ERROR_TITLE");
		}

		// Return variables
		$this->Set("community_name", $this->config['general_community_name']);
		$this->Set("title", $title);
		$this->Set("error", $errors[$type][0]);
		$this->Set("action", $errors[$type][1]);
	}
}