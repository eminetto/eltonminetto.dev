from random import *
from copy import *
moedas = [1,1,1,1,1,10,10,15] #lista de moedas
print "Digite o valor a encontrar"
valor = int(raw_input())

soma = 0
res = [] #lista de resultados
while len(res) < 1000: #total de testes para encontrar as solucoes
	x = [] #lista q armazena uma solucao	
	tmp = copy(moedas) # lista temporaria
	while tmp: 
		j = choice(tmp) #escolhe aleatoriamente uma das moedas
		tmp.remove(j) #remove o valor escolhido para q nao seja escolhido novamente
		soma = soma + j
		x.append(j)  #adiciona a moeda a solucao atual
		if soma == valor:
			x.sort() #ordena as moedas
			res.append(x) #adiciona a solucao na lista de resultados
			break
	soma = 0

print "Solucoes encontradas"
#algoritmo para remover as duplicacoes, tambem usando forca bruta
u = []
for x in res:
	if x not in u:
		u.append(x)
for i in u:
	print "Solucao %s"  % i
#encontra a melhor solucao
tmp = 999
melhor = []
for i in u:
	if len(i) < tmp:
		tmp = len(i)
		melhor = i
print "A melhor solucao eh"
print melhor

