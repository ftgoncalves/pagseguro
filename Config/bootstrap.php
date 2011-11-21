<?php
// Relação entre número do código e seu nome
Configure::write('PagSeguro.Notifications.StatusCode', array(
	1 => 'Aguardando pagamento',
	2 => 'Em análise',
	3 => 'Paga',
	4 => 'Disponível',
	5 => 'Em disputa',
	6 => 'Devolvida',
	7 => 'Cancelada'
));