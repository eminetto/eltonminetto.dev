from sets import Set #importa as funcoes de conjuntos
#cria tres conjuntos
engenheiros = Set(['John', 'Jane', 'Jack', 'Janice']) 
programadores = Set(['Jack', 'Sam', 'Susan', 'Janice'])
gerentes = Set(['Jane', 'Jack', 'Susan', 'Zack'])
empregados = engenheiros | programadores | gerentes           # uniao
engenheiros_gerentes = engenheiros & gerentes            # interseccao
somente_gerente = gerentes - engenheiros - programadores # diferenca
engenheiros.add('Marvin')    # adiciona um engenheiro
print "Engenheiros"
print engenheiros
print "Programadores"
print programadores
if empregados.issuperset(engenheiros):           # superset
	print "Engenheiros esta contido em empregados"
else:
	print "Engenheiros nao esta contido em empregados"
empregados.union_update(engenheiros)         # atualiza a uniao
if empregados.issuperset(engenheiros):
	print "Engenheiros esta contido em empregados"
	print empregados
print "Imprimindo os conjuntos sem o elemento Susan "
for group in [engenheiros, programadores, gerentes, empregados]:
	group.discard('Susan') # remove o elemento susan
	print group
