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
?>
<?php
App::import('Component', 'PagSeguro.Checkout');
class CheckoutTestCase extends CakeTestCase {

	public $CheckoutComponentTest = null;

	function startCase() {
		$this->CheckoutComponentTest = new CheckoutComponent();

		$controller = new FakeCheckoutController();
		$controller->Checkout = new $this->CheckoutComponentTest;

		$this->CheckoutComponentTest->startup(&$controller);
	}

    function testConfig() {
		$this->CheckoutComponentTest->config(array(
			'email' => 'email@email.com',
			'token' => '3C2B7B8F25EB42648516DAF4BCF49953'
		));

		$this->assertEqual($this->CheckoutComponentTest->__config, array(
			'email' => 'email@email.com',
			'token' => '3C2B7B8F25EB42648516DAF4BCF49953',
			'currency' => 'BRL'
		), 'Modificação dos valores de configuração');
	}

	function testModConfig() {
		$this->CheckoutComponentTest->__config = array(
			'currency' => 'US'
		);

		$this->CheckoutComponentTest->config(array(
			'email' => 'email@email.com',
			'token' => '3C2B7B8F25EB42648516DAF4BCF49953'
		));

		$this->assertEqual($this->CheckoutComponentTest->__config, array(
			'email' => 'email@email.com',
			'token' => '3C2B7B8F25EB42648516DAF4BCF49953',
			'currency' => 'US'
		), 'Modificação do currency');
	}



	function endCase() {
		$this->CheckoutComponentTest = null;
	}
}
class FakeCheckoutController {}