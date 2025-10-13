import sys
moedas = [1,1,1,1,1,10,10,15] #lista de moedas
moedas.sort(reverse=True) #ordena a lista na ordem decrescente
print "Digite o valor a encontrar"
valor = int(raw_input())

for i in range(len(moedas)): #loop enquanto existem moedas a testar
	tmp = i+1 
	soma = moedas[i]
	res = []
	res.append(moedas[i]) #adiciona a primeira moeda na lista de solucao
	if soma < valor: #se a moeda for maior do que o valor nao precisa testar com as outras
		for j in range(tmp,len(moedas)): #faz um loop para testar com todas as outras moedas
			if (soma + moedas[j]) == valor: #se a soma mais o valor da moeda atual == valor 
				res.append(moedas[j]) #adiciona a moeda na solucao
				print "Solucao" 
				print res #mostra a solucao encontrada 
				sys.exit() #sai do sistema
			elif (soma+moedas[j]) > valor: #se a soma eh maior q o valor tenta somar com a proxima
				continue
			else:
				soma = soma + moedas[j] #se a soma eh menor q o valor adiciona a moeda na solucao
				res.append(moedas[j])
print "Nao eh possivel encontrar solucao"			
