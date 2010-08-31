<?php
class PagSeguroTestCase extends CakeTestCase {

	function testInit(){
		$this->PagSeguroComponentTest = new PagSeguroComponent();
		$this->PagSeguroComponentTest->init();
		$this->assertEqual(0, 1, "SP is best for 1xxxx-5xxxx");
	}



}

class FakePagesController {}