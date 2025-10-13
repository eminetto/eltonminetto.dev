---
title: "Melhorando o terminal"
date: 2023-05-16T08:30:43-03:00
draft: false
---

O terminal é provavelmente a ferramenta que eu mais uso no meu dia a dia. Com o passar dos anos eu criei uma série de atalhos, scripts e hábitos que me fazem ser mais produtivo na execução de várias das minhas tarefas. Neste post vou contar algumas das coisas que eu configurei e venho usando, com o objetivo de talvez inspirar alguém a dedicar um tempo para fazer o mesmo.

## O shell

O `shell` é a interface com a qual interagimos quando abrimos uma sessão de terminal. Por décadas o shell padrão para a grande maioria das distribuições Linux e o macOS foi o bom e velho `bash` mas nos últimos anos alguns competidores ganharam destaque. Talvez o mais relevante seja o [zsh](https://www.zsh.org/), que hoje é o padrão para o macOS e várias distros Linux. Eu usei o `zsh` por algum tempo, mas descobri o [fish](https://fishshell.com/) em um tweet do [Carlos Becker](https://twitter.com/caarlos0), testei e não voltei atrás. O `fish` é mais leve e rápido do que o `zsh`(fato impírico, é minha impressão pois não fiz ou li benchmarks) e vem com várias [funcionalidades](https://fishshell.com/docs/current/tutorial.html#why-fish) úteis embutidas como um auto-complete mais rápido, `wildcards` para navegar nos diretórios, etc. 

Dica: como o padrão do macOS é o `zsh` eu executei o comando a seguir para instalar o `fish`:

```bash
brew install fish
```

E o comando para definí-lo como meu shell padrão:

```bash
chsh -s /opt/homebrew/bin/fish
```

## Salvando as configurações

Nos derivados do Unix (Linux e macOS por exemplo) uma forma de manter a configuração de aplicações é usando-se os `dotfiles`, que são arquivos que começam com um `.` no nome. O mais interessante disso é que é fácil fazer alterações e backups das suas configurações. O que eu faço é manter estes arquivos em um serviço de núvem, como o Dropbox, e configurar minha máquina para usar estes arquivos, usando o recurso de [links simbólicos](https://pt.wikipedia.org/wiki/Liga%C3%A7%C3%A3o_simb%C3%B3lica). 

Estes são alguns dos arquivos de configuração da minha máquina:

```bash
❯ ls -lha ~/ | grep Dropbox
lrwxr-xr-x    1 eminetto  staff    36B 10 Mai  2022 .aws -> /Users/eminetto/Dropbox/dotfiles/aws
lrwxr-xr-x    1 eminetto  staff    43B 10 Mai  2022 .gitconfig -> /Users/eminetto/Dropbox/dotfiles/.gitconfig
lrwxr-xr-x    1 eminetto  staff    30B 10 Mai  2022 .gnupg -> /Users/eminetto/Dropbox/gnupg/
lrwxr-xr-x    1 eminetto  staff    28B 10 Mai  2022 .ssh -> /Users/eminetto/Dropbox/ssh/
```

Como eu uso o `fish` também fiz o mesmo com os arquivos de configuração dele:

```bash
❯ ls -lha ~/.config | grep Dropbox
lrwxr-xr-x   1 eminetto  staff    37B 10 Mai  2022 fish -> /Users/eminetto/Dropbox/dotfiles/fish
```

Para facilitar a criação destes arquivos eu criei um script que uso sempre que preciso trocar de computador:

```bash
cd ~
ln -s /Users/eminetto/Dropbox/dotfiles/aws .aws
ln -s /Users/eminetto/Dropbox/dotfiles/.gitconfig .gitconfig
ln -s /Users/eminetto/Dropbox/gnupg/ .gnupg
ln -s /Users/eminetto/Dropbox/ssh/ .ssh
ln -s /Users/eminetto/Dropbox/dotfiles/sshw .sshw
ln -s /Users/eminetto/Dropbox/dotfiles/fish .config/fish
```

Desta forma rapidamente eu tenho as configurações aplicadas aos meus aplicativos principais.

## Atalhos

Outro recurso poderoso para se usar no terminal é criar atalhos, ou `alias`. Como o nome sugere, são atalhos para comandos e que aceleram a sua produtividade. Estes são alguns dos atalhos que tenho no momento em meu `fish`:

```bash
❯ alias
alias cot 'open /Applications/CotEditor.app/'
alias d docker
alias dc 'docker compose'
alias dcdown 'docker compose stop'
alias dcup 'docker-compose up -d'
alias g git
alias icloud cd\\\ /Users/eminetto/Library/Mobile\\\\\\\ Documents/com\\\~apple\\\~CloudDocs/
alias k kubectl
alias kb kubectl
alias ls 'ls -G'
alias open-ports netstat\\\ -anvp\\\ tcp\\\ \\\|\\\ awk\\\ \\\'NR\\\<3\\\ \\\|\\\|\\\ /LISTEN/\\\'
alias py3 python3
alias subl /Applications/Sublime\\\\\\\ Text.app/Contents/SharedSupport/bin/subl
```

Para salvar um `alias` no `fish` basta executar:

```bash
alias -s icloud="cd xxx"
```

E a configuração vai ser salva no diretório `~/.config/fish`. Cada `shell` tem um comando para isso, recomendo pesquisar como fazer isso no seu. 

Outro lugar onde é possível adicionar atalhos é no arquivo `~/.gitconfig`. Estes são alguns que eu criei:

```bash
[alias]
  cleanup = "!git fetch --all --prune; git branch --merged origin/master | grep -v \"\\*\" | grep -v \"\\  master\" | xargs -n 1 git branch -d"
  s = status
  d = diff
  co = checkout
  br = branch
  c = commit
```

Desta forma eu posso aliar o `alias` do `fish` com o do git e ao invés de digitar:

```bash
git commit -m "message"
```

Basta executar:

```bash
g c -m "message"
```

E eu ganho alguns segundos a cada comando :)

Uma dica relacionada ao git e que eu estou usando é configurar o `1Password` para assinar os meus commits. Nesta [documentação](https://developer.1password.com/docs/ssh/) é possível ver como fazer isso. 

## Customizando o prompt

Como eu comentei no começo deste post, o `shell` é a interface com a qual interagimos com o sistema operacional. E interfaces deveriam ser amigáveis e bonitas. Uma forma de se atingir isso é usando uma ferramenta que permita customizações e a dica que quero deixar aqui é o [Starship](https://starship.rs/). Trata-se de uma aplicação feita em Rust e que pode ser usada em qualquer `shell` para permitir customizações do `prompt`, que é o `input` onde digitamos os comandos. A instalação e configuração é muito simples, conforme consta na página inicial do projeto. Após instalado basta configurar o arquivo `~/.config/starship.toml`. Por exemplo, a configuração:

```toml
[aws]
disabled = true

[cmake]
disabled = true

[cmd_duration]
min_time = 500
format = "[$duration]($style) "

[conda]
disabled = true

[crystal]
disabled = true

[dart]
disabled = true

[docker_context]
disabled = true

[dotnet]
disabled = true

[elixir]
disabled = true

[elm]
disabled = true

[env_var]
disabled = true

[erlang]
disabled = true

[gcloud]
disabled = true

[golang]
disabled = true

[helm]
disabled = true

[java]
disabled = true

[jobs]
disabled = true

[julia]
disabled = true

[kotlin]
disabled = true

[kubernetes]
format = '[☸ $context \($namespace\)](dimmed green) '
disabled = false

[lua]
disabled = true

[memory_usage]
disabled = true
threshold = -1
symbol = ' '
style = 'bold dimmed green'

[nim]
disabled = true

[nix_shell]
disabled = true

[nodejs]
disabled = true

[ocaml]
disabled = true

[openstack]
disabled = true

[package]
disabled = true

[perl]
disabled = true

[php]
disabled = true

[purescript]
disabled = true

[python]
disabled = true

[ruby]
disabled = true

[rust]
disabled = true

[scala]
disabled = true

[shlvl]
disabled = true

[singularity]
disabled = true

[swift]
disabled = true

[status]
style = 'bg:blue'
symbol = '🔴 '
success_symbol = '🟢 SUCCESS'
format = '[\[$symbol$common_meaning$signal_name$maybe_int\]]($style) '
map_symbol = true
disabled = false

[terraform]
disabled = true

[vagrant]
disabled = true

[zig]
disabled = true

[username]
format = "[$user]($style)@"

```

Gera o seguinte visual no meu terminal:

[![starship](/images/posts/starship.jpg)](/images/posts/starship.jpg)

Sendo que, na imagem:

- `rancher-desktop` é o nome do cluster Kubernetes onde estou conectado
- `votes` é o nome do `namespace` do Kubernetes
- `api-o11y` é o nome do diretório onde estou
- `add-metrics` é o nome da branch do git
- `[!]` indica que houveram alterações na branch atual, o equivalente a um `git status`
- `5s` é o tempo que demorou a execução do último comando
- `🟢 SUCCESS` indica que o último comando que executei finalizou com sucesso. 

Na [documentação](https://starship.rs/config/) do Starship é possível ver todas as configurações possíveis. 

Espero que estas dicas ajudem e inspirem você a dedicar um tempo para customizar e otimizar o seu terminal. Garanto que vai ser divertido e melhorar sua performance no dia a dia. Aproveite e compartilhe as suas dicas também, eu adoraria adicionar novos truques ao meu workflow :)