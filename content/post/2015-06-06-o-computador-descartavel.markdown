---
categories: null
comments: true
date: 2015-06-06T00:00:00Z
title: O computador descartável
url: /2015/06/06/o-computador-descartavel/
---

Computadores falham, quebram, param de funcionar e isso é um fato que precisamos aceitar. Temos pouco controle sobre isso então precisamos pensar em como diminuir o impacto que irá nos causar caso nosso computador pessoal pare de funcionar. Felizmente hoje temos tecnologia disponível para podermos diminuir esta dependência e neste post vou citar as que eu uso.

A mais óbvia é usar a nuvem. Todos os meus arquivos pessoais estão em serviços como Copy, Dropbox e Amazon S3. O mesmo vale para os arquivos da Coderockr que estão no Dropbox e Google Drive. Para acessá-los eu uso a ferramenta [AirFile](https://itunes.apple.com/us/app/airfile-cloud-manager-for/id747595327?mt=12) que é um cliente para serviços em nuvem que me permite economizar o caro espaço do meu disco SSD pois não faço a sincronização dos arquivos locais com todos os serviços. 

O único serviço que mantenho a sincronização local é o Dropbox. Faço isso porque uso ele para armazenar o texto dos meus livros, o que o [Leanpub](https://leanpub.com/u/eminetto) exige, e para armazenar meus "dotfiles". Olhando o diretório home do MacOSX tenho o seguinte:

	lrwxr-xr-x    1 eminetto  staff    30B Apr 24 09:34 .gnupg -> /Users/eminetto/Dropbox/gnupg/
	lrwxr-xr-x    1 eminetto  staff    28B Feb 23 20:22 .ssh -> /Users/eminetto/Dropbox/ssh/

Os diretórios .gnupg e .ssh, que armazenam as minhas chaves de criptografia, são links para diretórios do Dropbox. E o meu arquivo .bash_profile contém:

	. ~/Dropbox/dotfiles/exports
	. ~/Dropbox/dotfiles/alias
	. ~/Dropbox/dotfiles/git-completion.bash
	. ~/Dropbox/dotfiles/git-prompt.sh

Ou seja, ele importa o conteúdo de outros scripts que estão no Dropbox. Assim eu mantenho o mesmo ambiente em qualquer máquina que eu sincronizar com o Dropbox.

Outro ponto importante é o meu ambiente de desenvolvimento. Eu uso Github e Bitbucket para armazenar todos os projetos, tanto os pessoais quanto os da empresa. Este site por exemplo é um repositório no Bitbucket. E o ambiente de execução dos projetos é formado por máquinas virtuais gerenciadas via Vagrant. Recentemente venho testando o Docker com o [Kitematic](https://kitematic.com/) para gerenciar alguns containers, mas ainda estou usando o Vagrant na maioria dos projetos, com scripts shell ou Puppet para automatizar a instalação de pacotes. Ou mesmo o MAMP se eu preciso de algo muito simples e de instalação rápida. 

Além disso eu uso coisas mais simples como o [Letterspace](https://programmerbird.com/letterspace/) para manter minhas anotações na nuvem compartilhada com o iOS, Gmail para gerenciar todas as minhas contas de e-mail, iCloud para salvar todos os meus documentos como apresentações e textos, etc. 

Eu tento sempre manter este conceito na minha cabeça: 

> Este Macbook Air pode quebrar a qualquer momento. Ou pode ser roubado, ou raptado por aliens!

Caso um evento assim ocorra eu preciso apenas de poucas horas para instalar um novo SO e sincronizar algumas contas na nuvem para ter um ambiente totalmente funcional e produtivo. Pode ser um pouco paranóico mas me deixa bem mais tranquilo ;)