<?php
App::uses('PagSeguroConsult', 'PagSeguro.Lib');
App::uses('PagSeguroException', 'PagSeguro.Lib');

/**
 * Lib que implementa a API de notificação do PagSeguro.
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
class PagSeguroNotification extends PagSeguroConsult {

	/**
	 * Construtor padrão
	 *
	 * @param array $settings
	 */
	public function __construct($settings = array()) {
		parent::__construct($settings);

		$this->URI['path'] = '/v2/transactions/notifications/';
	}

	/**
	 * Sobrecarrega o método PagSeguroConsult::read para validar a
	 * notificação recebida antes de requisitar da API do PagSeguro
	 * a situação de um pagamento.
	 *
	 * @param array $data Dados vindos do PagSeguro
	 * @return mixed array com dos dados da notificação em caso de sucesso, false em caso de falha
	 */
	public function read($data) {
		if(!$this->isValidNotification($data)) {
			return false;
		}

		return parent::read($data['notificationCode']);
	}

	/**
	 * Valida se um array com dados vindos do PagSeguro
	 * caracterizam uma notificação válida.
	 *
	 * @param array $data Dados vindos do PagSeguro
	 * @return bool $isValid
	 */
	public function isValidNotification($data) {
		if(!isset($data['notificationCode']) || strlen($data['notificationCode']) != 39)
			return false;

		if(!isset($data['notificationType']) || $data['notificationType'] != 'transaction')
			return false;

		return true;
	}
}