---
title: "Improving the terminal"
date: 2023-05-16T08:30:43-03:00
draft: false
---

The terminal is the tool I use the most in my day-to-day work. Over the years, I've created a series of shortcuts, scripts, and habits that make me more productive in performing many of my tasks. In this post, I will tell you some of the things I've set up and been using to inspire someone else to take the time to do the same.


## The shell

The shell is the interface we interact with when we open a terminal session. The default shell for most Linux distributions and macOS was the good old `bash` for decades, but some competitors have gained prominence in recent years. The most relevant is [zsh](https://www.zsh.org/), now the default for macOS and many Linux distros. I used zsh for a while, but discovered [fish](https://fishshell.com/) in a tweet by [Carlos Becker](https://twitter.com/caarlos0), tried it, and didn't come back back. `fish` is lighter and faster than zsh (empirical fact, it's my impression as I haven't done or read benchmarks) and comes with lots of [features](https://fishshell.com/docs/current/tutorial.html#why-fish) built-in utilities like faster auto-complete, wildcards for browsing directories, between many other.

Hint: since macOS defaults to `zsh,` I ran the following command to install `fish.`

```bash
brew install fish
```

And the command to set it as my default shell:

```bash
chsh -s /opt/homebrew/bin/fish
```

## Saving the settings

On Unix derivatives (Linux and macOS, for example), the primary way to maintain application configuration is by using `dotfiles,` files that start with a dot in the name. The most exciting thing about this is that it's easy to make changes and backups of your settings. So I keep these files in a cloud service, like Dropbox, and configure my machine to use these files using [symbolic links](https://en.wikipedia.org/wiki/Symbolic_link).

These are some of my machine's configuration files:

```bash
❯ ls -lha ~/ | grep Dropbox
lrwxr-xr-x    1 eminetto  staff    36B 10 Mai  2022 .aws -> /Users/eminetto/Dropbox/dotfiles/aws
lrwxr-xr-x    1 eminetto  staff    43B 10 Mai  2022 .gitconfig -> /Users/eminetto/Dropbox/dotfiles/.gitconfig
lrwxr-xr-x    1 eminetto  staff    30B 10 Mai  2022 .gnupg -> /Users/eminetto/Dropbox/gnupg/
lrwxr-xr-x    1 eminetto  staff    28B 10 Mai  2022 .ssh -> /Users/eminetto/Dropbox/ssh/
```

As I use `fish,` I also did the same with its configuration files:

```bash
❯ ls -lha ~/.config | grep Dropbox
lrwxr-xr-x   1 eminetto  staff    37B 10 Mai  2022 fish -> /Users/eminetto/Dropbox/dotfiles/fish
```

To facilitate the creation of these files, I created a script that I use whenever I need to change computers:

```bash
cd ~
ln -s /Users/eminetto/Dropbox/dotfiles/aws .aws
ln -s /Users/eminetto/Dropbox/dotfiles/.gitconfig .gitconfig
ln -s /Users/eminetto/Dropbox/gnupg/ .gnupg
ln -s /Users/eminetto/Dropbox/ssh/ .ssh
ln -s /Users/eminetto/Dropbox/dotfiles/sshw .sshw
ln -s /Users/eminetto/Dropbox/dotfiles/fish .config/fish
```

This way, I quickly have the settings applied to my apps.

## Aliases

Another powerful feature in the terminal is creating aliases. As the name suggests, they are shortcuts to commands that speed up your productivity. These are some I am currently using:

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

To save an `alias` in `fish,` just run:

```bash
alias -s icloud="cd xxx"
```

And this will save the configuration in the `~/.config/fish` directory. Each shell has a command for this, and I recommend researching how to do this in yours.

Another place you can add shortcuts is in the `~/.gitconfig` file. These are some that I created:

```bash
[alias]
  cleanup = "!git fetch --all --prune; git branch --merged origin/master | grep -v \"\\*\" | grep -v \"\\  master\" | xargs -n 1 git branch -d"
  s = status
  d = diff
  co = checkout
  br = branch
  c = commit
```

This way, I can combine the fish alias with git. Instead of typing:

```bash
git commit -m "message"
```

I can run:

```bash
g c -m "message"
```

And I save a few seconds with each command :)

Another git-related tip I'm using is setting `1Password` to sign my commits. In this [documentation](https://developer.1password.com/docs/ssh/), you can see how to do this.

## Customizing the prompt

As I commented at the beginning of this post, the shell is the interface with which we interact with the operating system. And interfaces should be user-friendly and beautiful. One way to achieve this is by using a tool that allows customizations, and the tip I want to leave here is [Starship](https://starship.rs/). It is an application made in Rust, and we can use it in any shell to allow customizations of the prompt. The prompt is the input where we type the commands to be executed by the operational system. Installation and configuration are straightforward, as seen on the project's page. Once installed, configure the `~/.config/starship.toml` file. For example, the configuration:

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

It generates the following visual in my terminal:

[![starship](/images/posts/starship.jpg)](/images/posts/starship.jpg)

So, in the picture:

- `rancher-desktop` is the name of the Kubernetes cluster the user is connected to
- `votes` is the name of the Kubernetes `namespace.`
- `api-o11y` is the name of the directory the user is in
- `add-metrics` is the name of the git branch
- `[!]` indicates that there have been changes in the current `branch`, the equivalent of a `git status
- `5s` is the time it took to execute the last command
- `🟢 SUCCESS` indicates the status of the last command the user executed.

In Starship's [documentation](https://starship.rs/config/), you can see all possible configurations.

My goal with this post is to help and inspire you to customize and optimize your terminal. I guarantee it will be fun and improve your performance daily. Enjoy and share your tips, too; I would love to add new tricks to my workflow :)