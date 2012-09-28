<?php
/**
 * Regras de validações para o PagSeguro
 *
 */

App::uses('Validation', 'Utility');

/**
 * PagSeguroValidation
 *
 * @package       PagSeguro.Lib
 */
class PagSeguroValidation
{

/**
 * Checa o nome do remetente de acordo com
 * as regras do PagSeguro
 *
 * @param string $check The value to check.
 * @return boolean
 */
	public static function name($check)
	{
		if(empty($check)) {
			return false;
		}

		if(strlen($check) > 50) {
			return false;
		}

		$parts = explode(' ', $check);

		if(count($parts) < 2) {
			return false;
		}

		foreach($parts as $part) {
			if(!Validation::alphaNumeric($part)) {
				return false;
			}
		}

		return true;
	}
}
