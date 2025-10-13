#include <stdio.h>
#include <stdlib.h>

#define MAX 10 //máximo de elementos da lista

/*definição da lista*/
typedef struct nlista
{
   int prox[MAX];      	//Próximo elemento da lista
   int ant[MAX];      	//Elemento anterior da lista
   int info[MAX];	//Informação
   int prim;		//primeiro elemento
   int disp;		//disponivel
}lista;


lista l1;  //primeira lista
lista l2;  //segunda lista

/* funcao que cria uma nova lista*/
lista make_set(int x) {
	lista nova;
	nova.prim = 0;
	nova.ant[0] = 0;
	nova.info[0] = x;
	nova.prox[0]=-1;
	nova.disp = 1;
	return nova;
}

/* funcao que cria a opcao de busca*/
lista find_set(int x) {
	int i;
	for(i=0;i<=l1.disp;i++){
		if (l1.info[i] == x)
			return l1;
	}
	for(i=0;i<=l2.disp;i++){
		if (l2.info[i] == x)
			return l2;
	}
}
/* funcao que faz a uniao*/
lista union_set(int x,int y) {
	//busca as listas de x e y
	lista lx = find_set(x);
	lista ly = find_set(y);
	
	if (lx.info[lx.prim] == ly.info[ly.prim]) {
		printf("É a mesma lista\n");
		return lx;
	}
	int i;
	//adiciona a lista x no final da lista y
	ly.prox[ly.disp-1] = ly.disp; //o último elemento de y aponta para x
	for(i=0;i<lx.disp;i++) {
		ly.info[ly.disp] = lx.info[i];//adiciona o elemento de x no disp. de y
		ly.disp++; //incrementa o disp de y
		ly.ant[i] = ly.prim; //os anteriores dos novos elementos apontam para o representante
		ly.prox[ly.disp-1] = ly.disp;//o próximo elemento da lista aponta para o disp
	}
	ly.prox[ly.disp-1] = -1;//corrige o último elemento para apontar para -1=fim
	return ly; //retorna a nova lista
}

/*funcao que faz a uniao ponderada*/
lista uniao_ponderada(int x,int y){
	lista lx = find_set(x);
	lista ly = find_set(y);

	if(lx.disp > ly.disp)
		return union_set(y,x);
	else
		return union_set(x,y);
	
}
/* função que mostra todos os elementos da lista*/
void mostraTodos(lista l) {
	int i;
	if(l.prim == -1) {
		printf("Lista vazia");
		exit(1);
	}
	else {
		printf("Pos\tAnt\tInfo\tProx\n");
		for(i=0;i!=-1;i=l.prox[i]) {
			printf("%d\t%d\t%d\t%d\n",i,l.ant[i],l.info[i],l.prox[i]);
		}
		printf("Disp:%d\tPrim:%d\n",l.disp,l.prim);
	}
}
int main() {
	int x,y;
	//cria a lista l1 e l2;
	l1.prim = 0;
	l1.info[0] = 1;
	l1.info[1] = 2;
	l1.info[2] = 3;
	l1.ant[0] = 0;
	l1.ant[1] = 0;
	l1.ant[2] = 0;
	l1.prox[0] = 1;
	l1.prox[1] = 2;
	l1.prox[2] = -1;
	l1.disp = 3;

	l2.prim = 0;
	l2.info[0] = 4;
	l2.info[1] = 5;
	l2.info[2] = 6;
	l2.info[3] = 7;
	l2.ant[0] = 0;
	l2.ant[1] = 0;
	l2.ant[2] = 0;
	l2.ant[3] = 0;
	l2.prox[0] = 1;
	l2.prox[1] = 2;
	l2.prox[2] = 3;
	l2.prox[3] = -1;
	l2.disp = 4;

	printf("Lista 1\n");
	mostraTodos(l1);;
	printf("Lista 2\n");
	mostraTodos(l2);
	printf("Digite os dois elementos das listas a unir:");
	scanf("%d",&x);
	scanf("%d",&y);
	printf("Uniao das listas com os elementos %d e %d\n",x,y);
	mostraTodos(union_set(x,y));
	printf("Uniao ponderada das listas com os elementos %d e %d\n",x,y);
	mostraTodos(uniao_ponderada(x,y));
	return(0);
}
