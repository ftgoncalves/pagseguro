# -*- encoding: utf-8 -*- 
'''Servidor de teste para o PagSeguro.'''
import socket, os, cgi
from SocketServer import BaseServer
from BaseHTTPServer import HTTPServer,BaseHTTPRequestHandler
from SimpleHTTPServer import SimpleHTTPRequestHandler
from OpenSSL import SSL

class SecureHTTPServer(HTTPServer):
    '''Servidor HTTPS com OpenSSL.'''
    def __init__(self, server_address, HandlerClass,fpem):
        BaseServer.__init__(self, server_address, HandlerClass)
        ctx = SSL.Context(SSL.SSLv23_METHOD)
        ctx.use_privatekey_file (fpem)
        ctx.use_certificate_file(fpem)
        self.socket = SSL.Connection(ctx, socket.socket(self.address_family,
                                                        self.socket_type))
        self.server_bind()
        self.server_activate()

class HTTPSHandler(BaseHTTPRequestHandler):

    def setup(self):
        self.connection = self.request
        self.rfile = socket._fileobject(self.request, "rb", self.rbufsize)
        self.wfile = socket._fileobject(self.request, "wb", self.wbufsize)

    def send(self,msg,code=200):
        '''Envia HTML para o client'''
        self.send_response(code)
        self.end_headers()
        self.wfile.write(msg)

    def do_POST(self):
        '''Responde a requisições POST'''
        if self.rfile:
            self.data=cgi.parse_qs(self.rfile.read(int(self.headers['Content-Length'])))
        else:
            self.data={}
        ret=self.process()
        self.send(ret)

    def process(self):
        '''Processa a requisição. Sobrescreva este método em suas subclasses.'''
        return "Hello world!"

    def do_GET(self):
        '''Apenas mostra uma mensagem de erro, uma vez que não deveríamos mesmo usar GET.'''
        self.send("Why GETting?")

#server.pem's location (containing the server private key and
#the server certificate).
fpem = 'server.pem'

def run(HandlerClass = HTTPSHandler,
         ServerClass = SecureHTTPServer):
    '''Roda o servidor'''
    server_address = ('', 443) # (address, port)
    httpd = ServerClass(server_address, HandlerClass, fpem)
    httpd.serve_forever()

if __name__ == '__main__':
    run()

