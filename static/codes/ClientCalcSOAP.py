#!/usr/bin/python
from SOAPpy import SOAPProxy

server = SOAPProxy('http://localhost:8081/')
print '2 + 2 = ' + str(server.calcula(2,2,'+'))
print '5 - 2 = ' + str(server.calcula(5,2,'-'))
print '2 * 2 = ' + str(server.calcula(2,2,'*'))
print '6 / 2 = ' + str(server.calcula(6,2,'/'))

