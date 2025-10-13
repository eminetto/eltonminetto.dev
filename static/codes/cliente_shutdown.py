#!/usr/bin/python
import socket
import sys

HOST= sys.argv[1]
PORT=50007
s = socket.socket(socket.AF_INET, socket.SOCK_STREAM)
s.connect((HOST, PORT))
s.send('Desligar')
data = s.recv(1024)
s.close()
print 'Desligando Servidor', data
