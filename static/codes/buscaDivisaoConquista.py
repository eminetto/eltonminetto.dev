def binarySearch(array, target, i, n):
	if n == 0:
		return None
	if n == 1:
		if array[i] == target:
			return i
		return None
	else:
		j = i + (n / 2)
		if array[j] <= target:
			return binarySearch(array, target, j, n-n/2)
		else:	
			return binarySearch(array, target, i, n/2)

array = []
for i in range(5000):
	array.append(i)
array.sort()
print "Digite o valor a pesquisar"
target = int(raw_input())
print binarySearch(array, target, 0, len(array))
