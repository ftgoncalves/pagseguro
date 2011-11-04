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

App::uses('HttpSocket', 'Network/Http');
App::uses('Xml', 'Utility');

class NotificationsComponent extends Component {

	public $timeout = 20;

	public $pgURI = 'https://ws.pagseguro.uol.com.br/v2/transactions/notifications/';

	/**
	 * 
	 * 
	 * @var Controller
	 */
	protected $Controller = null;

	public $__config = array();

	public $notificationCode = null;
	
	/**
	 * Construtor padrão
	 *
	 * @param ComponentCollection $collection
	 * @param array $settings
	 */
	public function __construct(ComponentCollection $collection, $settings = array()) {
		parent::__construct($collection, $settings);
	}

	public function initialize(&$controller) {
		$this->Controller = $controller;

		if (Configure::read('PagSeguro') != false && is_array(Configure::read('PagSeguro'))) {
			$this->__config = array_merge($this->__config, Configure::read('PagSeguro'));
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

	/**
	 * 
	 */
	public function isNotification() {
		if ($this->Controller->request->is('post')) {
			if ($this->Controller->request->referer())
				return true;
		}
		
		return false;
	}

	/**
	 * 
	 */
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

	/**
	 * 
	 * @param string $code
	 */
	public function getStatus($code) {
		
		$HttpSocket = new HttpSocket(array('timeout' => $this->timeout));

		$response = $HttpSocket->get($this->pgURI . $code, "email={$__config['email']}&token={$__config['token']}");
		return $this->__status($response);
	}

	/**
	 * 
	 * @param string $response
	 */
	private function __status($response) {
		return Xml::toArray(Xml::build($response['body']));
	}

	/**
	 * 
	 * @param string $referer
	 * @return boolean
	 */
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

	/**
	 * 
	 */
	private function __configValidates() {
		if (!isset($this->__config['email']))
			trigger_error('Não foi informado o email do vendedor.', E_USER_ERROR);
		if (!isset($this->__config['token']))
			trigger_error('Não foi informado o token.', E_USER_ERROR);
		if (!isset($this->__config['currency']))
			trigger_error('Não foi informado o currency.', E_USER_ERROR);
	}
}