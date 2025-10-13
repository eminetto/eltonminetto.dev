import timeit
print "Execucao do forca_bruta"
t = timeit.Timer("buscaForcaBruta","import buscaForcaBruta")#script a ser testado
x = t.repeat() # o repeat vai executar 3 vezes o script e guardar o resultado de cada execucao
media = 0
for i in x:
	media += i
print "Tempo %s" % x
print "Media de Tempo %s" % (media/3) #faz a media das execucoes
print "Execucao do divisao e conquista"
t = timeit.Timer("buscaDivisaoConquista","import buscaDivisaoConquista")
x = t.repeat()
media = 0
for i in x:
	media += i
print "Tempo %s" % x
print "Media de Tempo %s" % (media/3)
