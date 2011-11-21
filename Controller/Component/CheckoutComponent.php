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
 * @author      Felipe Theodoro Gonçalves
 * @author      Cauan Cabral
 * @link        https://github.com/ftgoncalves/pagseguro/
 * @license     MIT License (http://www.opensource.org/licenses/mit-license.php)
 * @version     2.0
 */
App::uses('HttpSocket', 'Network/Http');
App::uses('Xml', 'Utility');

class CheckoutComponent extends Component {

	/**
	 *
	 * Instancia do Controller
	 * @var Controller
	 */
	protected $Controller = null;

	/**
	 *
	 * Dominio do webserver do PagSeguro
	 * @var String
	 */
	private $__URI = 'ws.pagseguro.uol.com.br';

	/**
	 *
	 * Endereço do ws PagSeguro v2
	 * @var String
	 */
	private $__path = '/v2/checkout/';

	/**
	 *
	 * Charset
	 * @var String
	 */
	public $charset = 'UTF-8';

	/**
	 *
	 * Timeout do post
	 * @var int
	 */
	public $timeout = 20;

	/**
	 *
	 * Endereço para redirecionamento para o checkout do PagSeguro
	 * @var String
	 */
	private $__redirect = 'https://pagseguro.uol.com.br/v2/checkout/payment.html?code=';

	/**
	 *
	 * Atributo contendo as configurações do pagseguro
	 * @var array
	 */
	public $__config = array(
		'currency' => 'BRL'
	);

	/**
	 *
	 * Dados do cliente e endereço de cobrança
	 * @var array
	 */
	public $__shipping = array();

	/**
	 *
	 * atributo com os dados da compra
	 * @var array
	 */
	public $__data = array();
	
	/**
	 * Construtor padrão
	 *
	 * @param ComponentCollection $collection
	 * @param array $settings
	 */
	public function __construct(ComponentCollection $collection, $settings = array()) {
		parent::__construct($collection, $settings);
	}

	/**
	 *
	 * Methodo para setar as configurações defaults do pagseguro
	 * @param Object $controller
	 */
	public function startup(&$controller) {
		$this->Controller =& $controller;

		if ((Configure::read('PagSeguro') != false) && is_array(Configure::read('PagSeguro'))) {
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
	 * Seta as informações de cobrança e id da compra
	 * @param array $data
	 */
	public function setShipping($data) {
		$this->__shipping = $data;
	}

	/**
	 *
	 * Seta os itens da venda
	 * @param array $data
	 */
	public function set($data) {
		$this->__data = $data;
		
		$this->__dataValidates();
	}
	
	/**
	 *
	 * Finaliza a compra.
	 * Recebe o codigo para redirecionamento ou erro.
	 */
	public function finalize() {
		$HttpSocket = new HttpSocket(array(
			'timeout' => $this->timeout
		));

		$return = $HttpSocket->request(array(
			'method' => 'POST',
			'uri' => array(
				'scheme' => 'https',
				'host' => $this->__URI,
				'path' => $this->__path
			),
			'header' => array(
				'Content-Type' => "application/x-www-form-urlencoded; charset={$this->charset}"
			),
			'body' => array_merge($this->__config, $this->__data, $this->__shipping)
		));
		return $this->__response($return);
	}

	/**
	 *
	 * Recebe o Xml com os dados redirecionamento ou erros. Iniciando o redirecionamento
	 * @param String $res
	 * @return array
	 */
	private function __response($res) {
		$response = Xml::toArray(Xml::build($res['body']));
		
		if (isset($response['checkout'])) {
			$this->Controller->redirect($this->__redirect . $response['checkout']['code']);
		}
		
		return $response;
	}

	/**
	 *
	 * Valida os dados de configuração caso falhe dispara erro de PHP
	 */
	private function __configValidates() {
		if (!isset($this->__config['email']))
			trigger_error('E-mail the seller not found', E_USER_ERROR);
		if (!isset($this->__config['token']))
			trigger_error('Token not found', E_USER_ERROR);
		if (!isset($this->__config['currency']))
			trigger_error('Currency of reference not found', E_USER_ERROR);
	}

	/**
	 *
	 * Valida os itens da compra caso falhe dispara erro de PHP
	 */
	private function __dataValidates() {
		if (empty($this->__data) && !is_array($this->__data))
			trigger_error('Purchase data empty', E_USER_ERROR);

		foreach ($this->__data as $key => $value) {
			if (preg_match('/^([a-zA-Z]+)[0-9]{1,}$/', $key)) {
				if (empty($value)) {
					trigger_error('Empty value for the attribute: "' . $key . '"', E_USER_ERROR);
					break;
				}
			} else {
				trigger_error('This attribute was not identified: "' . $key . '"', E_USER_ERROR);
				break;
			}
		}
	}
}