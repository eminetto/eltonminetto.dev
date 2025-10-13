def busca(array, target):
	for i in array:
		if i == target:
			return i
array = []
for i in range(5000):
	array.append(i)
array.sort()
print "Digite o valor a pesquisar"
target = int(raw_input())
print busca(array, target)
