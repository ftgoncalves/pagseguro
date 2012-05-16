<?php
App::uses('Controller', 'Controller');
App::uses('CheckoutComponent', 'PagSeguro.Controller/Component');
class CheckoutComponentTestCase extends CakeTestCase {

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

		$this->Controller = new Controller(null);

		$this->Checkout = new CheckoutComponent($this->Controller->Components);
		$this->Checkout->startup($this->Controller);
	}

	public function tearDown()
	{
		$this->Controller = null;
		$this->Checkout = null;
	}

	/**
	 * @expectedException PagSeguroException
	 * @expectedExceptionMessage Erro de configuração - Atributo 'email' não definido.
	 */
	public function testIncompleteConfig() {
		$this->Checkout->config(array(
			'token' => '3C2B7B8F25EB42648516DAF4BCF49953'
		));
	}

	public function testConfig() {
		$this->Checkout->config(array(
			'email' => 'email@email.com',
			'token' => '3C2B7B8F25EB42648516DAF4BCF49953'
		));

		$this->assertEqual($this->Checkout->config(), array(
			'email' => 'email@email.com',
			'token' => '3C2B7B8F25EB42648516DAF4BCF49953',
			'currency' => 'BRL'
		), 'Modificação dos valores de configuração');
	}

	public function testGlobalConfig() {
		Configure::write('PagSeguro', array(
			'email' => 'email@email.com',
			'token' => '3C2B7B8F25EB42648516DAF4BCF49953'
			)
		);

		$this->Controller = new Controller(null);
		$this->Checkout = new CheckoutComponent($this->Controller->Components);
		$this->Checkout->startup($this->Controller);

		$this->assertEqual($this->Checkout->config(), array(
			'email' => 'email@email.com',
			'token' => '3C2B7B8F25EB42648516DAF4BCF49953',
			'currency' => 'BRL'
		), 'Modificação dos valores de configuração');
	}
}