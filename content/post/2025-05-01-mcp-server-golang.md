---
title: Criando um MCP Server usando Go
date: 2025-05-01T11:00:00-03:00
draft: false
tags:
  - go
---

Em Novembro de 2024 a Anthropic publicou um post em seu [blog](https://www.anthropic.com/news/model-context-protocol) anunciando o que talvez seja sua maior contribuição para o ecossistema de AI até o momento: o [**Model Context Protocol**](https://modelcontextprotocol.io/introduction)

Segundo a definição oficial no site:

> O MCP é um protocolo aberto que padroniza como os aplicativos fornecem contexto ao LLMS. Pense no MCP como uma porta USB-C para aplicativos de IA. Assim como o USB-C fornece uma maneira padronizada de conectar seus dispositivos a vários periféricos e acessórios, o MCP fornece uma maneira padronizada de conectar modelos de IA a diferentes fontes e ferramentas de dados.

Rapidamente outros players começaram a anunciar o suporte a este novo protocolo:

[![gemini_mcp](/images/posts/gemini_mcp.jpeg)](/images/posts/gemini_mcp.jpeg)

Não vou entrar em detalhes sobre toda a arquitetura definida no MCP para não tornar este post muito extenso, mas vou deixar alguns links no final para quem quiser se aprofundar mais. 

Mesmo assim, alguns componentes básicos são necessários para o entendimento:
- **MCP Hosts**: Programas como Claude Desktop, IDEs ou ferramentas de AI que desejam acessar dados através do MCP
- **MCP Clients**: Clientes de protocolo que mantêm conexões 1: 1 com servidores
- **MCP Servers**: Programas leves que expõem recursos específicos através do MCP

Neste exemplo eu vou criar um *Server*, uma aplicação Go, e vou usar alguns *Hosts* para testar o acesso a ele.

Além das definições sobre o protocolo, no site oficial do projeto estão disponíveis SDKs para algumas linguagens como Python, TypeScript, Java, Kotlin e C#. Apesar de existir uma [discussão](https://github.com/orgs/modelcontextprotocol/discussions/224) sobre o suporte a Go no repositório oficial, a comunidade já começou a criar algumas implementações do protocolo. Para a prova de conceito apresentada neste post eu usei um destes projetos, o [mcp-golang.](https://mcpgolang.com/introduction)

O primeiro passo foi a criação de um novo projeto Go:

```bash
mkdir mcp-server
go mod init github.com/eminetto/mcp-server
```

O código do `main.go` ficou desta forma:

```go
package main

import (
	"encoding/json"
	"errors"
	"fmt"
	"io"
	"net/http"
	"os"
	"strings"
	"time"

	mcp_golang "github.com/metoro-io/mcp-golang"
	"github.com/metoro-io/mcp-golang/transport/stdio"
	"github.com/ryanuber/go-filecache"
)

const cacheTime = 500

type MyFunctionsArguments struct {
	ZipCode string `json:"zip_code" jsonschema:"required,description=The zip code to be searched"`
}

// Cep is the brazilian postal code and address information
type Cep struct {
	Cep         string `json:"cep"`
	Logradouro  string `json:"logradouro"`
	Complemento string `json:"complemento"`
	Bairro      string `json:"bairro"`
	Localidade  string `json:"localidade"`
	Uf          string `json:"uf"`
	Unidade     string `json:"unidade"`
	Ibge        string `json:"ibge"`
	Gia         string `json:"gia"`
}

func main() {
	done := make(chan struct{})

	server := mcp_golang.NewServer(stdio.NewStdioServerTransport())
	err := server.RegisterTool("zipcode", "Find an address by his zip code", func(arguments MyFunctionsArguments) (*mcp_golang.ToolResponse, error) {
		address, err := getCep(arguments.ZipCode)
		if err != nil {
			return nil, err
		}

		return mcp_golang.NewToolResponse(mcp_golang.NewTextContent(fmt.Sprintf("Your address is %s!", address))), nil
	})
	if err != nil {
		panic(err)
	}
	err = server.Serve()
	if err != nil {
		panic(err)
	}

	<-done
}

func getCep(id string) (string, error) {
	cached := getFromCache(id)
	if cached != "" {
		return cached, nil
	}
	req, err := http.Get(fmt.Sprintf("http://viacep.com.br/ws/%s/json/", id))
	if err != nil {
		return "", err
	}

	var c Cep
	err = json.NewDecoder(req.Body).Decode(&c)
	if err != nil {
		return "", err
	}
	res, err := json.Marshal(c)
	if err != nil {
		return "", err
	}

	return saveOnCache(id, string(res)), nil
}

func getFromCache(id string) string {
	updater := func(path string) error {
		return errors.New("expired")
	}

	fc := filecache.New(getCacheFilename(id), cacheTime*time.Second, updater)

	fh, err := fc.Get()
	if err != nil {
		return ""
	}

	content, err := io.ReadAll(fh)
	if err != nil {
		return ""
	}

	return string(content)
}

func saveOnCache(id string, content string) string {
	updater := func(path string) error {
		f, err := os.Create(path)
		if err != nil {
			return err
		}
		defer f.Close()
		_, err = f.Write([]byte(content))
		return err
	}

	fc := filecache.New(getCacheFilename(id), cacheTime*time.Second, updater)

	_, err := fc.Get()
	if err != nil {
		return ""
	}

	return content
}

func getCacheFilename(id string) string {
	return os.TempDir() + "/cep" + strings.Replace(id, "-", "", -1)
}

```

Vou destacar a seguir os trechos mais importantes.

Definição dos parâmetros da função que vai estar disponível:

```go
type MyFunctionsArguments struct {
	ZipCode string `json:"zip_code" jsonschema:"required,description=The zip code to be searched"`
}
```


Definição da ferramenta que vai estar disponível para o client e host:

```go
server := mcp_golang.NewServer(stdio.NewStdioServerTransport())
	err := server.RegisterTool("zipcode", "Find an address by his zip code", func(arguments MyFunctionsArguments) (*mcp_golang.ToolResponse, error) {
		address, err := getCep(arguments.ZipCode)
		if err != nil {
			return nil, err
		}

		return mcp_golang.NewToolResponse(mcp_golang.NewTextContent(fmt.Sprintf("Your address is %s!", address))), nil
	})
	if err != nil {
		panic(err)
	}
	err = server.Serve()
```

O protocolo define que um Server pode disponibilizar três “capabilities”:

- **Resources**: Dados semelhantes a arquivos que podem ser lidos por clientes (como respostas de API ou conteúdo de arquivo)
- **Tools**: Funções que podem ser chamadas pelo LLM (com aprovação do usuário)
- **Prompts**: Modelos pré-escritos que ajudam os usuários a realizar tarefas específicas

Como podemos ver no código acima, nesta prova de conceito estou disponibilizando apenas uma *tool*. Os outros itens eu deixei de fora para simplificar o exemplo. 

O restante do código é responsável pela lógica da ferramenta, que neste caso é acessar uma API externa e retornar dados de acordo com o que foi pesquisado. Outros exemplos poderiam ser o acesso um banco de dados, um recurso de uma nuvem privada, etc. No site do projeto constam alguns outros [exemplos](https://modelcontextprotocol.io/examples).

O próximo passo é gerar um binário, usando o `go build`.

Agora que temos nosso *Server* gerado precisamos configurar algum *Host* para fazer uso da lógica que estamos disponibilizando.

Como a Anthropic foi a criadora do protocolo nada mais justo do que a sua ferramenta, o [Claude](https://claude.ai) seja o melhor exemplo para começarmos. Com o aplicativo instalado no macOS basta acessarmos a área de configuração, clicar em “Developer”, e em “Edit Config”. Somos levados à localização do arquivo `claude_desktop_config.json` que editei e salvei com o seguinte conteúdo:

```json
{
  "mcpServers": {
    "golang-mcp-server": {
      "command": "/Users/eminetto/Developer/mcp-server/mcp-server",
      "args": [],
      "env": {}
    }
  }
}

```

Existem mais configurações possíveis, mas estas foram suficientes para este cenário. 

Podemos agora fazer uma pergunta ao Claude, como: `Qual é o endereço completo do cep 88034102?`. Ele vai avisar que para responder isso é necessário acessar uma ferramenta externa:

[![claude_mcp_1.png](/images/posts/claude_mcp_1.png)](/images/posts/claude_mcp_1.png)

Após a permissão ser dada a resposta é mostrada com sucesso:

[![claude_mcp_2.png](/images/posts/claude_mcp_2.png)](/images/posts/claude_mcp_2.png)

A mesma pergunta pode ser feita em inglês e o resultado é o esperado:

[![claude_mcp_3.png](/images/posts/claude_mcp_3.png)](/images/posts/claude_mcp_3.png)

Como o MCP se propõe, podemos usar o mesmo *Server* com outros *Hosts*. Para testar isso usei o [Cursor](https://www.cursor.com), famosa IDE baseada em IA. Para configurá-la também é bem simples, bastando clicar no ícone de configurações (a universal engrenagem). Na tela de configurações existe uma opção “MCP” e dentro dela a “Add new global MCP Server”. A IDE vai abrir um arquivo `mcp.json` onde vamos preencher com o mesmo conteúdo que usamos para configurar o Claude. Podemos agora usar o chat da IDE para fazer a mesma pergunta que fizemos anteriormente e temos o mesmo resultado:

[![cursor_mcp.png](/images/posts/cursor_mcp.png)](/images/posts/cursor_mcp.png)

O MCP abre caminho para um alcance ainda maior das ferramentas de IA existentes, principalemente com mais empresas adotando o padrão. Confesso que demorei um pouco para me empolgar com as possibilidades das IAs/GenAI, para poder separar o hype da realidade, mas finalmente essa empolgação me atingiu e vejo grandes possibilidades para aplicações dentro das empresas.

Quando o Gemini lançar o suporte ao MCP (talvez no Google I/O deste ano? Vou estar lá, espero trazer novidades) eu pretendo fazer outro post sobre o assunto. O mesmo no momento do lançamento da SDK oficial de Go. 

E qual sua opinião sobre isso, nobre leitor(a)? Acredita que é mais uma onda de hype ou esta padronização realmente abre caminho para novas aplicações?

### Links

- [MCP and the future of AI tools](https://leaddev.com/technical-direction/mcp-and-the-future-of-ai-tools)
- [Extend the Amazon Q Developer CLI with Model Context Protocol (MCP) for Richer Context](https://aws.amazon.com/pt/blogs/devops/extend-the-amazon-q-developer-cli-with-mcp/)
- [Use MCP servers in VS Code (Preview)](https://code.visualstudio.com/docs/copilot/chat/mcp-servers)
