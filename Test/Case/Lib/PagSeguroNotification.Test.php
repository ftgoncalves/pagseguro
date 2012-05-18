<?php
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
			'token' => '3C2B7B8F25EB42648516DAF4BCF49953',
			'onlyBasic' => false,
			'type' => 'read'
		));

		$this->PagSeguroNotification = new PagSeguroNotification(array(
			'email' => 'email@email.com',
			'token' => '3C2B7B8F25EB42648516DAF4BCF49953',
			'onlyBasic' => true,
			'type' => 'read'
		));

		$this->assertEqual($this->PagSeguroNotification->config(), array(
			'email' => 'email@email.com',
			'token' => '3C2B7B8F25EB42648516DAF4BCF49953',
			'onlyBasic' => true,
			'type' => 'read'
		));
	}

	public function testFailIsNotification() {
		$this->assertFalse($this->PagSeguroNotification->isValidNotification(array()));
	}

	public function testIsNotification() {
		$arr = array(
			'notificationCode' => '123456789012345678901234567890123456789'
		);

		$this->assertFalse($this->PagSeguroNotification->isValidNotification($arr));

		$arr['notificationType'] = 'transaction';

		$this->assertTrue($this->PagSeguroNotification->isValidNotification($arr));
	}

	public function testBogusRead() {
		$arr = array(
			'notificationCode' => '123456789012345678901234567890123456789',
			'notificationType' => 'transaction'
		);

		$this->assertFalse($this->PagSeguroNotification->read($arr));
		$this->assertEquals($this->PagSeguroNotification->lastError, 'O Token ou E-mail foi rejeitado pelo PagSeguro. Verifique as configurações.');
	}
}