<?php
/**
 * @author: Felipe Theodoro Gonçalves blog.ftgoncalves.com
 * @created: 27/08/2010
 * @version: 0.2
 */
/**
 * Component para tratamento dos dados para envio ao PagSeguro
 * Este componente foi devenvolvido para trabalhar com o Cake 1.3
 *
 * @author Felipe Theodoro Gonçalves
 *
 */
class CheckoutComponent extends Object {

	public $controller = null;

	private $__URI = 'ws.pagseguro.uol.com.br';

	private $__path = '/v2/checkout/';

	public $charset = 'UTF-8';

	public $timeout = 20;

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
	 *
	 * Methodo para setar as configurações defaults do pagseguro
	 */
	public function startup(&$controller) {
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

	public function setShipping($data) {
		$this->__shipping = $data;
	}

	public function set($data) {
		$this->__data = $data;

		$this->__dataValidates();
	}

	public function finalize() {
		App::import('Core', 'HttpSocket');
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

	private function __response($res) {
		App::import('Core', 'Xml');
		$xml = new xml($res);
		$response = $xml->toArray();
		if (!isset($response['Errors'])) {
			if (isset($response['Checkout'])) {
				$this->controller->redirect($this->__redirect . $response['Checkout']['code'], null, false);
				return $response['Checkout']['code'];
			}
		} else
			return $response['Errors'];
	}

	private function __configValidates() {
		if (!isset($this->__config['email']))
			trigger_error('E-mail the seller not found', E_USER_ERROR);
		if (!isset($this->__config['token']))
			trigger_error('Token not found', E_USER_ERROR);
		if (!isset($this->__config['currency']))
			trigger_error('Currency of reference not found', E_USER_ERROR);
	}

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