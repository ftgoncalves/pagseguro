<?php
App::uses('HttpSocket', 'Network/Http');
App::uses('Xml', 'Utility');
/**
 * Plugin de integração com a API do PagSeguro e CakePHP.
 * 
 * Componente para recuperar e verificar notificação de pagamento
 * para a versão 1 da API do PagSeguro.
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
class LegacyNotificationsComponent extends Component
{
	public $timeout = 20;

	public $pgURI = array(
		'host' => 'pagseguro.uol.com.br',
		'path' => '/pagseguro-ws/checkout/NPI.jhtml',
		'scheme' => 'https',
		'port' => '443'
	);

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
	 * @var string
	 */
	public $transacaoID = null;
	
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
		if($request->is('post') && isset($request->data['TransacaoID'])) {
			$this->transactionID = $request->data['TransacaoID'];
			
			return true;
		}
		
		return false;
	}

	/**
	 * Valida a notificação recebida e requisita da API do PagSeguro a situação de um pagamento,
	 * converte o retorno de XML para Array e então o retorna.
	 * 
	 * @param CakeRequest $request Requisição
	 * @return mixed array com dos dados da notificação em caso de sucesso, null em caso de falha
	 */
	public function getNotification($request) {
		
		$decoded = $this->decode($request->data);
		
		$HttpSocket = new HttpSocket(array('timeout' => $this->timeout));
		
		$extraParams = array(
			'Token' => $this->__config['token'],
			'Comando' => 'Validar'
		);

		$response = $HttpSocket->post($this->pgURI, array_merge($request->data, $extraParams));
		
		if(strtolower($response['body']) != 'verificado') {
			CakeLog::write('debug', "Notificação Legacy com código {$this->transacaoID} não pode ser verificada.");
		}
		
		return $decoded;
	}
	
	/**
	 *
	 * @param array $data
	 * @return array 
	 */
	protected function decode($data) {
		$amount = 0;
		
		// Cálcula total da compra
		for($i = 1; true; $i++)
		{
			if(isset($data['ProdValor_' . $i]))
			{
				$amount += ($data['ProdValor_' . $i] * $data['ProdQuantidade_' . $i]);
			}
		}
		
		$amount += $data['ValorFrete'];
		
		$decoded = array(
			'transaction' => array(
				'code' => $data['TransacaoID'],
				'date' => $data['DataTransacao'],
				'value' => $amount,
				'status' => $data['StatusTransacao']
			)
		);
		
		return $decoded;
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