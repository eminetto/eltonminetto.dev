---
title: "Go Cloud Development Kit"
date: 2023-01-10T09:00:19-03:00
draft: false
tags:
  - go
---

Neste post vou falar sobre um projeto bem interessante que é mantido pelo time que desenvolve a linguagem Go. Trata-se do [Go Cloud Development Kit](https://gocloud.dev/), também conhecido como **‌Go CDK**.

O Go CDK fornece uma série de abstrações para um bom número de features bastante usadas em aplicações que rodam na nuvem, como banco de dados, armazenamento, mensageria, segredos, etc. O objetivo principal do projeto em criar estas abstrações é tornar o código independente de fornecedor de nuvem. Ao invés de tornar seu código dependente de uma solução, digamos o _AWS S3_, ao usar o Go CDK seria facilmente possível alterar para outro fornecedor, como o _Google Cloud Storage_.

Mas você pode estar se perguntando algo como:

> Ok, legal. Mas na prática dificilmente eu vou mudar de fornecedor. Porque vale a pena usar algo assim?

Além desta opção, eu consigo ver outras vantagens em se usar o Go CDK:

- **Escrita de testes.** Usando as abstrações é bem simples podermos usar um armazenamento [em memória](https://gocloud.dev/howto/blob/#local) nos testes, enquanto que no ambiente de produção usamos o provedor de nuvem;
- **Diferentes ambientes.** Podemos usar um fornecedor mais barato em um ambiente de testes/homologação e outro mais robusto e caro no ambiente de produção
- **Evolução.** É possível que a sua aplicação comece com uma solução mais simples, digamos o [SQS](https://gocloud.dev/howto/pubsub/publish/#sqs) para _pub/sub_, e conforme a carga e a complexidade aumentar pode-se mudar a decisão e começar a usar o [Kafka](https://gocloud.dev/howto/pubsub/publish/#kafka).

Para exemplificar, vejamos o código a seguir:

```go
package main

import (
	"context"
	"fmt"

	"gocloud.dev/blob"
	"gocloud.dev/blob/memblob"
)

func main() {
	ctx := context.Background()
	// Create an in-memory bucket.
	bucket := memblob.OpenBucket(nil)
	defer bucket.Close()

	key := "my-key"
	text := []byte("hello world")

	// Now we can use bucket to read or write files to the bucket.
	err := write(ctx, bucket, key, text)
	if err != nil {
		panic(err)
	}
	data, err := read(ctx, bucket, key)
	if err != nil {
		panic(err)
	}
	fmt.Println(string(data))
}

func write(ctx context.Context, bucket *blob.Bucket, key string, text []byte) error {
	err := bucket.WriteAll(ctx, key, text, nil)
	return err
}

func read(ctx context.Context, bucket *blob.Bucket, key string) ([]byte, error) {
	return bucket.ReadAll(ctx, key)
}

```

No código estamos escrevendo e lendo de um documento que foi armazenado em um `bucket` em memória. Para mudarmos a decisão e usarmos o `S3` é necessário apenas mudar o trecho abaixo na função main:

```go
sess, err := session.NewSession(&aws.Config{
	Region: aws.String("us-west-1"),
})

sess, err = session.NewSessionWithOptions(session.Options{
	Profile: "profile_name_configured_in_your_machine",
	Config: aws.Config{
		Region: aws.String("us-east-1"),
	},
})
if err != nil {
	panic(err)
}
bucket, err := s3blob.OpenBucket(ctx, sess, "post-go-cdk", nil)
if err != nil {
	panic(err)
}
defer bucket.Close()
```

Estamos apenas configurando a conexão com o `S3` e criando um `bucket` neste fornecedor. O restante do código não muda, continuamos usando as funções `read` e `write`.

Atualmente o projeto possui abstrações para:

- [Blob](https://gocloud.dev/howto/blob/), ou seja, armazenamento de arquivos. Possui suporte para _Google Cloud Storage_, _S3_, _Azure Blob Storage_ e armazenamento local.
- [Docstore](https://gocloud.dev/howto/docstore/), ou seja, bancos de dados de documentos, com suporte para _Google Cloud Firestore_, _Amazon DynamoDB_, _Azure Cosmos DB_, _MongoDB_ e armazenamento em memória.
- [MySQL/PostgreSQL](https://gocloud.dev/howto/sql/), com suporte a bancos locais, _GCP Cloud SQL_, _AWS RDS_ e _Azure Database_.
- [Pub/Sub](https://gocloud.dev/howto/pubsub/). Talvez a mais completa, com suporte a _Google Cloud Pub/Sub_, _Amazon Simple Notification Service (SNS)_, _Amazon Simple Queue Service (SQS)_, _Azure Service Bus_, _RabbitMQ_, _NATS_, _Kafka_ e armazenamento em memória.
- Entre outros.

E além de código o site oficial tem uma área com alguns [conceitos importantes](https://gocloud.dev/concepts/).

Apesar de ser um projeto ainda em estágios iniciais (o último release no momento da escrita deste post é o 0.28), é um projeto que está em evolução e bem ativo, além de ser mantido pelo time da própria linguagem. Por isso acredito que vale muito o investimento e uso para abstrair as complexidades que comentei neste post.
