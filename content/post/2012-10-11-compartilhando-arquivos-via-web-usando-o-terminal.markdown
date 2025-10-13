---
categories:
- etc
comments: true
date: 2012-10-11T13:17:59Z
slug: compartilhando-arquivos-via-web-usando-o-terminal
title: Compartilhando arquivos via web usando o Terminal
url: /2012/10/11/compartilhando-arquivos-via-web-usando-o-terminal/
wordpress_id: 1158
---

Lendo a minha sempre útil (e divertida) timeline do Twitter me deparei com uma ferramenta bem interessante, chamada [geturl](https://github.com/uams/geturl). A dica veio do amigo [@filaruina](https://twitter.com/filaruina/status/256408605874601984)

O geturl é um script em Python que usa a API do serviço [Filepicker.io](https://www.filepicker.io) para enviar o arquivo selecionado para o cloud da Amazon e fornecer uma url pública para o acesso do mesmo.
Instalei no meu Mountain Lion e funcionou perfeitamente (pelo que consta na documentação deve funcionar facilmente no Linux).

Para instalar é só digitar no Terminal:

```
sudo curl https://raw.github.com/uams/geturl/master/geturl -o /usr/bin/geturl;
sudo chmod +x /usr/bin/geturl
```
Para enviar um arquivo basta:

```
geturl /path/do/arquivo
```

A primeira vez que você executar o comando acima ele vai solicitar o e-mail que você cadastrou no Filepicker, ou um e-mail para ser gerado o cadastro.
Além de gerar a URL ele já usa o comando pbcopy do Mac para jogar a url para a área de transferência, então basta um Cmd+V para colar em qualquer lugar (o mesmo deve funcionar no Linux).
A conta gratuíta do Filepicker.io permite o envio de 5000 arquivos/mês, apesar de eu não ter encontrado referências sobre o tamanho máximo dos arquivos e nem quanto tempo eles ficam disponíveis. Mas para o que eu preciso, compartilhar arquivos pequenos e de forma temporária, é mais do que o necessário.
