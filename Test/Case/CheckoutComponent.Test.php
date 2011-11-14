<?php
/* SVN FILE: $Id:$ */
/**
 * BrainStern Soluções Ltda - http://www.brainstern.com/
 * E-mail: contato@brainstern.com
 *
 * @created: 01/02/2011
 * @version: $Rev:$
 * @author: $Author:$
 * @LastChangedDate: $Date:$
 * @link: $HeadURL:$
 */

App::uses('Controller', 'Controller');
App::uses('CheckoutComponent', 'PagSeguro/Component');
class CheckoutTestCase extends CakeTestCase {

	public $CheckoutComponentTest = null;
	
	public $Controller = null;
	
	public $Checkout = null;

	/**
	* setUp
	*
	* @retun void
	* @access public
	*/
	public function setUp()
	{
		parent::setUp();
	
		Configure::write('Language.default', 'pt-br');
		setlocale(LC_ALL, 'pt_BR.utf-8', 'pt_BR', 'pt-br', 'pt_BR.iso-8859-1');
	
		$this->Controller = new Controller(null);
		
		$this->Checkout = new CheckoutComponent($this->Controller->Components);
		$this->Checkout->startup($this->Controller);
	}

    function testConfig() {
		$this->Checkout->config(array(
			'email' => 'email@email.com',
			'token' => '3C2B7B8F25EB42648516DAF4BCF49953'
		));

		$this->assertEqual($this->Checkout->__config, array(
			'email' => 'email@email.com',
			'token' => '3C2B7B8F25EB42648516DAF4BCF49953',
			'currency' => 'BRL'
		), 'Modificação dos valores de configuração');
	}

	function testModConfig() {
		$this->Checkout->__config = array(
			'currency' => 'US'
		);

		$this->Checkout->config(array(
			'email' => 'email@email.com',
			'token' => '3C2B7B8F25EB42648516DAF4BCF49953'
		));

		$this->assertEqual($this->Checkout->__config, array(
			'email' => 'email@email.com',
			'token' => '3C2B7B8F25EB42648516DAF4BCF49953',
			'currency' => 'US'
		), 'Modificação do currency');
	}
}