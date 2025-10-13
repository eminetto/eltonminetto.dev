#!/usr/bin/python
import socket
import os
import string
import exceptions
import sys

## Variaveis globais
mySock = 0
## Path onde estao os sockets
SPATH='/tmp/bully'

## Funcao q manda mensagem
def sendMsg(dest,msg):
	global mySock
	try:
		ns = socket.socket(socket.AF_UNIX,socket.SOCK_STREAM)
		ns.connect((dest))
		ns.send(msg+';'+mySock)
		ns.close()
		return 1
	except:
		return 0
## Funcao q fica se comunicando com o coordenador para ver se esta ativo
def verificaCoord(coord):
	global SPATH
	global mySock

	while 1:
		## Verifica se o coordenador esta ativo
		status = sendMsg(coord,'1')
		if status == 0:
			print 'Coodenador parado '+str(coord)+'. Iniciando Eleicao'
			list = os.listdir(SPATH)
			for i in list:
				if string.atoi(i) > string.atoi(mySock):
					status = sendMsg(i,'E')
					if status == 0:
						print 'Erro enviando E ao processo '+i
			break
		else:
			print 'Conectou no coordenador '+coord
	return 0

def main():
	## Variaveis
	fCord = 1
	BUF=1024

	global mySock
	global SPATH
	try:
		## Se recebeu um argumento he um processo recussitado
		mySock = sys.argv[1]
		## Testa se existem processos com prioridade maior
		list = os.listdir(SPATH)
		for i in list:
			if string.atoi(i) > string.atoi(mySock):
				status = sendMsg(i,'1')
				if status == 1:
					fCord = 0 ## Se houver processos maiores, nao he coordenador
					continue
		## Se for o maior anuncia a todos
		if fCord == 1:
			for i in list:
				if string.atoi(i) < string.atoi(mySock):
					status = sendMsg(i,'C')
	except:
		## Nome do socket = pid do processo
		mySock=str(os.getpid())
		fCord = 0
	print 'Iniciando. PID='+str(mySock)
	## Verifica se existe o diretorio para os sockets
	try:
		os.chdir(SPATH)
	except:
		## Se nao existe cria o diretorio
		os.mkdir(SPATH)
	try:
		## Cria o socket
		s = socket.socket(socket.AF_UNIX,socket.SOCK_STREAM)
		s.bind((mySock)) ## Atribui um nome ao socket
		s.listen(1) ## Estabelece fila para conexoes
		## Inicialmente o menor PID he o coordenador
		list = os.listdir(SPATH)
		## Ordena os pids e pega o primeiro
		list.sort(cmp)
		if list:
			COORD=list[0]
		else:
			COORD=mySock
		## Nao he o coordenador
		if COORD != mySock and fCord == 0:
			## Divide o processo
			childPID = os.fork()
			if childPID == 0:
				## O filho permanece testando o coordenador
				verificaCoord(COORD)
				#sys.exit(1)
		else:
			COORD = mySock
			print 'Eu sou o coordenador '+str(mySock)
		## Todos os processos pais permanecem em wait, aguardando conexao
		conn, addr = s.accept()
		data = conn.recv(BUF)
		while 1:
			if data:
				msg = string.split(data,';')
				## Se a mensagem recebida for de eleicao
				if msg[0] == 'E':
					print 'Recebido mensagem '+msg[0]+' de PID='+str(msg[1])
					## Enviar msg para pid menor parar eleicao
					sendMsg(msg[1],'PE')
					#procura se tem alguem maior q ele
					list = os.listdir(SPATH)
					maior = 1
					for i in list:
						if string.atoi(i) > string.atoi(mySock):
							#print 'Enviar para PID '+i
							status = sendMsg(i,'E')
							if status == 1:
								maior = 0
					#se for o maior envia msg para todos como novo coord.
					if maior == 1:
						print 'Eu sou o novo Coordenador '+str(mySock)
						os.kill(childPID,9)
						for i in list:
							if string.atoi(i) < string.atoi(mySock):
								print 'enviando C para '+str(i)
								send = sendMsg(i,'C')

				## Recebeu msg para parar eleicao
				if msg[0] == 'PE':
					print 'Recebida mensagem '+msg[0]+' de PID='+str(msg[1])
				## Recebeu msg de verificacao de conexao
				#if msg[0] == '1':
				#	print 'Recebida mensagem '+msg[0]+' de PID='+str(msg[1])
				## Recebeu msg de coordenador
				if msg[0] == 'C':
					print 'Novo coordenador '+msg[1]
					os.kill(childPID,9)
					childPID = os.fork()
					if childPID == 0:
						## Novo filho gerado monitora o novo coordenador
						verificaCoord(msg[1])
						#sys.exit(1)
				data = conn.recv(BUF)
			else:
				conn, addr = s.accept()
				data = conn.recv(BUF)
	except:
		print "Unexpected error:", sys.exc_info()[0]
		s.close()
		os.remove(SPATH+'/'+str(mySock))
		print 'Processo '+str(mySock)+' parando'


## Invoca a funcao main na inicializacao do programa
if __name__ == '__main__':
	main()

