<?php

namespace Froxlor\UI;

class Response
{

	/**
	 * Sends an header ( 'Location ...' ) to the browser.
	 *
	 * @param string $destination
	 *        	Destination
	 * @param array $get_variables
	 *        	Get-Variables
	 * @param boolean $isRelative
	 *        	if the target we are creating for a redirect
	 *        	should be a relative or an absolute url
	 *        	
	 * @return boolean false if params is not an array
	 */
	public static function redirectTo($destination, $get_variables = null, $isRelative = true)
	{
		global $s;

		if (is_array($get_variables)) {
			if (isset($get_variables['s'])) {
				$linker = new Linker($destination, $get_variables['s']);
			} else {
				$linker = new Linker($destination, $s);
			}

			foreach ($get_variables as $key => $value) {
				$linker->add($key, $value);
			}

			if ($isRelative) {
				$linker->protocol = '';
				$linker->hostname = '';
				$path = './';
			} else {
				if (isset($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) == 'on') {
					$linker->protocol = 'https';
				} else {
					$linker->protocol = 'http';
				}

				$linker->hostname = $_SERVER['HTTP_HOST'];

				if (dirname($_SERVER['PHP_SELF']) == '/') {
					$path = '/';
				} else {
					$path = dirname($_SERVER['PHP_SELF']) . '/';
				}
				$linker->filename = $path . $destination;
			}
			header('Location: ' . $linker->getLink());
			exit();
		} elseif ($get_variables == null) {
			if ($isRelative) {
				$linker = new Linker($destination, $s);
			} else {
				$linker = new Linker($destination);
			}
			header('Location: ' . $linker->getLink());
			exit();
		}

		return false;
	}

	/**
	 * Prints one ore more errormessages on screen
	 *
	 * @param array $errors
	 *        	Errormessages
	 * @param string $replacer
	 *        	A %s in the errormessage will be replaced by this string.
	 * @param bool $throw_exception
	 *
	 * @author Florian Lippert <flo@syscp.org>
	 * @author Ron Brand <ron.brand@web.de>
	 */
	public static function standard_error($errors = '', $replacer = '', $throw_exception = false)
	{
		global $lng;

		$_SESSION['requestData'] = $_POST;
		$replacer = htmlentities($replacer);

		if (!is_array($errors)) {
			$errors = array(
				$errors
			);
		}

		$link_ref = '';
		if (isset($_SERVER['HTTP_REFERER']) && strpos($_SERVER['HTTP_REFERER'], $_SERVER['HTTP_HOST']) !== false) {
			$link_ref = htmlentities($_SERVER['HTTP_REFERER']);
		}

		$error = '';
		foreach ($errors as $single_error) {
			if (isset($lng['error'][$single_error])) {
				$single_error = $lng['error'][$single_error];
				$single_error = strtr($single_error, array(
					'%s' => $replacer
				));
			} else {
				$error = 'Unknown Error (' . $single_error . '): ' . $replacer;
				break;
			}

			if (empty($error)) {
				$error = $single_error;
			} else {
				$error .= ' ' . $single_error;
			}
		}

		if ($throw_exception) {
			throw new \Exception(strip_tags($error), 400);
		}
		\Froxlor\UI\Panel\UI::twigBuffer('misc/alert.html.twig', [
			'type' => 'danger',
			'btntype' => 'light',
			'heading' => $lng['error']['error'],
			'alert_msg' => $error,
			'redirect_link' => $link_ref
		]);
		\Froxlor\UI\Panel\UI::twigOutputBuffer();
		exit;
	}

	public static function dynamic_error($message)
	{
		global $lng;
		$_SESSION['requestData'] = $_POST;
		$link_ref = '';
		if (isset($_SERVER['HTTP_REFERER']) && strpos($_SERVER['HTTP_REFERER'], $_SERVER['HTTP_HOST']) !== false) {
			$link_ref = htmlentities($_SERVER['HTTP_REFERER']);
		}

		\Froxlor\UI\Panel\UI::twigBuffer('misc/alert.html.twig', [
			'type' => 'danger',
			'btntype' => 'light',
			'heading' => $lng['error']['error'],
			'alert_msg' => $message,
			'redirect_link' => $link_ref
		]);
		\Froxlor\UI\Panel\UI::twigOutputBuffer();
		exit;
	}

	/**
	 * Prints one ore more errormessages on screen
	 *
	 * @param array $success_message
	 *        	Errormessages
	 * @param string $replacer
	 *        	A %s in the errormessage will be replaced by this string.
	 * @param array $params
	 * @param bool $throw_exception
	 *
	 * @author Florian Lippert <flo@syscp.org>
	 */
	public static function standard_success($success_message = '', $replacer = '', $params = array(), $throw_exception = false)
	{
		global $s, $lng;

		if (isset($lng['success'][$success_message])) {
			$success_message = strtr($lng['success'][$success_message], array(
				'%s' => htmlentities($replacer)
			));
		}

		if ($throw_exception) {
			throw new \Exception(strip_tags($success_message), 200);
		}

		if (is_array($params) && isset($params['filename'])) {
			$redirect_url = $params['filename'] . '?s=' . $s;
			unset($params['filename']);

			foreach ($params as $varname => $value) {
				if ($value != '') {
					$redirect_url .= '&amp;' . $varname . '=' . $value;
				}
			}
		} else {
			$redirect_url = '';
		}

		\Froxlor\UI\Panel\UI::twigBuffer('misc/alert.html.twig', [
			'type' => 'success',
			'btntype' => 'light',
			'heading' => $lng['success']['success'],
			'alert_msg' => $success_message,
			'redirect_link' => $redirect_url
		]);
		\Froxlor\UI\Panel\UI::twigOutputBuffer();
		exit;
	}
}
