#!/usr/bin/python
import socket
import string
import sys
import commands

HOST= ''
PORT = 50007
SERVER_VALIDO = sys.argv[1]

s = socket.socket(socket.AF_INET,socket.SOCK_STREAM)
s.bind((HOST, PORT))
s.listen(1)
conn, addr = s.accept()
tmp = string.find(addr[0],SERVER_VALIDO)
if tmp == 0:
	print 'Servidor OK'
else:
	print 'Servidor inválido', addr[0]
	sys.exit()
while 1:
	data = conn.recv(1024)
	if not data: break
	tmp = string.find(data,'Desligar')
	if tmp == 0:
		print commands.getstatusoutput("/sbin/shutdown -h now")
	conn.send(data)
conn.close()
