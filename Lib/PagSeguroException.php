<?php
App::uses('CakeException', 'CORE');
class PagSeguroException extends CakeException {

	/**
	 * Sobrescreve exceção do Cake para incluir informação
	 * do erro que poderá ser logada.
	 *
	 * @param string  $message Mensagem da Exceção
	 * @param integer $code    Código do erro
	 * @param string  $error   O erro retornado pelo PagSeguro (possivelmente um XML)
	 */
	public function __construct($message, $code = 1, $error = null)
	{
		if(!empty($error)) {

			try {
				$decoded = Xml::toArray(Xml::build($error));
				$error = $this->_parseXmlError($decoded);
			} catch(XmlException $e) {
				// apenas uma string... não faz conversão
			}

			$msg = __('Erro relacionado ao PagSeguro:') . "\n" . $error;
			CakeLog::write('error', $error);
		}

		parent::__construct($message, $code);
	}

	/**
	 * Parseia um erro XML (convertido para Array) retornado pelo PagSeguro retornando
	 * uma string.
	 *
	 * @param  array $error
	 * @return string
	 */
	protected function _parseXmlError($error)
	{
		if(!isset($response['errors']))
			return '';

		$errors = '';
		foreach($response['errors'] as $error) {
			$errors .= "[{$error['error']['code']}] {$error['error']['message']}\n";
		}

		return $errors;
	}
}