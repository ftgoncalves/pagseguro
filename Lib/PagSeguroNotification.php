<?php
App::uses('PagSeguro', 'PagSeguro.Lib');
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
class PagSeguroNotification extends PagSeguro {

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
	 * Valida a notificação recebida e requisita da API do PagSeguro a situação de um pagamento,
	 * converte o retorno de XML para Array e então o retorna.
	 *
	 * @param string $code Código da notificação
	 * @return mixed array com dos dados da notificação em caso de sucesso, null em caso de falha
	 */
	public function read($request) {
		if(!$this->isValidNotification($request)) {
			return false;
		}

		$this->URI['path'] .= '/' . $request->data['notificationCode'];

		try {
			$response = $this->_sendData($this->_prepareData(), 'GET');
			return $response;
		}
		catch(PagSeguroException $e) {
			$this->lastError = $e->getMessage();
			return false;
		}
	}

	/**
	 * Valida se um objeto da classe CakeRequest é uma requisição
	 * válida vinda do PagSeguro.
	 *
	 * @param CakeRequest $request Instância de CakeRequest carregando dados da notificação
	 * @return bool $isValid
	 */
	public function isValidNotification(CakeRequest $request) {
		if(!$request->is('post'))
			return false;

		if(!isset($request->data['notificationCode']) || strlen($request->data['notificationCode']) != 39)
			return false;

		if(!isset($request->data['notificationType']) || $request->data['notificationType'] != 'transaction')
			return false;

		return true;
	}

	/**
	 * Prepara os dados para enviar ao PagSeguro
	 *
	 * @return array
	 */
	protected function _prepareData() {
		return $this->settings;
	}

	/**
	 * Recebe o Xml convertido para Array com os dados da Notificação
	 * lida do PagSeguro.
	 *
	 * Devolve o Array sem o índice base 'transaction' ou pode retornar
	 * um Array reduzido com as informações essenciais caso o segundo
	 * parâmetro seja true
	 *
	 * @param String $data
	 * @return array
	 */
	protected function _parseResponse($data, $onlyBasic = false) {
		if(!isset($data['transaction']))
			throw new PagSeguroException("Resposta inválida do PagSeguro para uma Notificação.");

		if(!$onlyBasic)
			return $data['transaction'];

		$date = substr($data['transaction']['date'], 0, 19);
		$date = str_replace('T', ' ', $date);

		$decoded = array(
			'date' => $date,
			'transaction_code' => $data['transaction']['code'],
			'value' => $data['transaction']['grossAmount'],
			'status_code' => $data['transaction']['status'],
			'reference' => $data['transaction']['reference']
		);

		return $decoded;
	}
}