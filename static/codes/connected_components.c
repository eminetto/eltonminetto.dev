#include <stdio.h>
#include <stdlib.h>

#define MAX 10 //maximo de elementos da lista

typedef struct varesta{ //struct representando a aresta
     int u;
     int v;
}aresta;

typedef struct nlista
{
   int prox[MAX];      	//Proximo elemento da lista
   int ant[MAX];      	//Elemento anterior da lista
   int info[MAX];	//Informacao
   int prim;		//primeiro elemento
   int disp;		//disponivel
}lista;


int v[MAX]; //vetor com os vertices do grafo
int num_arestas; //numero de arestas

lista listas[MAX]; //vetor com as listas usadas no programa

aresta arestas[MAX]; //vetor com todas as arestas

/* funcao que mostra todos os elementos da lista*/
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

/*funcao que retorna a lista onde esta o elemento x*/
lista find_set(int x){
	lista ret;
	int i,j;
	for(j=0;j<MAX;j++){ //caminha pelo vetor de listas
		for(i=0;i<listas[j].disp;i++){ //caminha pelos elementos da lista
			if(listas[j].info[i]==x){
				ret=listas[j];
			}
		}
	}	
	return ret;
}

/* funcao que retorna o indice da lista que contem x no vetor de listas*/
int find_set_id(int x){
	int ret;
	int i,j;
	for(j=0;j<MAX;j++){//caminha pelo vetor de listas
		for(i=0;i<listas[j].disp;i++){//caminha pelos elementos da lista
			if(listas[j].info[i]==x){
				ret=j;
			}
		}
	}	
	return ret;
}


/* funcao que faz a uniao*/
lista uniao(int x,int y) {
	//busca as listas de x e y
	lista lx = find_set(x);
	lista ly = find_set(y);
	
	if (lx.info[lx.prim] == ly.info[ly.prim]) {
		printf("He a mesma lista\n");
		return lx;
	}
	int i;
	//adiciona a lista x no final da lista y
	ly.prox[ly.disp-1] = ly.disp; //o ultimo elemento de y aponta para x
	for(i=0;i<lx.disp;i++) {
		ly.info[ly.disp] = lx.info[i];//adiciona o elemento de x no disp. de y
		ly.disp++; //incrementa o disp de y
		ly.ant[ly.disp] = ly.prim; //os anteriores dos novos elementos apontam para o representante
		ly.prox[ly.disp-1] = ly.disp;//o proximo elemento da lista aponta para o disp
	}
	ly.prox[ly.disp-1] = -1;//corrige o ultimo elemento para apontar para -1=fim
	return ly; //retorna a nova lista
}

/* funcao que atualiza as listas para representar a conexao dos componentes*/
void connected_component(){
	int i;
	/* loop que cria uma nova lista para cada vertice do grafo*/
	for(i=0;i<MAX;i++){
		listas[i]=make_set(i);
		printf("lista %d\n",i);
		mostraTodos(listas[i]);	
	}
	for(i=0;i<num_arestas;i++){ //caminha pelas arestas
		//se as listas onde estao as arestas forem diferentes une as listas
		if(find_set(arestas[i].u).info[0] != find_set(arestas[i].v).info[0]){
			//guarda a lista contendo a uniao na lista de v e de u
			listas[find_set_id(arestas[i].u)]=uniao(arestas[i].u,arestas[i].v);
			listas[find_set_id(arestas[i].v)]=uniao(arestas[i].u,arestas[i].v);
		}
	}
	/* loop que mostra as listas apos as unioes*/
	for(i=0;i<MAX;i++){
		printf("lista %d depois da uniao\n",i);
		mostraTodos(listas[i]);	
	}
	
}

/* funcao que verifica se dois vertices estao no mesmo componente do grafico*/
int same_componnent(int x, int y) {
	if(find_set(x).info[0] == find_set(y).info[0])
		return 1;
	else
		return 0;
}


int main()  {
	int i,x,y;
	//alimenta o vetor de vertices
	for(i=0;i<MAX;i++){
		v[i]=i;
	}
	//alimenta o vetor com as arestas	
	arestas[0].u=1;
	arestas[0].v=2;
	arestas[1].u=1;
	arestas[1].v=3;
	arestas[2].u=1;
	arestas[2].v=4;
	arestas[3].u=2;
	arestas[3].v=3;
	arestas[4].u=4;
	arestas[4].v=6;
	num_arestas=5;
	//mostra as arestas
	printf("Arestas\n");
	for(i=0;i<num_arestas;i++){
		printf("Aresta %d:%d,%d\n",i,arestas[i].u,arestas[i].v);
	}
	connected_component();
	printf("Digite os vertices a pesquisar\n");
	scanf("%d",&x);
	scanf("%d",&y);
	if(same_componnent(x,y))
		printf("Os vertices %d e %d estao no mesmo componente",x,y);
	else
		printf("Os vertices %d e %d nao estao no mesmo componente",x,y);		
	return 0;
}
