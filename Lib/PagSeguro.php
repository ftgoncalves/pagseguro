<?php
App::uses('HttpSocket', 'Network/Http');
App::uses('Xml', 'Utility');
App::uses('PagSeguroException', 'PagSeguro.Lib');

/**
 * Classe base que fornece estrutura comum aos componentes
 * que interagem com o PagSeguro
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
class PagSeguro {
	protected $URI = array(
		'scheme' => 'https',
		'host' => 'ws.pagseguro.uol.com.br',
		'port' => '443',
		'path' => '',
	);

	protected $settings = array(
		'email' => null,
		'token' => null
	);

	public $charset = 'UTF-8';

	public $timeout = 20;

	public $lastError = null;

	public function __construct($settings = array()) {

		if(empty($settings) && Configure::read('PagSeguro') !== null) {
			$settings = (array)Configure::read('PagSeguro');
		}

		$this->settings = array_merge($this->settings, $settings);
	}

	/**
	 * Sobrescreve as configurações em tempo de execução
	 *
	 * @param array $config
	 */
	public function config($config = null) {
		if($config !== null) {
			$this->settings = array_merge($this->settings, $config);
			$this->_settingsValidates();
		}

		return $this->settings;
	}

	/**
	 * Envia os dados para API do PagSeguro usando método POST.
	 *
	 * @throws PagSeguroException
	 * @param array $data
	 * @return array
	 */
	protected function _sendData($data) {
		$this->_settingsValidates();

		$HttpSocket = new HttpSocket(array(
			'timeout' => $this->timeout
		));

		$return = $HttpSocket->request(array(
			'method' => 'POST',
			'uri' => $this->URI,
			'header' => array(
				'Content-Type' => "application/x-www-form-urlencoded; charset={$this->charset}"
			),
			'body' => $data
		));

		if($return->body == 'Unauthorized')
			throw PagSeguro('O Token ou E-mail foi rejeitado pelo PagSeguro. Verifique as configurações.');

		$response = Xml::toArray(Xml::build($return->body));

		if($this->_parseResponseErrors($response))
			throw new PagSeguroException("Erro com os dados enviados no PagSeguro.");

		return $this->_parseResponse($return->body);
	}

	/**
	 * Parseia e retorna a resposta do PagSeguro.
	 * Deve ser implementado nas classes filhas
	 *
	 * @param array $data
	 * @return array
	 */
	protected function _parseResponse($data) {
		throw new PagSeguroException("Erro de implementação. O método _parseResponse deve ser implementado nas classes filhas de PagSeguro.");
	}

	/**
	 * Verifica se há erros na resposta do PagSeguro e formata as mensagens
	 * no atributo lastError da classe.
	 *
	 * @param array $data
	 * @return bool True caso hajam erros, False caso contrário
	 */
	protected function _parseResponseErrors($data) {
		if(!isset($response['errors']))
			return false;

		$lastError = "Erro no PagSeguro: \n";

		foreach($response['errors'] as $error)
			$lastError .= "[{$error['error']['code']}] {$error['error']['message']}\n";

		return true;
	}

	/**
	 * Valida os dados de configuração caso falhe dispara uma exceção
	 *
	 * @throws PagSeguroException
	 * @return void
	 */
	protected function _settingsValidates() {
		$fields = array('email', 'token');

		foreach($fields as $field) {
			if (!isset($this->settings[$field]) || empty($this->settings[$field]))
				throw new PagSeguroException("Erro de configuração - Atributo '{$field}' não definido.");
		}
	}
}