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
	 * @param CakeRequest $request
	 * @return bool Valido ou não
	*/
	public function isNotification() {
		return (
			$this->Controller->request->is('post') &&
			strpos($request->referer(), 'pagseguro.uol.com.br') !== false &&
			isset($request->data['notificationCode']) &&
			isset($request->data['notificationType']) &&
			strlen($request->data['notificationCode']) == 39 &&
			$request->data['notificationType'] == 'transaction'
		);
	}

	/**
	 * Valida a notificação recebida e requisita da API do PagSeguro a situação de um pagamento,
	 * converte o retorno de XML para Array e então o retorna.
	 * 
	 * @return mixed array com dos dados da notificação em caso de sucesso, null em caso de falha
	 */
	public function getNotification() {
		if($this->isNotification($this->Controller->request))
		{
			$this->notificationCode = $this->Controller->request->data['notificationCode'];
			
			$HttpSocket = new HttpSocket(array('timeout' => $this->timeout));

			$response = $HttpSocket->get($this->pgURI . $this->notificationCode, "email={$__config['email']}&token={$__config['token']}");
		
			return Xml::toArray(Xml::build($response['body']));
			
		} else
			return null;
	}

	/**
	 * Valida as configurações, disparando um erro fatal quando
	 * forem inválidas.
	 * 
	 * @todo Substituir trigger_error por Exceções
	 * 
	 * @return void
	 */
	private function __configValidates() {
		if (!isset($this->__config['email']))
			trigger_error('Não foi informado o email do vendedor.', E_USER_ERROR);
		if (!isset($this->__config['token']))
			trigger_error('Não foi informado o token.', E_USER_ERROR);
		
		// Validação de acordo com API 2.0 do PagSeguro
		if(strlen($this->__config['email']) > 60)
			trigger_error('Email do vendedor extrapola limite de 60 caracteres da API.', E_USER_ERROR);
		if(strlen($this->__config['token']) > 32)
			trigger_error('Token extrapola limite de 32 caracteres da API.', E_USER_ERROR);
	}
}