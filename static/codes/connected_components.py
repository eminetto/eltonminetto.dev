#!/usr/bin/python
import Grouper as grouper
g = grouper.Grouper()

#funcao connected_components verifica se os dois vértices da aresta do grafo
#estão no mesmo conjunto. Se não estiverem ele faz a junção em um único 
#conjunto
def connected_components(arestas):
	for i in arestas:
		if not g.joined(i[0],i[1]):
			g.join(i[0],i[1])
	print "Mostrando os conjuntos"
	print list(g.get())

#a função same_component responde se dois vértices estão conectados no 
#mesmo componente
def same_component(x,y):
	if g.joined(x,y):
		print "Estao conectados"
	else:
		print "Nao estao conectados"

def main():
	arestas = [] #cria uma lista de arestas para poder testar 
	arestas.append(['a','b'])
	arestas.append(['a','c'])
	arestas.append(['b','c'])
	arestas.append(['b','d'])
	arestas.append(['e','f'])
	arestas.append(['e','g'])
	arestas.append(['h','i'])
	connected_components(arestas)
	x = ''
	while x != 'fim':
		print "Digite as arestas para verificar se estao conectadas. Digite 'fim' para terminar"
		x = raw_input()
		if x == 'fim':
			break
		y = raw_input()
		same_component(x,y)

## Invoca a funcao main na inicializacao do programa
if __name__ == '__main__':
	main()
