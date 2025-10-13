/*Exercicios de Arquivos Indexados
implementar as funcoes que estao faltando
*/
#include <stdio.h>
#include <stdlib.h>

//struct com os dados da pessoa
typedef struct tpessoa {
	int codigo;
	char nome[100];
}pessoa;

//struct com o indice
typedef struct tindice {
	int codigo;
	int pos;
	int excluido;
}indice;

//arqivos de dados e indices
FILE *data, *idx;

//funcao que faz a inclusao
void inserir() {
	pessoa tmp;
	indice tmp_idx;
	int pos;
	printf("Digite o código:");
	scanf("%d",&tmp.codigo);
	printf("Digite o nome:");
	scanf("%s",&tmp.nome);
	//calcula a posicao
	fseek(data,0,SEEK_END);
	pos = (ftell(data) / sizeof(tmp));
	//grava
	fseek(data,pos*sizeof(pessoa),0);
	if(!fwrite(&tmp,sizeof(tmp),1,data)) {
		perror("erro gravando pessoa");
	}
    //grava no arquivo de indice
	tmp_idx.codigo = tmp.codigo;
	tmp_idx.pos = pos;
	tmp_idx.excluido = 0;
	printf("debug indice codigo=%d pos=%d\n",tmp_idx.codigo, tmp_idx.pos);
	if(!fwrite(&tmp_idx,sizeof(tmp_idx),1,idx)) {
		perror("erro gravando indice");
	}
	
}

//funcao que faz a pesquisa sequencial
int pesquisar() {
	indice tmp_idx;
	int k;
	printf("Digite o código a pesquisar:");
	scanf("%d",&k);
	rewind(idx);
	//procura em todos os elementos do indice
	while (fread(&tmp_idx, sizeof(indice), 1, idx)) { 
		if(tmp_idx.codigo == k && tmp_idx.excluido == 0) {
			printf("Encontrado na posição %d\n",tmp_idx.pos);
			return tmp_idx.pos;
		}
	}
	return -1;
	
}
//funcao que mostra os dados da pessoa 
void mostraDados(int pos) {	
	pessoa tmp;

	fseek(data,pos*sizeof(pessoa),0);
		
	if(!fread(&tmp, sizeof(pessoa), 1, data)) {
		perror("erro no mostraDados");
	}
	printf("Código: %d \t Nome: %s\n",tmp.codigo, tmp.nome);	
	
}

//funcao que faz a alteracao do nome
void alterar() {}

//funcao que exclui o registro
void excluir() {}

//funcao que faz a pesquisa binaria
int pesquisaBinaria() {}

//funcao que mostra os registros em ordem de codigo
void ordenar() {}


int main() {
    int op = 0;
	int pos = 0;
	data = fopen("pessoas.dat","r+b");
	if(!data) {
        data = fopen("pessoas.dat","a+b");
        if(!data) {
		   perror("Erro na abertura do arquivo de dados");
        }
        fclose(data);
        data = fopen("pessoas.dat","r+b");
	}
	idx = fopen("pessoas.idx","r+b");
	if(!idx) {
        idx = fopen("pessoas.idx","a+b");
        if(!idx)
		   perror("Erro na abertura do arquivo de indices");
        fclose(idx);
        idx = fopen("pessoas.idx","r+b");
	}

	while(op != 9) {
		printf("Escolha uma opção\n");
		printf("1-Inserir\n");
		printf("2-Pesquisar\n");
		printf("3-Alterar\n");
		printf("4-Excluir\n");
		printf("5-Ordenar\n");
		printf("6-Pesquisa Binária\n");
		printf("9-Sair\n");
		scanf("%d",&op);
		switch(op) {
			case 1:
				inserir();
				break;
			case 2:
				pos = pesquisar();
				if(pos == -1) {
					printf("Não encontrado\n");
				}
				else {
					mostraDados(pos);
				}
				break;
			case 3:
				alterar();
				break;
			case 4:
				excluir();
				break;
			case 5:
				ordenar();
				break;
			case 6:
				pos = pesquisaBinaria();
				if(pos == -1) {
					printf("Não encontrado\n");
				}
				else {
					mostraDados(pos);
				}
				break;
			case 9:
				exit(0);
		}
	}
	return 0;
}
