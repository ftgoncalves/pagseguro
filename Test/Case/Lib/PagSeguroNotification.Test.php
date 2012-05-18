<?php
App::uses('CakeRequest', 'CORE');
App::uses('PagSeguroNotification', 'PagSeguro.Lib');
class PagSeguroNotificationTestCase extends CakeTestCase {

	/**
	* setUp
	*
	* @retun void
	* @access public
	*/
	public function setUp()
	{
		parent::setUp();

		$this->PagSeguroNotification = new PagSeguroNotification(array(
			'email' => 'email@email.com',
			'token' => '3C2B7B8F25EB42648516DAF4BCF49953'
		));
	}

	public function tearDown()
	{
		$this->PagSeguroNotification = null;
	}

	public function testConfig() {
		$this->assertEqual($this->PagSeguroNotification->config(), array(
			'email' => 'email@email.com',
			'token' => '3C2B7B8F25EB42648516DAF4BCF49953'
		));
	}

	/**
	 *
	 * @expectedException PHPUnit_Framework_Error
	 * @return
	 */
	public function testFailIsNotification() {
		$obj = new Object();

		$this->assertFalse($this->PagSeguroNotification->isValidNotification($obj));
	}

	public function testIsNotification() {
		$obj = new CakeRequest();
		$this->assertFalse($this->PagSeguroNotification->isValidNotification($obj));

		$_POST['notificationCode'] = '123456789012345678901234567890123456789';
		$_POST['_method'] = 'POST';

		$obj = new CakeRequest();
		$this->assertFalse($this->PagSeguroNotification->isValidNotification($obj));

		$_POST['notificationType'] = 'transaction';

		$obj = new CakeRequest();
		$this->assertTrue($this->PagSeguroNotification->isValidNotification($obj));
	}

	public function testBogusRead() {
		$_POST['notificationCode'] = '123456789012345678901234567890123456789';
		$_POST['notificationType'] = 'transaction';
		$_POST['_method'] = 'POST';

		$obj = new CakeRequest();
		$this->assertFalse($this->PagSeguroNotification->read($obj));
		$this->assertEquals($this->PagSeguroNotification->lastError, 'Recurso não encontrado. Verifique os dados enviados.');
	}
}