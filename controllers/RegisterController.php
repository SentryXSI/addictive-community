<?php

## -------------------------------------------------------
#  ADDICTIVE COMMUNITY
## -------------------------------------------------------
#  Created by Brunno Pleffken Hosti
#  https://github.com/addictivehub/addictive-community
#
#  File: Register.php
#  License: GPLv2
#  Copyright: (c) 2017 - Addictive Community
## -------------------------------------------------------

namespace AC\Controllers;

use \AC\Kernel\Database;
use \AC\Kernel\Email;
use \AC\Kernel\Html;
use \AC\Kernel\Http;
use \AC\Kernel\i18n;
use \AC\Kernel\Text;

class Register extends Application
{
	/**
	 * --------------------------------------------------------------------
	 * VIEW: REGISTER NEW MEMBER
	 * --------------------------------------------------------------------
	 */
	public function index()
	{
		// Get step
		$step = (!Http::request("step", true)) ? 1 : Http::request("step", true);

		// Notifications
		$message_id = Http::request("m", true);
		$notification = array("",
			Html::notification(i18n::translate("register.notification.required"), "failure", true),
			Html::notification(i18n::translate("register.notification.does_not_match"), "failure", true),
			Html::notification(i18n::translate("register.notification.username"), "failure", true),
			Html::notification(i18n::translate("register.notification.already_exists"), "failure", true),
			Html::notification(i18n::translate("register.notification.captcha"), "failure", true)
		);

		// Page info
		$page_info['title'] = i18n::translate("register.title");
		$page_info['bc'] = array(i18n::translate("register.title"));
		$this->Set("page_info", $page_info);

		// Return variables
		$this->Set("step", $step);
		$this->Set("notification", $notification[$message_id]);
		$this->Set("general_security_validation", $this->Core->config['general_security_validation']);
		$this->Set("is_captcha", $this->Core->config['general_security_captcha']);
		$this->Set("is_register_disabled", $this->Core->config['general_disable_registrations']);


	}

	/**
	 * --------------------------------------------------------------------
	 * PROCESS REGISTER
	 * --------------------------------------------------------------------
	 */
	public function signUp()
	{
		$this->layout = false;
		$_SESSION['Register']['Username']=Http::request("username");
		$_SESSION['Register']['Email']=Http::request("email");
		// Check if the entered CAPTCHA matches the registered in the session
		if($this->Core->config['general_security_captcha']) {
			if(Http::request("captcha") != $_SESSION['captcha']) {
				$this->Core->redirect("register?step=2&m=5");
			}
			else {
				unset($_SESSION['captcha']);
			}
		}

		// Check if user has entered with username, password and e-mail address
		if(!Http::request("username") || !Http::request("email") || !Http::request("password")) {
			$this->Core->Request("register?step=2&m=1");
		}

		// Check if passwords are equal
		if(Http::request("password") != Http::request("password_conf")) {
			$this->Core->Request("register?step=2&m=2");
		}

		// Check username length
		if(strlen(Http::request("username")) < 3 || strlen(Http::request("username")) > 20) {
			$this->Core->Request("register?step=2&m=3");
		}
		unset($_SESSION['Register']['Username']);
		unset($_SESSION['Register']['Email']);


		// Check if Require Validation is TRUE in community settings
		$usergroup = ($this->Core->config['general_security_validation']) ? 6 : 3;

		// Hash
		$salt = array(
			"hash" => $this->Core->config['security_salt_hash'],
			"key"  => $this->Core->config['security_salt_key']
		);

		// Build new member info array
		$register_info = array(
			"username"      => Http::request("username"),
			"password"      => Text::encrypt(Http::request("password"), $salt),
			"email"         => Http::request("email"),
			"hide_email"    => 1,
			"ip_address"    => $_SERVER['REMOTE_ADDR'],
			"joined"        => time(),
			"usergroup"     => $usergroup,
			"photo_type"    => "gravatar",
			"posts"         => 0,
			"template"      => $this->Core->config['template'],
			"theme"         => $this->Core->config['theme'],
			"language"      => $this->Core->config['language'],
			"time_offset"   => $this->Core->config['date_default_offset'],
			"dst"           => 0,
			"show_birthday" => 1,
			"show_gender"   => 1,
			"token"         => md5(microtime())
		);

		// Check if username or e-mail address already exists
		Database::query("SELECT username, email FROM c_members
				WHERE username = '{$register_info['username']}' OR email = '{$register_info['email']}';");

		if(Database::rows() > 0) {
			$this->Core->redirect("register?step=2&m=4");
		}

		// Save new member in the Database

		if($register_info['usergroup'] == 6) {
			// REQURE VALIDATION

			// Instance of Email() class
			$Email = new Email($this->Core->config);

			// Insert into database and update stats
			Database::insert("c_members", $register_info);
			$new_member_id = Database::getLastId();
			Database::update("c_stats", "member_count = member_count + 1");

			// Buid e-mail body
			$validation_url = $this->Core->config['general_community_url']
					. "register/validate?m={$new_member_id}&token={$register_info['token']}";

			Database::query("SELECT content FROM c_emails WHERE type = 'validate';");
			$email_raw_content = Database::fetch();

			$email_formatted_content = sprintf($email_raw_content['content'],
				$register_info['username'],
				$this->Core->config['general_community_name'],
				$validation_url
			);

			// Send e-mail
			$Email->sendMail(
				$register_info['email'],
				"[" . $this->Core->config['general_community_name'] . "] New Member Validation",
				$email_formatted_content
			);

			$this->Core->redirect("register?step=3");
		}
		else {
			// DO NOT REQUIRE VALIDATION
			Database::insert("c_members", $register_info);
			Database::update("c_stats", "member_count = member_count + 1");
			$this->Core->redirect("register?step=3");
		}

		exit;
	}

	/**
	 * --------------------------------------------------------------------
	 * VALIDATE NEW MEMBER ACCOUNT
	 * --------------------------------------------------------------------
	 */
	public function validate()
	{
		$this->layout = false;

		// Get member ID
		$member = Http::request("m", true);
		$token  = Http::request("token");

		// Check if user has already validated
		Database::query("SELECT m_id, usergroup, token FROM c_members WHERE m_id = {$member};");
		$validation_count = Database::rows();
		$validation_results = Database::fetch();

		if($validation_count > 0) {
			// Validate usergroup
			if($validation_results['usergroup'] != 6) {
				Html::throwError(i18n::translate("register.done.already_validated"));
			}

			// Validate member's security token
			if($validation_results['token'] != $token) {
				Html::throwError(i18n::translate("register.done.not_match"));
			}

			// Validate and redirect
			Database::update("c_members", "usergroup = '3'", "m_id = '{$member}'");
			$this->Core->redirect("register?step=4");
		}
		else {
			Html::throwError(i18n::translate("register.done.not_found"));
		}
	}

	/**
	 * --------------------------------------------------------------------
	 * SHOW GD CREATED SECURITY IMAGE (A.K.A. CAPTCHA)
	 * --------------------------------------------------------------------
	 */
	public function captcha()
	{
		// Build random word
		$word = "";
		$letters = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz";
		$len = strlen($letters);

		for ($i = 0; $i < 6; $i++) {
			$letter = $letters[mt_rand(0, $len - 1)];
			$word .= $letter;
		}

		// Save random content in a new session with the CAPTCHA key
		$_SESSION['captcha'] = trim($word);

		// JPEG image MIME type
		header("Content-Type: image/jpeg");

		$tmp_x = 140;
		$tmp_y = 20;
		$image_x = 210;
		$image_y = 65;

		$circles = 6;

		$tmp = imagecreatetruecolor($tmp_x, $tmp_y);
		$im  = imagecreatetruecolor($image_x, $image_y);

		$white = ImageColorAllocate($tmp, 255, 255, 255);
		$black = ImageColorAllocate($tmp, 0, 0, 0);

		imagefill($tmp, 0, 0, $white);

		for($i = 1; $i <= $circles; $i++) {
			$values = array(
				0  => mt_rand(0, $tmp_x - 10),
				1  => mt_rand(0, $tmp_y - 3),
				2  => mt_rand(0, $tmp_x - 10),
				3  => mt_rand(0, $tmp_y - 3),
				4  => mt_rand(0, $tmp_x - 10),
				5  => mt_rand(0, $tmp_y - 3),
				6  => mt_rand(0, $tmp_x - 10),
				7  => mt_rand(0, $tmp_y - 3),
				8  => mt_rand(0, $tmp_x - 10),
				9  => mt_rand(0, $tmp_y - 3),
				10 => mt_rand(0, $tmp_x - 10),
				11 => mt_rand(0, $tmp_y - 3),
			);

			// Draw random polygon
			$randomcolor = imagecolorallocate($tmp, mt_rand(100, 255), mt_rand(100, 255), mt_rand(100, 255));
			imagefilledpolygon($tmp, $values, 6, $randomcolor);
		}

		// Render string
		imagestring($tmp, 5, mt_rand(5, 60), mt_rand(0, 5), $word, $black);

		// Distort by resizing
		imagecopyresized($im, $tmp, 0, 0, 0, 0, $image_x, $image_y, $tmp_x, $tmp_y);
		imagedestroy($tmp);

		$black = ImageColorAllocate($im, 0, 0, 0);
		$grey  = ImageColorAllocate($im, 100, 100, 100);

		// Draw random pixel dots
		$random_pixels = $image_x * $image_y / 10;
		for($i = 0; $i < $random_pixels; $i++) {
			ImageSetPixel($im, mt_rand(0, $image_x), mt_rand(0, $image_y), $black);
		}

		$no_x_lines = ($image_x - 1) / 5;

		for($i = 0; $i <= $no_x_lines; $i++) {
			// Vertical lines
			ImageLine($im, $i * $no_x_lines, 0, $i * $no_x_lines, $image_y, $grey);

			// Diagonal lines
			ImageLine($im, $i * $no_x_lines, 0, ($i * $no_x_lines)+$no_x_lines, $image_y, $grey);
		}

		// Draw horizontal lines
		$no_y_lines = ($image_y - 1) / 5;
		for($i = 0; $i <= $no_y_lines; $i++) {
			ImageLine($im, 0, $i * $no_y_lines, $image_x, $i * $no_y_lines, $grey);
		}

		ImageJPEG($im);
		ImageDestroy($im);

		exit;
	}
}
