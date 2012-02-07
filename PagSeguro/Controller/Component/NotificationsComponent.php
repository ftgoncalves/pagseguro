<?php
/**
 * Plugin de integração com a API do PagSeguro e CakePHP.
 *
 * PHP versions 5+
 * Copyright 2010-2011, Felipe Theodoro Gonçalves, (http://ftgoncalves.com.br)
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @author	 	  Felipe Theodoro Gonçalves
 * @link          https://github.com/ftgoncalves/pagseguro/
 * @license       MIT License (http://www.opensource.org/licenses/mit-license.php)
 * @version		  1.0
 */
class NotificationsComponent extends Component {

	public $component = array('RequestHandler');

	public $timeout = 20;

	public $pgURI = 'https://ws.pagseguro.uol.com.br/v2/transactions/notifications/';

	public $controller = null;

	public $__config = array();

	public $notificationCode = null;

	public function initialize(&$controller, $settings = array()) {
		$this->controller = $controller;

		if ((Configure::read('pag_seguro') != false || Configure::read('pag_seguro') != null) && is_array(Configure::read('pag_seguro'))) {
			$this->__config = array_merge($this->__config, Configure::read('pag_seguro'));
			$this->__configValidates();
		}
	}

	/**
	 *
	 * Força configurações em tempo de execução
	 * @param array $config
	 */
	public function config($config) {
		$this->__config = array_merge($this->__config, $config);
		$this->__configValidates();
	}

	public function isNotification() {
		if ($this->RequestHandler->isPost()) {
			if ($this->RequestHandler->getReferer())
				return true;
			else
				return false;
		} else
			return false;
	}

	public function getNotification() {
		if (
			isset($_POST['notificationCode']) &&
			!empty($_POST['notificationCode']) &&
			isset($_POST['notificationType']) &&
			!empty($_POST['notificationType']) &&
			$_POST['notificationType'] == 'transaction'
		) {
			$this->notificationCode = $_POST['notificationCode'];
			return $this->getStatus($_POST['notificationCode']);
		} else
			return null;
	}

	public function getStatus($code) {
		App::import('Core', 'HttpSocket');
		$HttpSocket = new HttpSocket(array(
			'timeout' => $this->timeout
		));

		$response = $HttpSocket->get($this->pgURI . $code, "email={$__config['email']}&token={$__config['token']}");
		return $this->__status($response);
	}

	private function __status($response) {
		App::import('Core', 'Xml');
		$xml = new xml($res);
		return $xml->toArray();
	}

	private function __refererValidate($referer) {
		if (
			$referer == 'http://pagseguro.uol.com.br/' ||
			$referer == 'http://pagseguro.uol.com.br' ||
			$referer == 'pagseguro.uol.com.br'
		)
			return true;
		else
			return false;
	}

	private function __configValidates() {
		if (!isset($this->__config['email']))
			trigger_error('Não foi informado o email do vendedor.', E_USER_ERROR);
		if (!isset($this->__config['token']))
			trigger_error('Não foi informado o token.', E_USER_ERROR);
		if (!isset($this->__config['currency']))
			trigger_error('Não foi informado o currency.', E_USER_ERROR);
	}
}