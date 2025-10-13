#!/usr/bin/python
import Grouper as grouper
g = grouper.Grouper()
print "Unindo a e b"
g.join('a', 'b')
print "Unindo b e c"
g.join('b', 'c')
print "Unindo d e e"
g.join('d', 'e')
print "Listas"
print list(g.get())
#verificando se os dois elementos estão no mesmo conjunto
print "Mesmo componente a e b "+str(g.joined('a', 'b')) 
print "Mesmo componente a e c "+str(g.joined('a', 'c'))
print "Mesmo componente a e d "+str(g.joined('a', 'd'))
