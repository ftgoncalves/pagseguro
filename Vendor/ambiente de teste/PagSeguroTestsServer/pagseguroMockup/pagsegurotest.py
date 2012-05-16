# -*- encoding: utf-8 -*- 
import unittest
import pagseguro

pagtoURL='/Security/webpagamentos/webpagto.aspx'
retornoURL='/Security/NPI/Default.aspx'

def dados():
  return {
    "email_cobranca":["teste@teste.com.br"],
    "tipo":["CP"],
    "moeda":["BR"],
    "cliente_nome":["Joaquim José da Silva Xavier"],
    "cliente_cep":["01234567"],
    "cliente_end":["Rua dos Bobos, 0"],
    "cliente_bairro":["Paytown"],
    "cliente_cidade":["Payland"],
    "cliente_uf":["AC"],
    "cliente_pais":["BRA"],
    "cliente_tel":["55555555"],
    "cliente_email":["elcio@visie.com.br"],
    "item_id_1":["1"],
    "item_descr_1":["Enxugador de gelo"],
    "item_quant_1":["1"],
    "item_valor_1":["20"],
    "item_frete_1":["0"],
    "ref_transacao":["b612"],
  }


class MockupTest(unittest.TestCase):
  '''Testa o objeto Mockup'''

  def testExistance(self):
    '''A função process existe e retorna uma string'''
    self.assertEquals(str,type(pagseguro.process('',{})))

  def testValidPaymentTitle(self):
    '''O retorno de uma requisição válida de pagamento deve incluir um título.'''
    retorno=pagseguro.process(pagtoURL,dados())
    self.assertTrue('Pagamento processado.</h1>' in retorno)

  def testValidPaymentDump(self):
    '''O retorno de uma requisição válida de pagamento deve incluir um dump dos dados.'''
    retorno=pagseguro.process(pagtoURL,dados())
    self.assertTrue('ref_transacao="b612"' in retorno)

  def testValidPaymentForm(self):
    '''O retorno de uma requisição válida de pagamento deve incluir um formulário para teste do retorno automático.'''
    retorno=pagseguro.process(pagtoURL,dados())
    self.assertTrue('<form' in retorno)
    self.assertTrue('name="Referencia" value="b612"' in retorno)

def testNPIWithoutSlash(self):
    '''Testa se a confirmação do retorno automático (usando url sem /) vem como "VERIFICADO"'''
    retorno=pagseguro.process(retornoURL,dados())
    self.assertEquals('VERIFICADO',retorno)
 
def testNPIWithSlash(self):
    '''Testa se a confirmação do retorno automático (usando url com /)vem como "VERIFICADO"'''
    retorno=pagseguro.process(retornoURL + '/',dados())
    self.assertEquals('VERIFICADO',retorno)

if __name__=="__main__":
  unittest.main()

