<?php
App::uses('Component', 'Controller');
App::uses('PagSeguroNotification', 'PagSeguro.Lib');

/**
 * Wrapper para a lib PagSeguroNotification ser usada
 * junto à controllers.
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
class NotificationsComponent extends Component {

	/**
	 *
	 * @var Controller
	 */
	protected $Controller = null;

	/**
	 * Instância da Lib PagSeguroNotification
	 * que é responsável por toda a iteração
	 * com a API do PagSeguro.
	 *
	 * @var PagSeguroNotification
	 */
	protected $_PagSeguroNotification = null;

	/**
	 * Construtor padrão
	 *
	 * @param ComponentCollection $collection
	 * @param array $settings
	 */
	public function __construct(ComponentCollection $collection, $settings = array()) {
		parent::__construct($collection, $settings);

		$this->_PagSeguroNotification = new PagSeguroNotification($settings);
	}

	/**
	 *
	 * @param Controller &$controller
	 * @return void
	 */
	public function initialize(&$controller) {
		$this->Controller =& $controller;
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
		return $this->_PagSeguroNotification->config($config);
	}

	/**
	 * Retorna o último erro na lib
	 *
	 * @return string
	 */
	public function getErrors() {
		return $this->_PagSeguroNotification->lastError;
	}

	/**
	 * Requisita e retorna a notificação enviada pelo PagSeguro
	 *
	 * @param array $data Array do objeto da requisição recebido do PagSeguro
	 * @return mixed array com dos dados da notificação em caso de sucesso, false em caso de falha
	 */
	public function read($data) {
		try{
			return $this->_PagSeguroNotification->read($data);
		}
		catch(PagSeguroException $e) {
			return false;
		}
	}
}