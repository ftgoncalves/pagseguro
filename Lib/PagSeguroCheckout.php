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

		$this->URI['path'] = '/v2/checkout/';
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
	 * @param string $id			Identificação do produto no seu sistema
	 * @param string $description	Nome do produto
	 * @param string $amount		Valor do item
	 * @param string $weight		Peso do item
	 * @param integer $quantity		Quantidade
	 * @param string $shippingCost	Custo da entrega
	 *
	 * @return PagSeguroCheckout
	 */
	public function addItem($id, $description, $amount, $quantity = 1, $weight = 0, $shippingCost = null) {

		$item = compact($id, $description, $amount, $quantity);

		if(!empty($weight))
			$item['weight'] = $weight;

		if(!empty($shippingCost))
			$item['shippingCost'] = $shippingCost;

		$this->setItem($item);

		return $this;
	}

	/**
	 * Incluí um item passado como um array
	 *
	 * @param array $item um array contendo os seguintes indices:
	 *  - string id OBRIGATÓRIO
	 *  - string description OBRIGATÓRIO
	 *  - string amount OBRIGATÓRIO
	 *  - integer quantity OBRIGATÓRIO
	 *  - string weight OPCIONAL
	 *  - string shippingCost OPCIONAL
	 *
	 * @throws PagSeguroException
	 *
	 * @return PagSeguroCheckout
	 */
	public function setItem($item) {
		if(!is_array($item))
			throw new PagSeguroException("Este método recebe um array como parâmetro.");

		$requireds = array('id', 'description', 'amount', 'quantity');

		foreach($requireds as $field) {
			if(!isset($item[$field]) || empty($item[$field]))
				throw new PagSeguroException("O campo {$field} é obrigatório para inclusão de itens.");
		}

		extract($item);
		$nextId = $this->cartCount;

		$item = array(
			"itemId{$nextId}"			=> $id,
			"itemDescription{$nextId}"	=> $description,
			"itemAmount{$nextId}"		=> str_replace(',', '', number_format($amount, 2)),
			"itemQuantity{$nextId}"		=> $quantity
		);

		if(isset($weight))
			$item["itemWeight{$nextId}"] = $weight;

		if(isset($shippingCost))
			$item["itemShippingCost{$nextId}"] = $shippingCost;

		$this->cart = array_merge($this->cart, $item);
		$this->cartCount++;

		return $this;
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
	 *
	 * @return PagSeguroCheckout
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

		return $this;
	}

	/**
	 * Define os dados do cliente
	 *
	 * @param string $email
	 * @param string $name
	 * @param string $areaCode
	 * @param string $phoneNumber
	 *
	 * @return PagSeguroCheckout
	 */
	public function setCustomer($email, $name, $areaCode = null, $phoneNumber = null) {
		$this->shippingCustomer = array(
			'senderName'		=> $name,
			'senderEmail'		=> $email
		);

		if($areaCode && $phoneNumber) {
			$this->shippingCustomer['senderAreaCode'] = $areaCode;
			$this->shippingCustomer['senderPhone']    = $phoneNumber;
		}

		return $this;
	}

	/**
	 * Define o tipo de entrega
	 *
	 * @param string $type
	 * @throws PagSeguroException
	 *
	 * @return PagSeguroCheckout
	 */
	public function setShippingType($type) {
		if (!isset($this->typeFreight[$type]))
			throw new PagSeguroException("Tipo de entrega '{$type}' não suportado.");

		$this->type = array('shippingType' => $this->typeFreight[$type]);

		return $this;
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

		if(!isset($this->settings['currency']) || empty($this->settings['currency']))
			throw new PagSeguroException("Erro de configuração - Atributo 'currency' não definido.");
		if($this->settings['currency'] !== 'BRL')
			throw new PagSeguroException("Erro de configuração - Atributo 'currency' só aceita o valor 'BRL'.");
	}

	/**
	 * Recebe o XML convertido para Array com os dados de redirecionamento ou erros.
	 *
	 * @param array $data
	 * @return array
	 */
	protected function _parseResponse($data) {
		if(!isset($data['checkout']))
			throw new PagSeguroException("Resposta inválida do PagSeguro para Checkout.");

		$data['redirectTo'] = sprintf($this->redirectTo, $data['checkout']['code']);

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