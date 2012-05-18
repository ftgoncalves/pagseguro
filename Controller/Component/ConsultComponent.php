<?php
App::uses('Component', 'Controller');
App::uses('PagSeguroNotification', 'PagSeguro.Lib');

/**
 * Plugin de integração com a API do PagSeguro e CakePHP.
 *
 * PHP versions 5+
 * Copyright 2010-2012, Felipe Theodoro Gonçalves, (http://ftgoncalves.com.br)
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @author	 	 Felipe Theodoro Gonçalves
 * @author       Cauan Cabral
 * @link         https://github.com/ftgoncalves/pagseguro/
 * @license      MIT License (http://www.opensource.org/licenses/mit-license.php)
 * @version      2.1
 */
class ConsultComponent extends Component {

	/**
	 *
	 * @var Controller
	 */
	protected $Controller = null;

	/**
	 * Instância da Lib PagSeguroConsult
	 * que é responsável por toda a iteração
	 * com a API do PagSeguro.
	 *
	 * @var PagSeguroConsult
	 */
	protected $_PagSeguroConsult = null;

	/**
	 * Construtor padrão
	 *
	 * @param ComponentCollection $collection
	 * @param array $settings
	 */
	public function __construct(ComponentCollection $collection, $settings = array()) {
		parent::__construct($collection, $settings);

		$this->_PagSeguroConsult = new PagSeguroConsult($settings);
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
	 * Recupera informações de uma transação
	 *
	 * @param string $transactionCode
	 * @return mixed Array com resposta em caso de sucesso e null em caso de falha
	*/
	public function getTransactionInfo($transactionCode) {
		$HttpSocket = new HttpSocket(array('timeout' => $this->timeout));

		$params = array(
			'email' => $this->__config['email'],
			'token' => $this->__config['token']
		);

		$this->pgURI['path'] .= '/' . $transactionCode;
		$response = $HttpSocket->get($this->pgURI, $params);


		if(empty($response) || empty($response->body) || $response->body == 'Not Found') {
			return null;
		}

		return Xml::toArray(Xml::build($response->body));
	}

	/**
	 * Faz consulta a API do PagSeguro sobre a situação dos paramentos realizados
	 * entre duas data.s
	 * Converte o retorno de XML para Array e então o retorna.
	 *
	 * @param DateTime $periodStart
	 * @param DateTime $periodEnd
	 * @param int $page
	 *
	 * @return mixed Array com dos dados da notificação em caso de sucesso, null em caso de falha
	 */
	public function getTransactions($periodStart, $periodEnd, $page = 1) {
		$HttpSocket = new HttpSocket(array('timeout' => $this->timeout));

		$params = array(
			'initialDate' => $periodStart->format(DateTime::W3C),
			'finalDate' => $periodEnd->format(DateTime::W3C),
			'page' => $page,
			'email' => $this->__config['email'],
			'token' => $this->__config['token']
		);

		$response = $HttpSocket->get($this->pgURI, $params);

		if(empty($response) || empty($response->body) || $response->body == 'Not Found') {
			return null;
		}

		return Xml::toArray(Xml::build($response['body']));
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


	/**
	 * "Decodifica" a estrutura de dados do PagSeguro para
	 * um conjunto de transações recebidas pelo sistema
	 *
	 * @param array $data
	 * @return boolean
	 */
	private function __historicPagSeguro($data)
	{
		if(!isset($data['transactionSearchResult']))
		{
			return false;
		}

		$decoded = array(
			'pages' => $data['transactionSearchResult']['totalPages'],
			'current' => $data['transactionSearchResult']['currentPage']
		);

		$decoded['items'] = array();

		foreach($data['transactionSearchResult']['transactions'] as $transaction)
		{
			$date = substr($transaction['date'], 0, 19);
			$date = str_replace('T', ' ', $date);

			$decoded['items'][] = array(
				'date' => $date,
				'transaction_code' => $transaction['code'],
				'value' => $transaction['grossAmount'],
				'status_code' => $transaction['status'],
				'reference' => $transaction['reference']
			);
		}

		return $decoded;
	}
}