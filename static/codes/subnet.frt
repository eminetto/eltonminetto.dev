\ Calcula as subnets possiveis em uma rede Classe C, a apartir da mascara de rede passada
\ Elton Luis Minetto <elm@unochapeco.edu.br>

comment:
  Calcula todas as informacoes sobre sub-redes de acordo com a mascara de rede desejada.
  Exemplo de uso : 224 subnet ( Calcula todas as informacos da mascara de rede 255.255.255.224)

  Os valores validos como entrada sao : 128,192,224,240,248, e 252. Sao todas as mascaras de rede de uma Classe C.

  A tabela abaixo mostra um exemplo com as informacoes das principais subnets usadas em uma rede classe C.
  A finalidade do programa he montar as informacoes contidas neste exemplo de acordo com a mascara.

 Netmask         Subnets Network B'cast  MinIP   MaxIP   Hosts  Total Hosts
 --------------------------------------------------------------------------
     128            2       0     127       1     126    126
                          128     255     129     254    126     252

     192            4       0      63       1      62     62
                           64     127      65     126     62
                          128     191     129     190     62
                          192     255     193     254     62     248

     224            8       0      31       1      30     30
                           32      63      33      62     30
                           64      95      65      94     30
                           96     127      97     126     30
                          128     159     129     158     30
                          160     191     161     190     30
                          192     223     193     222     30
                          224     255     225     254     30     240


Referencias Bibliograficas
        BERNHARDT, Muriel de Fatima. FORTH Principios Basicos e Experimentacao Remota.UFSC, 2002
        IP Subnetworking. http://www.tldp.org/HOWTO/mini/IP-Subnetworking.html. 11/03/2003

comment;

\ Monta a barra para separar o cabecalho dos dados
: barra  cr 100 0 do 42 emit loop ;

\ Limpa todos os elementos da fila deixando apenas o topo
: limpa_fila_topo dup depth 1 - 0 do swap drop loop ;

\ Limpa todos os elementos da fila deixando apenas o primeiro
: limpa_fila_primeiro dup depth 1 - 0 do drop loop ;

\ Calcula o numero de possiveis sub-redes a patir da mascara
\ Calculo : 256 / (256 - mascara)
: num_subnet dup 256 swap - 256 swap / ;

\ Calcula o numero de hosts possiveis na rede
\ Calculo : 256 - mascara - 2 (o endereco de rede e o endereco de broadcast)
: num_hosts dup 256 swap - 2 -  ;

\ Calcula o total de hosts nas sub-redes
\ Calculo : Numero de sub-redes nultiplicado pelo numero de hosts
: total_hosts dup num_subnet swap num_hosts rot * ;

\ Calcula os enderecos network possiveis para a mascara
\ Definicao : Para calcular os enderecos de network usa-se um loop sendo que o primeiro endereco he sempre 0 e
\ o ultimo he sempre a propria mascara de rede. O endereco he sempre o ultimo endereco somado do numero de hosts
\ mais 2 (endereco de rede + broadcast)
: endereco_network
        0 . tab ( primeiro endereco he sempre o 0)
        dup
        num_hosts
        swap
        num_subnet 1- 0 do
                drop 2 + dup . tab swap num_hosts rot + dup
        loop
        ;

\ Calcula os enderecos de broadcast possiveis para a mascara
\ Definicao : O primeiro endereco de broadcast he sempre o numero de hosts + 1, os seguintes calcula-se
\ adicionando o numero de hosts + 2 ao endereco anterior
: endereco_broadcast
        dup
        num_hosts
        1 + dup . \ O primeiro endereco de broadcast he sempre o numero de hosts + 1
        tab
        swap
        num_subnet 1- 0 do
                drop 2 + over num_hosts rot + dup . tab dup
        loop
        ;


\ Calcula o menor endereco ip disponivel na subrede
\ Definicao : O menor endereco ip disponivel da subnet he sempre o endereco de network + 1
: min_endereco
        1 . tab ( primeiro endereco he sempre o 1)
        dup
        num_hosts
        swap
        num_subnet 1- 0 do
                drop 2 + dup 1 + . tab swap num_hosts rot + dup
        loop
        ;
\ Calcula o maior endereco ip disponivel na subrede
\ Definicao : O maior endereco ip disponivel da subnet he sempre o endereco de broadcast - 1
: max_endereco
        dup
        num_hosts
        1 + dup 1 - .
        tab
        swap
        num_subnet 1- 0 do
                drop 2 + over num_hosts rot + dup 1 - . tab dup
        loop
        ;


: subnet
        limpa_fila_topo
        dup
        cr cr ." MASCARA " .
        barra cr
        ." Total de Subnets = " num_subnet . cr
        ." Hosts por Subnet = "  num_hosts . cr
        ." -------------------Redes --------------------------------------------- " cr
        ." Enderecos Network      = " endereco_network cr
        ." Enderecos Broadcast    = " limpa_fila_primeiro endereco_broadcast  cr
        ." Menor Endereco da Rede = " limpa_fila_primeiro min_endereco  cr
        ." Maior Endereco da Rede = " limpa_fila_primeiro max_endereco cr
        ." ---------------------------------------------------------------------- " cr
        ." Total de Hosts  = " limpa_fila_primeiro total_hosts . cr
        barra cr ;
