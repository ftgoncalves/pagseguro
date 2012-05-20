<?php
App::uses('Component', 'Controller');
App::uses('PagSeguroConsult', 'PagSeguro.Lib');

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
	 * Sobrescreve as configurações em tempo de execução.
	 * Caso nenhum parâmetro seja passado, retorna as configurações
	 * atuais.
	 *
	 * @param array $config
	 * @return mixed Array com as configurações caso não seja
	 * passado parâmetro, nada caso contrário.
	 */
	public function config($config = null) {
		return $this->_PagSeguroConsult->config($config);
	}

	/**
	 * Retorna o último erro na lib
	 *
	 * @return string
	 */
	public function getErrors() {
		return $this->_PagSeguroConsult->lastError;
	}

	/**
	 * Recupera informações de uma transação
	 *
	 * @param string $transactionCode
	 * @return mixed Array com resposta em caso de sucesso e null em caso de falha
	*/
	public function getTransactionInfo($transactionCode) {
		try{
			return $this->_PagSeguroConsult->read($transactionCode);
		}
		catch(PagSeguroException $e) {
			return false;
		}
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
		try {
			return $_PagSeguroConsult->find($periodStart, $periodEnd, null, $page);
		}
		catch(PagSeguroException $e) {
			return false;
		}
	}
}