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

			return $this;
		}

		return $this->settings;
	}

	/**
	 * Envia os dados para API do PagSeguro usando método especificado.
	 *
	 * @throws PagSeguroException
	 * @param array $data
	 * @param string $method
	 * @return array
	 */
	protected function _sendData($data, $method = 'POST') {
		$this->_settingsValidates();

		$HttpSocket = new HttpSocket(array(
			'timeout' => $this->timeout
		));

		if('get' === strtolower($method)) {
			$return = $HttpSocket->get(
				$this->URI,
				$data,
				array('header' => array('Content-Type' => "application/x-www-form-urlencoded; {$this->charset}"))
			);
		} else {
			$return = $HttpSocket->post(
				$this->URI,
				$data,
				array('header' => array('Content-Type' => "application/x-www-form-urlencoded; {$this->charset}"))
			);
		}

		switch ($return->code) {
			case 200:
				break;
			case 400:
				throw new PagSeguroException('A requisição foi rejeitada pela API do PagSeguro. Verifique as configurações.');
			case 401:
				throw new PagSeguroException('O Token ou E-mail foi rejeitado pelo PagSeguro. Verifique as configurações.');
			case 404:
				throw new PagSeguroException('Recurso não encontrado. Verifique os dados enviados.');
			default:
				throw new PagSeguroException('Erro desconhecido com a API do PagSeguro. Verifique suas configurações.');
		}

		try {
			$response = Xml::toArray(Xml::build($return->body));
		}
		catch(XmlException $e) {
			throw new PagSeguroException('A resposta do PagSeguro não é um XML válido.');
		}

		if($this->_parseResponseErrors($response))
			throw new PagSeguroException("Erro com os dados enviados no PagSeguro.");

		return $this->_parseResponse($response);
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
		$fields = array('email' => 60, 'token' => 32);

		foreach($fields as $fieldName => $length) {
			if (!isset($this->settings[$fieldName]) || empty($this->settings[$fieldName]))
				throw new PagSeguroException("Erro de configuração - Atributo '{$fieldName}' não definido.");

			if(strlen($this->settings[$fieldName]) > $length)
				throw new PagSeguroException("Erro de configuração - Atributo '{$fieldName}' excede limite de {$length} caracteres da API.");
		}
	}
}