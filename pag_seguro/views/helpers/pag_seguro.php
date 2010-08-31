<?php
/* SVN FILE: $Id:$ */
/**
 * BrainStern Soluções Ltda - http://www.brainstern.com/
 * E-mail: contato@brainstern.com
 *
 * @created: 27/08/2010
 * @version: $Rev:$
 * @author: $Author:$
 * @LastChangedDate: $Date:$
 * @link: $HeadURL:$
 */
class PagSeguroHelper extends AppHelper {

	var $helpers = array('Form');

	var $data = array();

	var $settings = array(
		'theme' => 1,
		'alt' => 'Pague com o PagSeguro',
		'type' => 'image',
		'src' => 'https://pagseguro.uol.com.br/Security/Imagens/',
		'value' => 'PagSeguro'
	);

	function form($data){
		$this->data = $data;
		return $this->output('<form target="pagseguro" method="post" action="https://pagseguro.uol.com.br/checkout/checkout.jhtml" accept-charset="'.$data['init']['definitions']['encode'].'">');
	}

	function data(){
		$this->__echoInit();

		foreach($this->data['init']['customer'] as $field => $value)
			if(!is_null($value))
				echo '<input type="hidden" value="'.$value.'" name="'.$field.'" />';

		foreach($this->data['data'] as $item)
			foreach($item as $key => $att)
				foreach($att as $key2 => $val)
					echo '<input type="hidden" value="'.$val.'" name="'.$key2.'" />';
	}

	function submit($settings = array()){
		$this->settings = array_merge($this->settings, $settings);

		$this->settings['div'] = false;
		$theme = $this->settings['theme'];
		unset($this->settings['theme']);

		if($this->settings['src'] != false)
			$src = $this->settings['src'].$this->__getTheme($theme);
		else if ($theme == false)
			$src = $this->settings['src'];
		else
			$src = null;
		unset($this->settings['src']);

		return $this->output($this->Form->submit($src, $this->settings). '</form>');
	}

	function __echoInit(){
		echo '<input type="hidden" value="'.$this->data['init']['pagseguro']['email'].'" name="email_cobranca" />';
		echo '<input type="hidden" value="'.$this->data['init']['pagseguro']['type'].'" name="tipo" />';
		echo '<input type="hidden" value="'.$this->data['init']['pagseguro']['currency'].'" name="moeda" />';
		echo '<input type="hidden" value="'.$this->data['init']['pagseguro']['reference'].'" name="ref_transacao" />';
		echo '<input type="hidden" value="'.$this->data['init']['pagseguro']['freight_type'].'" name="tipo_frete" />';
		echo '<input type="hidden" value="'.$this->data['init']['definitions']['encode'].'" name="encoding" />';
		echo '<input type="hidden" value="'.$this->data['init']['pagseguro']['extra'].'" name="extras" />';
	}

	function __getTheme($opt){
		switch ($opt) {
			case 1:
				return 'btnComprarBR.jpg';
			case 2:
				return 'btnPagarBR.jpg';
			case 3:
				return 'btnPagueComBR.jpg';
			case 4:
				return 'btnComprar.jpg';
			case 5:
				return 'btnPagar.jpg';
			default:
				return 'btnComprarBR.jpg';
        }
	}
}