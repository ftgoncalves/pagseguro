# -*- encoding: utf-8 -*- 
import random,md5
from datetime import datetime
from settings import retornourl

template="""<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">

<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />

<title>%(titulo)s</title>

</head>

<body>
<h1>%(titulo)s</h1>
<pre>%(dump)s</pre>
<hr />
<form method="POST" action="%(url)s" target="_blank">
%(formdump)s
<input type="submit" value="Testar Retorno Automático" />
</form>

<hr />
<a href="%(url)s">Testar link de retorno para a loja</a>

</body>

</html>"""

def input(name,value):
  '''Gera um input hidden HTML'''
  return '<input name="%(name)s" value="%(value)s" type="hidden" />\n' % locals()

def select(name,values):
  '''Gera um input select HTML'''
  return '<label>'+name+': <select name="'+name+'">\n'+('\n'.join([
      '<option value="%s">%s</option>' % (i,i) for i in values
    ]))+'\n</select></label><br />\n'

def get(k,d,v):
  if k in d: return ''.join(d[k])
  return v

def process(path,data):
  '''Imita o PagSeguro'''
  global retornourl
  url=retornourl
  if path.lower()=='/security/webpagamentos/webpagto.aspx':
    titulo='Pagamento processado.'
    dump='\n'.join(sorted(['%s="%s"' % (k,'","'.join(v)) for k,v in data.iteritems()]))
    transid=md5.new(str(random.random())).hexdigest()
    prods=[i for i in data if i.startswith('item_id')]
    datamap={
      'TransacaoID':    transid,
      'TipoFrete':      'FR',
      'ValorFrete':     '0,00',
      'Anotacao':       'Pagamento gerado pelo ambiente de testes',
      'DataTransacao':  datetime.now().strftime('%d/%m/%Y %H:%M:%S'),
      'ValorFrete':     '0,00',
      'VendedorEmail':  'email_cobranca',
      'Referencia':     ''.join(data['ref_transacao']), #'ref_transacao',
      'CliNome':        'nome',
      'CliEmail':       'email',
      'CliEndereco':    'Rua dos Bobos',
      'CliNumero':      '0',
      'CliComplemento': '',
      'CliBairro':      'Paytown',
      'CliCidade':      'Payland',
      'CliEstado':      'AC',
      'CliCEP':         '01234567',
      'CliTelefone':    '99 55555555',
      'NumItens':       len(prods),
    }
    proddatamap={
      'ProdId':'item_id',
      'ProdDescricao':'item_descr',
      'ProdQuantidade':'item_quant',
      'ProdFrete':'0,00',
      'ProdExtras':'0,00',
      'ProdValor':'item_valor',
    }
    formdump=''.join([input(k,get(k,data,v)) for k,v in datamap.iteritems()])
    for i in filter(lambda a:'valor' in a,data):
      data[i][0]=("%.2f" % (int(data[i][0])/100.0)).replace(".",",")
    for prod in prods:
      prod_id=prod.replace('item_id','')
      for k,v in proddatamap.iteritems():
        if v+prod_id in data:v=data[v+prod_id][0]
        formdump+=input(k+prod_id,v)

    formdump+=select('TipoPagamento',(
      'Pagamento',
      'Cartão de Crédito',
      'Boleto',
      'Pagamento Online',
    ))
    formdump+=select('StatusTransacao',(
      'Completo',
      'Aguardando Pagto',
      'Aprovado',
      'Em Análise',
      'Cancelado',
    ))
    return template % locals()
  if path.lower().rstrip('/')=='/security/npi/default.aspx':
    return "VERIFICADO"
  return 'Unknown data'
