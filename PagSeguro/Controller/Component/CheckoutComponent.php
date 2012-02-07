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
class CheckoutComponent extends Component {

	/**
	 *
	 * Instancia do Controller
	 * @var Object
	 */
	public $Controller = null;

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
	 * Endereço para redirecionamento para o checkout do PagSeguro
	 * @var String
	 */
	private $__redirect = 'https://pagseguro.uol.com.br/v2/checkout/payment.html?code=';

	/**
	 *
	 * Atributo contendo as configurações do pagseguro
	 * @var array
	 */
	public $config = array(
		'currency' => 'BRL'
	);

	/**
	 *
	 * Referencia da transação
	 * @var array
	 */
	private $reference = array();

	/**
	 *
	 * Dados do endereço
	 * @var array
	 */
	private $shippingAddress = array();

	/**
	 *
	 * Dados do cliente
	 * @var array
	 */
	private $shippingCustomer = array();

	/**
	 *
	 * Dados dos itens da compra
	 * @var array
	 */
	private $cart = array();

	/**
	 * Tipos de frete
	 */
	private $typeFreight = array(
		'PAC' => 1,
		'SEDEX' => 2,
		null => 3
	);

	private $type = array(
		'shippingType' => 3
	);

	private $count = 1;

	/**
	 *
	 * Methodo para setar as configurações defaults do pagseguro
	 * @param Object $controller
	 */
	public function startup(&$Controller) {
		$this->Controller = $Controller;

		if ((Configure::read('PagSeguro') != false || Configure::read('PagSeguro') != null) && is_array(Configure::read('PagSeguro')))
			$this->config = array_merge($this->config, Configure::read('pag_seguro'));
	}

	/**
	 *
	 * Força configurações em tempo de execução
	 * @param array $config
	 */
	public function setConfig($email, $token, $currency = 'BRL') {
		$this->config = array(
			'email' => $email,
			'token' => $token,
			'currency' => $currency
		);
	}

	public function setReference($id) {
		$this->reference = array('reference' => $id);
	}


	public function addItem($id, $name, $amount, $weight, $quantity = 1) {
		$this->cart = array_merge($this->cart, array(
			'itemId' . $this->count => $id,
			'itemDescription' . $this->count => $name,
			'itemAmount' . $this->count => str_replace(',', '', number_format($amount, 2)),
			'itemWeight' . $this->count => $weight,
			'itemQuantity' . $this->count => $quantity
		));
		$this->count++;
	}

	public function setShippingAddress($zip, $address, $number, $completion, $neighborhood, $city,	$estate, $country) {
		$this->shippingAddress = array(
			'shippingAddressStreet' => $address,
			'shippingAddressNumber' => $number,
			'shippingAddressDistrict' => $neighborhood,
			'shippingAddressPostalCode' => $zip,
			'shippingAddressCity' => $city,
			'shippingAddressState' => $estate,
			'shippingAddressCountry' => $country
		);
	}

	public function setCustomer($email, $name, $areaCode, $phoneNumber) {
		$this->shippingCustomer = array(
			'senderName' => $name,
			'senderAreaCode' => $areaCode,
			'senderPhone' => $phoneNumber,
			'senderEmail' => $email,
		);
	}

	public function setShippingType($type) {
		if (isset($this->typeFreight[$type]))
			$this->type = array('shippingType' => $this->typeFreight[$type]);
	}

	/**
	 *
	 * Finaliza a compra.
	 * Recebe o codigo para redirecionamento ou erro.
	 */
	public function finalize() {
		$http = new HttpSocket();

		$return = $http->request(array(
			'method' => 'POST',
			'uri' => array(
				'scheme' => 'https',
				'host' => $this->__URI,
				'path' => $this->__path
			),
			'header' => array(
				'Content-Type' => "application/x-www-form-urlencoded; charset={$this->charset}"
			),
			'body' => array_merge($this->reference, $this->config, $this->type, $this->cart, $this->shippingAddress, $this->shippingCustomer)
		));
		return $this->__response($return);
	}

	/**
	 *
	 * Recebe o Xml com os dados redirecionamento ou erros. Iniciando o redirecionamento
	 * @param String $res
	 * @return array
	 */
	private function __response($data) {
		App::uses('Xml', 'Utility');
		$response = Xml::toArray(Xml::build($data['body']));

		if (isset($response['checkout']))
			$this->Controller->redirect($this->__redirect . $response['checkout']['code'], null, false);

		return $response;
	}
}