from HTTPSServer import *
from pagseguroMockup import pagseguro

class PagSeguroHandler(HTTPSHandler):
  def process(self):
    return pagseguro.process(self.path,self.data)

if __name__=="__main__":
  run(PagSeguroHandler,SecureHTTPServer)


