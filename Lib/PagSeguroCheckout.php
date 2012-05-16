<?php
App::uses('PagSeguro', 'PagSeguro.Lib');
App::uses('PagSeguroException', 'PagSeguro.Lib');

/**
 * Classe que responsável por iniciar uma transação para pagamento
 * via PagSeguro.
 *
 * PHP versions 5+
 * Copyright 2010-2012, Felipe Theodoro Gonçalves, (http://ftgoncalves.com.br)
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @author      Felipe Theodoro Gonçalves
 * @author      Cauan Cabral
 * @link        https://github.com/ftgoncalves/pagseguro/
 * @license     MIT License (http://www.opensource.org/licenses/mit-license.php)
 * @version     2.1
 */
class PagSeguroCheckout extends PagSeguro {

	/**
	 * Endereço para redirecionamento para o checkout do PagSeguro
	 * @var String
	 */
	private $redirectTo = 'https://pagseguro.uol.com.br/v2/checkout/payment.html?code=%s';

	/**
	 * Referência da transação
	 *
	 * Essa referência pode ser usada para associar a transação
	 * do PagSeguro com um registro em seu sistema.
	 *
	 * @var array
	 */
	private $reference = array();

	/**
	 * Dados do endereço
	 *
	 * @var array
	 */
	private $shippingAddress = array();

	/**
	 * Dados do cliente
	 *
	 * @var array
	 */
	private $shippingCustomer = array();

	/**
	 * Dados dos itens da compra
	 *
	 * @var array
	 */
	private $cart = array();

	/**
	 * Quantidade de items no carrinho de
	 * compra.
	 *
	 * @var integer
	 */
	private $cartCount = 1;

	/**
	 * Tipos de frete suportados
	 *
	 * @var array
	 */
	private $typeFreight = array(
		null => 3,
		'PAC' => 1,
		'SEDEX' => 2,
	);

	/**
	 * Tipo de frete em uso
	 *
	 * @var array
	 */
	private $type = array(
		'shippingType' => 3
	);

	/**
	 * Construtor padrão
	 *
	 * @param array $settings
	 */
	public function __construct($settings = array()) {
		$this->settings['currency'] = 'BRL';

		parent::__construct($settings);

		$this->URI['resource'] = '/v2/checkout/';
	}

	/**
	 * Define uma referência para a transação com alguma
	 * identificação interna da aplicação.
	 *
	 * @param string $id
	 */
	public function setReference($id) {
		$this->reference = array('reference' => $id);
	}

	/**
	 * Incluí item no carrinho de compras
	 *
	 * @param string $id		Identificação do produto no seu sistema
	 * @param string $name		Nome do produto
	 * @param string $amount	Valor do item
	 * @param string $weight	Peso do item
	 * @param integer $quantity	Quantidade
	 *
	 * @return void
	 */
	public function addItem($id, $name, $amount, $weight, $quantity = 1) {
		$nextId = $this->cartCount;

		$item = array(
			"itemId{$nextId}"			=> $id,
			"itemDescription{$nextId}"	=> $name,
			"itemAmount{$nextId}"		=> str_replace(',', '', number_format($amount, 2)),
			"itemWeight{$nextId}"		=> $weight,
			"itemQuantity{$nextId}"		=> $quantity
		);

		$this->cart = array_merge($this->cart, $item);
		$this->cartCount++;
	}

	/**
	 * Define o endereço de entrega
	 *
	 * @param string $zip			CEP
	 * @param string $address		Endereço (Rua, por exemplo)
	 * @param string $number		Número
	 * @param string $completion	Complemento
	 * @param string $neighborhood	Bairro
	 * @param string $city			Cidade
	 * @param string $state			Estado
	 * @param string $country		País
	 */
	public function setShippingAddress($zip, $address, $number, $completion, $neighborhood, $city, $state, $country) {
		$this->shippingAddress = array(
			'shippingAddressStreet'		=> $address,
			'shippingAddressNumber'		=> $number,
			'shippingAddressDistrict'	=> $neighborhood,
			'shippingAddressPostalCode'	=> $zip,
			'shippingAddressCity'		=> $city,
			'shippingAddressState'		=> $state,
			'shippingAddressCountry'	=> $country
		);
	}

	/**
	 * Define os dados do cliente
	 *
	 * @param string $email
	 * @param string $name
	 * @param string $areaCode
	 * @param string $phoneNumber
	 */
	public function setCustomer($email, $name, $areaCode, $phoneNumber) {
		$this->shippingCustomer = array(
			'senderName'		=> $name,
			'senderAreaCode'	=> $areaCode,
			'senderPhone'		=> $phoneNumber,
			'senderEmail'		=> $email
		);
	}

	/**
	 * Define o tipo de entrega
	 *
	 * @param string $type
	 * @throws PagSeguroException
	 */
	public function setShippingType($type) {
		if (!isset($this->typeFreight[$type]))
			throw new PagSeguroException("Tipo de entrega '{$type}' não suportado.");

		$this->type = array('shippingType' => $this->typeFreight[$type]);
	}

	/**
	 * Envia dados ao PagSeguro para iniciar transação de pagamento.
	 * Caso haja falha, retorna false e guarda a mensagem de
	 * erro no atributo $lastError.
	 *
	 * @return mixed Um array com os dados da compra + endereço
	 * para redirecionar o usuário, caso haja um. False em caso
	 * de falha.
	 */
	public function finalize() {
		try {
			$response = $this->_sendData($this->_prepareData());
			return $response;
		}
		catch(PagSeguroException $e) {
			$this->lastError = $e->getMessage();

			return false;
		}
	}

	/**
	 * Valida os dados de configuração caso falhe dispara uma exceção
	 *
	 * @throws PagSeguroException
	 * @return void
	 */
	protected function _settingsValidates() {
		parent::_settingsValidates();

		$fields = array('currency');

		foreach($fields as $field) {
			if (!isset($this->settings[$field]) || empty($this->settings[$field]))
				throw new PagSeguroException("Erro de configuração - Atributo '{$field}' não definido.");
		}
	}

	/**
	 * Recebe o Xml com os dados redirecionamento ou erros.
	 * Iniciando o redirecionamento
	 *
	 * @param String $res
	 * @return array
	 */
	protected function _parseResponse($data) {
		if(!isset($data['checkout']))
			throw new PagSeguroException("Resposta inválida do PagSeguro para Checkout.");

		$data['redirectTo'] = sprinft($this->redirectTo, $data['checkout']['code']);

		return $data;
	}

	/**
	 * Prepara os dados para enviar ao PagSeguro
	 *
	 * @return array
	 */
	protected function _prepareData() {
		if($this->cartCount === 1)
			throw new PagSeguroException("Seu carrinho está vazio, adicione algum item antes de finalizar.");

		return array_merge($this->reference, $this->settings, $this->type, $this->cart, $this->shippingAddress, $this->shippingCustomer);
	}
}