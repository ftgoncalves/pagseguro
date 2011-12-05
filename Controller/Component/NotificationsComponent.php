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
 * @author       Cauan Cabral
 * @link         https://github.com/ftgoncalves/pagseguro/
 * @license      MIT License (http://www.opensource.org/licenses/mit-license.php)
 * @version      2.0
 */

App::uses('HttpSocket', 'Network/Http');
App::uses('Xml', 'Utility');

class NotificationsComponent extends Component {

	public $timeout = 20;

	public $pgURI = 'https://ws.pagseguro.uol.com.br/v2/transactions/notifications/';

	/**
	 * 
	 * @var Controller
	 */
	protected $Controller = null;

	public $__config = array();

	/**
	 * Código de 39 caracteres que identifica a notificação
	 * recebida
	 * 
	 * @var type string
	 */
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
		$this->Controller =& $controller;

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
	 * Validação de uma notificação recebida
	 * 
	 * @param CakeRequest $request Dados da requisição a ser testada
	 * @return bool Válido ou não
	*/
	public function isNotification($request) {
		if(
			$request->is('post') &&
			strpos($request->referer(), 'pagseguro.uol.com.br') !== false &&
			isset($request->data['notificationCode']) &&
			isset($request->data['notificationType']) &&
			strlen($request->data['notificationCode']) == 39 &&
			$request->data['notificationType'] == 'transaction'
		) {
			$this->notificationCode = $request->data['notificationCode'];
			
			return true;
		}
		
		return false;
	}

	/**
	 * Valida a notificação recebida e requisita da API do PagSeguro a situação de um pagamento,
	 * converte o retorno de XML para Array e então o retorna.
	 * 
	 * @param string $code Código da notificação
	 * @return mixed array com dos dados da notificação em caso de sucesso, null em caso de falha
	 */
	public function getNotification($code = null) {
		if(!empty($code) && is_string($code)) {
			$this->notificationCode = $code;
		}
		
		$HttpSocket = new HttpSocket(array('timeout' => $this->timeout));

		$response = $HttpSocket->get($this->pgURI . $this->notificationCode, "email={$__config['email']}&token={$__config['token']}");
		
		return Xml::toArray(Xml::build($response['body']));
	}

	/**
	 * Valida as configurações, disparando uma exceção quando
	 * forem inválidas.
	 * 
	 * @return void
	 */
	private function __configValidates() {
		if (!isset($this->__config['email']))
			throw new CakeException ('Não foi informado o email do vendedor.');
		if (!isset($this->__config['token']))
			throw new CakeException ('Não foi informado o token.');
		
		// Validação de acordo com API 2.0 do PagSeguro
		if(strlen($this->__config['email']) > 60)
			throw new CakeException ('Email do vendedor extrapola limite de 60 caracteres da API.');
		if(strlen($this->__config['token']) > 32)
			throw new CakeException ('Token extrapola limite de 32 caracteres da API.');
	}
}