<?php
App::import('Component', 'PagSeguro', array('plugin' => 'pag_seguro'));
class DefaultsTestCase extends CakeTestCase {

	var $default = array(
		'pagseguro' => array(
			'type' => 'CP',
			'reference' => null,
			'freight_type' => 'EN',
			'theme' => 1,
			'currency' => 'BRL',
			'extra'
		),
		'definitions' => array(
			'currency_type' => 'dolar',
			'weight_type' => 'kg',
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
			'cliente_ddd ' => null,
			'cliente_tel' => null,
			'cliente_email' => null
		),
		'format' => array(
			'item_id' => 'item_id',
			'item_descr' => 'item_descr',
			'item_quant' => 'item_quant',
			'item_valor' => 'item_valor',
			'item_frete' => 'item_frete',
			'item_peso' => 'item_peso'
		)
	);
}
class PagSeguroTestCase extends DefaultsTestCase {

	function start(){
		$this->PagSeguroComponentTest = new PagSeguroComponent();
	}

	function end(){
		unset($this->PagSeguroComponentTest);
	}

	function testInitOnlyFieldRequired(){
		$this->PagSeguroComponentTest->init(array(
			'pagseguro' => array(
				'email' => 'teste@teste.com'
			)
		));
		$this->assertTrue(isset($this->PagSeguroComponentTest->__init['pagseguro']['type']));
		$this->assertNull($this->PagSeguroComponentTest->__init['customer']['cliente_nome']);
	}

	function testCreate(){
		$this->PagSeguroComponentTest->init(array(
			'pagseguro' => array(
				'email' => 'teste@teste.com',
				'reference' => 02
			),
			'format' => array(
				'item_id' => 'id',
				'item_descr' => 'description',
				'item_quant' => 'amount',
				'item_valor' => 'price',
				'item_frete' => 'freight',
				'item_peso' => 'weight'
			),
			'definitions' => array(
				'currency_type' => 'real',
				'weight_type' => 'g'
			)
		));

		$this->assertEqual($this->PagSeguroComponentTest->__init['format']['item_id'], 'id');

		$env = array(
			0 => array(
				'ShoppingCart' => array(
					'id' => 0,
					'description' => 'Product A',
					'amount' => 2,
					'price' => '1345,73',
					'freight' => '12,50',
					'weight' => 300
				)
			)
		);

		$this->PagSeguroComponentTest->create($env);

		$this->assertTrue(isset($this->PagSeguroComponentTest->__items[1][0]['item_id_1']));
		$this->assertEqual($this->PagSeguroComponentTest->__items[1][3]['item_valor_1'], 134573);
		$this->assertEqual($this->PagSeguroComponentTest->__items[1][5]['item_peso_1'], 300);

	}

	function testNProducts(){
		$this->PagSeguroComponentTest->init(array(
			'pagseguro' => array(
				'email' => 'teste@teste.com',
				'reference' => 03
			),
			'format' => array(
				'item_id' => 'id',
				'item_descr' => 'description',
				'item_quant' => 'amount',
				'item_valor' => 'price',
				'item_frete' => 'freight',
				'item_peso' => 'weight'
			),
			'definitions' => array(
				'currency_type' => 'dolar',
				'weight_type' => 'kg'
			)
		));

		$this->assertEqual($this->PagSeguroComponentTest->__init['format']['item_id'], 'id');

		$env = array(
			0 => array(
				'ShoppingCart' => array(
					'id' => 0,
					'description' => 'Product A',
					'amount' => 2,
					'price' => 245.03,
					'freight' => 14,
					'weight' => 3.200
				)
			),
			1 => array(
				'ShoppingCart' => array(
					'id' => 2,
					'description' => 'Product B',
					'amount' => 1,
					'price' => 13,
					'freight' => 14,
					'weight' => 3
				)
			)
		);
		$this->PagSeguroComponentTest->__items = array();
		$this->PagSeguroComponentTest->create($env);

		$this->assertTrue(isset($this->PagSeguroComponentTest->__items[2][0]['item_id_2']));
		$this->assertEqual($this->PagSeguroComponentTest->__items[1][3]['item_valor_1'], 24503);
		$this->assertEqual($this->PagSeguroComponentTest->__items[2][5]['item_peso_2'], 3000);
		$this->assertEqual($this->PagSeguroComponentTest->__items[1][5]['item_peso_1'], 3200);

	}

	function testOneProduct(){
		$this->PagSeguroComponentTest->__init = $this->default;

		$this->PagSeguroComponentTest->init(array(
			'pagseguro' => array(
				'email' => 'teste@teste.com',
				'reference' => 04
			),
			'format' => array(
				'item_id' => 'id',
				'item_descr' => 'description',
				'item_quant' => 'amount',
				'item_valor' => 'price',
				'item_frete' => 'freight',
				'item_peso' => 'weight'
			)
		));
		$this->PagSeguroComponentTest->__items = array();

		$env = array('ShoppingCart' => array(
				'id' => 0,
				'description' => 'Product C',
				'amount' => 2,
				'price' => 245.03,
				'freight' => 14,
				'weight' => 3.223
			)
		);
		$this->PagSeguroComponentTest->create($env);

		$this->assertTrue(isset($this->PagSeguroComponentTest->__items[1][0]['item_id_1']));
		$this->assertEqual($this->PagSeguroComponentTest->__items[1][1]['item_descr_1'], 'Product C');
		$this->assertEqual($this->PagSeguroComponentTest->__items[1][3]['item_valor_1'], 24503);
		$this->assertEqual($this->PagSeguroComponentTest->__items[1][4]['item_frete_1'], 1400);
		$this->assertEqual($this->PagSeguroComponentTest->__items[1][5]['item_peso_1'], 3223);
	}

	function testSerialiseItem(){
		// test if value default combine of return
		$this->PagSeguroComponentTest->__init = $this->default;
		$this->PagSeguroComponentTest->init(array(
			'pagseguro' => array(
				'email' => 'teste@teste.com',
				'reference' => 05
			)
		));

		$expected = array(
			'item_id_0' => 78
		);

		$out = $this->PagSeguroComponentTest->__serialiseItem('item_id', 78, 0);
		$this->assertIdentical($out, $expected);

		// test if value set combine of return
		$this->PagSeguroComponentTest->__init = $this->default;
		$this->PagSeguroComponentTest->init(array(
			'pagseguro' => array(
				'email' => 'teste@teste.com',
				'reference' => 05
			),
			'format' => array(
				'item_id' => 'id'
			)
		));

		$expected = array(
			'item_id_4' => 19
		);

		$out = $this->PagSeguroComponentTest->__serialiseItem('id', 19, 4);
		$this->assertIdentical($out, $expected);
	}

	function testConvertWeight(){
		$this->PagSeguroComponentTest->__init = $this->default;
		$this->PagSeguroComponentTest->init(array(
			'pagseguro' => array(
				'email' => 'teste@teste.com',
				'reference' => 89
			),
			'definitions' => array(
				'weight_type' => 'kg'
			)
		));

		$out = $this->PagSeguroComponentTest->__convertWeight(3.800);
		$this->assertEqual($out, 3800);

		$out = $this->PagSeguroComponentTest->__convertWeight('3,820');
		$this->assertEqual($out, 3820);

		$out = $this->PagSeguroComponentTest->__convertWeight(9);
		$this->assertEqual($out, 9000);

		$this->PagSeguroComponentTest->__init['definitions']['weight_type'] = 'g';

		$out = $this->PagSeguroComponentTest->__convertWeight(300);
		$this->assertEqual($out, 300);

		$out = $this->PagSeguroComponentTest->__convertWeight(20);
		$this->assertEqual($out, 20);

	}

	function testFormatCurrency(){
		$this->PagSeguroComponentTest->__init = $this->default;
		$this->PagSeguroComponentTest->init(array(
			'pagseguro' => array(
				'email' => 'teste@teste.com',
				'reference' => 89
			),
			'definitions' => array(
				'currency_type' => 'dolar'
			)
		));

		$out = $this->PagSeguroComponentTest->__formatCurrency(140.89);
		$this->assertEqual($out, 14089);

		$out = $this->PagSeguroComponentTest->__formatCurrency(33140.89);
		$this->assertEqual($out, 3314089);

		$out = $this->PagSeguroComponentTest->__formatCurrency(445);
		$this->assertEqual($out, 44500);

		$out = $this->PagSeguroComponentTest->__formatCurrency(0.34);
		$this->assertEqual($out, 34);

		$this->PagSeguroComponentTest->__init['definitions']['currency_type'] = 'real';

		$out = $this->PagSeguroComponentTest->__formatCurrency('230,87');
		$this->assertEqual($out, 23087);

		$out = $this->PagSeguroComponentTest->__formatCurrency('23440,87');
		$this->assertEqual($out, 2344087);

		$out = $this->PagSeguroComponentTest->__formatCurrency('578');
		$this->assertEqual($out, 57800);

		$out = $this->PagSeguroComponentTest->__formatCurrency('0,98');
		$this->assertEqual($out, 98);
	}

	function testValidateEmail(){
		$this->assertTrue($this->PagSeguroComponentTest->__validateEmail('teste@teste.com'));
		$this->assertFalse($this->PagSeguroComponentTest->__validateEmail('teste@te&ste.com'));
		$this->assertTrue($this->PagSeguroComponentTest->__validateEmail('teste@teste.com.br'));
	}

	function testCurrencyTypeDolarAndWeightTypeWithComma(){
		$this->PagSeguroComponentTest->__init = $this->default;
		$this->PagSeguroComponentTest->init(array(
			'pagseguro' => array(
				'email' => 'teste@teste.com',
				'reference' => 89
			),
			'definitions' => array(
				'currency_type' => 'dolar',
				'weight_type' => 'kg'
			),
			'format' => array(
				'item_id' => 'id',
				'item_descr' => 'description',
				'item_quant' => 'amount',
				'item_valor' => 'price',
				'item_frete' => 'freight',
				'item_peso' => 'weight'
			)
		));

		$env = array(
			0 => array(
				'ShoppingCart' => array(
					'id' => 0,
					'description' => 'Product A',
					'amount' => 2,
					'price' => 1345.73,
					'freight' => 12.50,
					'weight' => '3,8'
				)
			)
		);

		$this->PagSeguroComponentTest->__items = array();
		$this->PagSeguroComponentTest->create($env);
		$this->assertEqual($this->PagSeguroComponentTest->__items[1][5]['item_peso_1'], 3800);
	}

	function testConfirmFalso(){
		$this->assertEqual($this->PagSeguroComponentTest->confirm(), 'FALSO');
	}

	function testConfirmIsConfirmation(){
		$this->assertFalse($this->PagSeguroComponentTest->isConfirmation());

		$_POST['post'] = 'post';
		$this->assertTrue($this->PagSeguroComponentTest->isConfirmation());
	}

	function testGetDataPayment(){
		$this->PagSeguroComponentTest->__init = $this->default;
		$this->PagSeguroComponentTest->init(array(
			'pagseguro' => array(
				'email' => 'test@test.com',
			),
			'format' => array(
				'item_id' => 'id',
				'item_descr' => 'description',
				'item_quant' => 'amount',
				'item_valor' => 'price'
			)
		));

		$_POST = array(
			'VendedorEmail' => 'test@test.com',
			'TransacaoID' => 'C418F96ECFA53543033AF94C03850125',
			'Referencia' => 22,
			'Extras' => '0,00',
			'TipoFrete' => 'EN',
			'ValorFrete' => '1,00',
			'Anotacao' => null,
			'DataTransacao' => '30/08/2010 09:19:34',
			'TipoPagamento' => 'Pagamento Online',
			'StatusTransacao' => 'Aprovado',
			'CliNome' => 'Cliente nome',
			'CliEmail' => 'cliente@cliente.com',
			'CliEndereco' => 'Rua teste',
			'CliNumero' => 402,
			'CliComplemento' => null,
			'CliBairro' => 'Vila',
			'CliCidade' => 'SAO PAULO',
			'CliEstado' => 'SP',
			'CliCEP' => 00000000,
			'CliTelefone' => '11 99999999',
			'NumItens' => 2,
			'Parcelas' => 1,
			'ProdID_1' => 1,
			'ProdDescricao_1' => 'Prod A',
			'ProdValor_1' => '1,00',
			'ProdQuantidade_1' => 1,
			'ProdFrete_1' => '1,00',
			'ProdExtras_1' => '0,00',
			'ProdID_2' => 2,
			'ProdDescricao_2' => 'Prod B',
			'ProdValor_2' => '345,00',
			'ProdQuantidade_2' => 3,
			'ProdFrete_2' => '14,00',
			'ProdExtras_2' => '1,00'
		);

		$sale_data = $this->PagSeguroComponentTest->getDataPayment();
		$this->assertEqual($sale_data['pagseguro'][1]['id'], 1);
		$this->assertTrue(isset($sale_data['pagseguro'][2]['price']) && isset($sale_data['pagseguro'][1]['price']));
		$this->assertEqual(count($sale_data['pagseguro']), 24);
	}

	function testSelectedInformations(){
		$this->PagSeguroComponentTest->__init = $this->default;
		$this->PagSeguroComponentTest->init(array(
			'pagseguro' => array(
				'email' => 'teste@teste.com',
				'reference' => 89
			),
			'format' => array(
				'item_id' => 'id',
				'item_descr' => 'description',
				'item_quant' => 'amount'
			)
		));

		$env = array(
			0 => array(
				'ShoppingCart' => array(
					'id' => 0,
					'description' => 'Product A',
					'amount' => 2,
					'price' => 1345.73,
					'freight' => 12.50,
					'weight' => '3,8',
					'color' => 'blue',
					'size' => 10,
					'file' => '/var/www',
					'status' => true,
					'created' => '0000-00-00 00:00:00',
					'updated' => '0000-00-00 00:00:00'
				)
			)
		);

		$this->PagSeguroComponentTest->__items = array();
		$this->PagSeguroComponentTest->create($env);

		$this->assertEqual(count($this->PagSeguroComponentTest->__items[1]), 3);
		$this->assertEqual($this->PagSeguroComponentTest->__items[1][2]['item_quant_1'], 2);
	}
}