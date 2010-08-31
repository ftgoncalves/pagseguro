<?php
App::import('Helper', 'PagSeguro', array('plugin' => 'pag_seguro'));
App::import('Helper', array('Form', 'Html'));
App::import('Core', array('Helper', 'AppHelper', 'ClassRegistry'));

class DefaultsTestCase extends CakeTestCase {

	var $defaults = array(
		'init' => array(
			'pagseguro' => array(
				'type' => 'CP',
				'reference' => 103,
				'freight_type' => 'EN',
				'theme' => 1,
				'currency' => 'BRL',
				'email' => 'teste@teste.com'
			),
			'definitions' => array(
				'currency_type' => 'dolar',
				'weight_type' => 'g',
				'encode' => 'utf-8'
			),
			'customer' => array(
				'cliente_nome' => null,
				'cliente_cep' => null,
				'cliente_end' => null,
				'cliente_num' => null,
				'cliente_compl' => null,
				'cliente_bairro' => null,
				'cliente_cidade' => null,
				'cliente_uf' => null,
				'cliente_pais' => null,
				'cliente_ddd' => null,
				'cliente_tel' => null,
				'cliente_email' => null
			),
			'format' => array(
				'item_id' => 'id',
				'item_descr' => 'description',
				'item_quant' => 'amount',
				'item_valor' => 'price',
				'item_frete' => 'item_frete',
				'item_peso' => 'item_peso',
			)
		),
		'data' => array(
			0 => array(
				0 => array(
					0 => array('item_id_0' => 1)
				),
				1 => array(
					1 => array('item_descr_0' => 'Produto teste')
				),
				2 => array(
					2 => array('item_valor_0' => 1235)
				),
				3 => array(
					3 => array('item_quant_0' => 1)
				),
				4 => array(
					4 => array('item_frete_0' => 134)
				),
				5 => array(
					5 => array('item_peso_0' => 305)
				)
			)
		)
	);
}

class PagSeguroTest extends DefaultsTestCase {

	function start(){
		$this->PagSeguroTest = new PagSeguroHelper();
		$this->PagSeguroTest->Form =& new FormHelper();
	}

	function testForm(){
		$out = $this->PagSeguroTest->form($this->defaults);
		$this->assertPattern('(<form(.+)">)', $out);
	}

	function testFormData(){
		$this->defaults['init']['pagseguro']['theme'] = 5;
		$out = $this->PagSeguroTest->form($this->defaults);
		$this->assertEqual($this->PagSeguroTest->data['init']['pagseguro']['theme'], 5);
	}
}