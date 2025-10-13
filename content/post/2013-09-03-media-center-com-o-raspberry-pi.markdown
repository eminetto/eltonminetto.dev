---
categories:
- toys
comments: true
date: 2013-09-03T00:00:00Z
title: Media center com o Raspberry Pi
url: /2013/09/03/media-center-com-o-raspberry-pi/
---

Recentemente comprei um brinquedinho nerd, o [Raspberry Pi](http://www.raspberrypi.org).

[![](/images/posts/rasp1.jpg)](/images/posts/rasp1.jpg)

Para quem andou em Marte no último ano e não conhece, ele é um micro-PC, um computador do tamanho de um cartão de crédito que vem sendo usado para diversas coisas legais como automação residencial, servidores de rede, estações de trabalho para escolas, etc.  Recomendo esse [vídeo](http://globotv.globo.com/globo-news/globonews-ciencia-e-tecnologia/t/todos-os-videos/v/menor-computador-do-mundo-e-vendido-a-preco-de-binquedo/2596762/) da Globo News, com uma entrevista com um dos criadores do projeto. 

O motivo pelo qual eu comprei o aparelho não foi nada tão nobre quanto ensinar crianças a programar, foi apenas montar um media center para poder assistir videos e ouvir algumas músicas sem precisar ligar o Macbook na TV. 

<!--more-->

#O hardware

O primeiro passo foi comprar o próprio Raspberry Pi. A parte triste é que um equipamento que custa U$ 35 lá nos EUA não sai por menos de R$ 250. Eu [encontrei um no Mercado Livre](http://produto.mercadolivre.com.br/MLB-501587281-raspberry-pi-b-512mb-hdmircausblan-case-transparente-_JM#D[S:VIP,L:SELLER_ITEMS,V:1]) por R$ 330, com a placa, case transparente e um cartão de memória de 4GB, frete incluso. 

[![](/images/posts/rasp2.jpg)](/images/posts/rasp2.jpg)

Como o Raspberry Pi não tem energia suficiente para energizar um dispositivo USB eu comprei um case para HD com alimentação própria. Existem várias opções no mercado e eu comprei um [direto do Deal Extreme](http://dx.com/p/2-5-3-5-docking-station-with-one-touch-backup-2-usb-sd-mmc-ms-tf-m2-card-reader-esata-34788) que suporta tanto HDs de notebook quanto os de PC. Usei um velho HD de 160GB de notebook que eu tinha. 

O kit que eu comprei não veio com cabo de energia, então precisei comprar um cabo micro-USB em uma loja de celulares. É basicamente o mesmo cabo da maioria dos celulares Android, então não deve ser difícil encontrar isso, além de ser barato. 

Para controlar o dispositivo você pode usar um teclado e mouse USB (ele possui duas entradas) mas o mais legal é que como eu liguei ele via HDMI na TV e ele suporta o  [CEC](http://en.wikipedia.org/wiki/Consumer_Electronics_Control#CEC) é possível usar o controle remoto padrão da TV para fazer isso.  A minha TV é uma Sony Bravia de 32 polegadas e implementa o CEC com a opção Bravia Link. Neste [link](http://wiki.xbmc.org/index.php?title=CEC) é possível ver outras TVs que suportam a mesma característica. 

E para finalizar, liguei no meu velho home theater Sony para poder ter um som legal. Pena que o Raspberry Pi não tem uma saída de áudio digital, mas isso já seria pedir demais para um dispositivo tão pequeno. 

# O Software

Existem algumas distribuições Linux compatíveis como o [Raspbian](http://pt.wikipedia.org/wiki/Raspbian) e é possível inclusive instalar o Android pois o Raspberry Pi usa uma arquitetura ARM. Eu optei pelo [Openelec](http://openelec.tv) uma distribuição Linux construída com o objetivo específico de ser um media center, vindo com o ótimo [XBMC](http://xbmc.org) instalado. No site do Openelec constam as instruções de como instalá-lo, mas basicamente você precisa baixar uma imagem e gravar no cartão SD com alguns comandos no Linux ou Mac (tem documentações de como fazer isso no Windows no site). 

Ao ligar o dispositivo ele automaticamente carrega o XBMB e dá as opções de assistir seus videos, ver fotos e ouvir músicas, permitindo escolher o HD externo ou outros serviços de rede como Samba. Se você tiver um dispositivo com iOS ele também emula o protocolo Airplay, então é possível mandar um video sendo tocado no iPhone direto para a tela da TV, ou mesmo jogar alguns jogos.

[![](/images/posts/rasp3.jpg)](/images/posts/rasp3.jpg)

O XBMC também possui um sistema de plugins muito interessante, com diversas opções para melhorar ainda mais a experiência e adicionar novas funcionalidades. Eu instalei os plugins Youtube, Apple Podcasts (permite navegar, assinar e ouvir podcasts),  Apple Trailers (pesquisar e assistir os trailers que a Apple libera dos filmes lançados na iTunes Store) e Rdio, que me permite ouvir as músicas do meu [serviço de streaming favorito](http://eltonminetto.net/blog/2012/11/29/oi-rdio/). Tive alguns problemas com o plugin do Rdio e precisei fazer um pequeno hack no código (escrito em Python) para funcionar, mas algumas atualizações depois isso parece ter sido corrigido (se o aparelho estiver conectado na rede os plugins são atualizados regularmente). 

[![](/images/posts/rasp4.jpg)](/images/posts/rasp4.jpg)

Tenho usado quase que diariamente nas últimas semanas e tenho gostado muito do resultado. Os videos tocam perfeitamente, sem "engasgar" em nenhum momento, mesmo arquivos de mais de 1GB. Poder curtir minhas mídias sem precisar ligar o computador na TV tem sido uma coisa tranquila e divertida, ainda mais com o controle da TV comandando tudo. Como o Raspberry Pi é pequeno eu escondi ele atrás do home theater e dá quase para esquecer que ele existe, além de consumir muito pouca energia.  Para ficar perfeito só falta a Netflix lançar o suporte a Linux para eu poder assistir os filmes direto dele, sem precisar ligar o computador ou o PS3. 

Se você também está usando o Raspberry Pi como media center ou com outros fins compartilhe nos comentários suas aventuras com esse brinquedinho nerd. 