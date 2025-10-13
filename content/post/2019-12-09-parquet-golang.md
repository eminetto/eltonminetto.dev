---
title: "Processando arquivos parquet em Go"
subtitle: ""
date: "2019-12-09T10:54:24+02:00"
bigimg: ""
tags:
  - go
---

Neste post vou falar sobre um formato relativamente novo de arquivo de dados, e como usá-lo em Go.

O formato chama-se [Parquet](http://parquet.apache.org), e atualmente é um projeto apoiado pela Apache Foundation. Trata-se de um formato binário de arquivos, com a finalidade de armazenar e facilitar o processamento de dados na [forma de colunas](http://en.wikipedia.org/wiki/Column-oriented_DBMS). Ele suporta diferentes tipos de compressão e é bastante usado no ambiente de data science e big data, com ferramentas como o Hadoop.

Na [Codenation](https://codenation.dev) estamos usando este formato para armazenar dados estatísticos em buckets do S3 facilitando o processamento paralelo, usando Lambda Functions, sem sobrecarregar nossos servidores de bancos de dados.

Neste post vou mostrar como gerar e processar arquivos neste formato usando a linguagem Go.

O primeiro passo é criar uma `struct` que vai representar os dados que vamos processar neste exemplo:

```go
type user struct {
  ID        string    `parquet:"name=id, type=UTF8, encoding=PLAIN_DICTIONARY"`
  FirstName string    `parquet:"name=firstname, type=UTF8, encoding=PLAIN_DICTIONARY"`
  LastName  string    `parquet:"name=lastname, type=UTF8, encoding=PLAIN_DICTIONARY"`
  Email     string    `parquet:"name=email, type=UTF8, encoding=PLAIN_DICTIONARY"`
  Phone     string    `parquet:"name=phone, type=UTF8, encoding=PLAIN_DICTIONARY"`
  Blog      string    `parquet:"name=blog, type=UTF8, encoding=PLAIN_DICTIONARY"`
  Username  string    `parquet:"name=username, type=UTF8, encoding=PLAIN_DICTIONARY"`
  Score     float64   `parquet:"name=score, type=DOUBLE"`
  CreatedAt time.Time //wont be saved in the parquet file
}
```

O detalhe importante neste código são as `tags` que declaram como cada campo da `struct` vai ser tratada no momento da geração do arquivo `parquet`. Para fazer o processamento dos dados estou usando o pacote [github.com/xitongsys/parquet-go](https://github.com/xitongsys/parquet-go) e no repositório é possível ver mais exemplos das [tags disponíveis](https://github.com/xitongsys/parquet-go#type).

Vamos agora gerar o nosso primeiro arquivo no formato `parquet`:

```go
package main

import (
  "fmt"
  "log"
  "time"
  "github.com/bxcodec/faker/v3"
  "github.com/xitongsys/parquet-go-source/local"
  "github.com/xitongsys/parquet-go/parquet"
  "github.com/xitongsys/parquet-go/reader"
  "github.com/xitongsys/parquet-go/writer"
)

type user struct {
  ID        string    `parquet:"name=id, type=UTF8, encoding=PLAIN_DICTIONARY"`
  FirstName string    `parquet:"name=firstname, type=UTF8, encoding=PLAIN_DICTIONARY"`
  LastName  string    `parquet:"name=lastname, type=UTF8, encoding=PLAIN_DICTIONARY"`
  Email     string    `parquet:"name=email, type=UTF8, encoding=PLAIN_DICTIONARY"`
  Phone     string    `parquet:"name=phone, type=UTF8, encoding=PLAIN_DICTIONARY"`
  Blog      string    `parquet:"name=blog, type=UTF8, encoding=PLAIN_DICTIONARY"`
  Username  string    `parquet:"name=username, type=UTF8, encoding=PLAIN_DICTIONARY"`
  Score     float64   `parquet:"name=score, type=DOUBLE"`
  CreatedAt time.Time //wont be saved in the parquet file
}

const recordNumber = 10000

func main() {
  var data []*user
  //create fake data
  for i := 0; i < recordNumber; i++ {
    u := &user{
      ID:        faker.UUIDDigit(),
      FirstName: faker.FirstName(),
      LastName:  faker.LastName(),
      Email:     faker.Email(),
      Phone:     faker.Phonenumber(),
      Blog:      faker.URL(),
      Username:  faker.Username(),
      Score:     float64(i),
      CreatedAt: time.Now(),
    }
    data = append(data, u)
  }
  err := generateParquet(data)
  if err != nil {
    log.Fatal(err)
  }

}

func generateParquet(data []*user) error {
  log.Println("generating parquet file")
  fw, err := local.NewLocalFileWriter("output.parquet")
  if err != nil {
    return err
  }
  //parameters: writer, type of struct, size
  pw, err := writer.NewParquetWriter(fw, new(user), int64(len(data)))
  if err != nil {
    return err
  }
  //compression type
  pw.CompressionType = parquet.CompressionCodec_GZIP
  defer fw.Close()
  for _, d := range data {
    if err = pw.Write(d); err != nil {
      return err
    }
  }
  if err = pw.WriteStop(); err != nil {
    return err
  }
  return nil
}
```

Com o arquivo gerado podemos fazer o processo inverso, lendo como no exemplo abaixo:

```go
func readParquet() ([]*user, error) {
  fr, err := local.NewLocalFileReader("output.parquet")
  if err != nil {
    return nil, err
  }
  pr, err := reader.NewParquetReader(fr, new(user), recordNumber)
  if err != nil {
    return nil, err
  }
  u := make([]*user, recordNumber)
  if err = pr.Read(&u); err != nil {
    return nil, err
  }
  pr.ReadStop()
  fr.Close()
  return u, nil
}
```

O exemplo acima é apenas didático, pois estou lendo o arquivo todo e colocando todos os 10000 registros em memória, o que pode ser um problema quando estivermos falando de gigabytes de dados. Na prática o ideal é usarmos as funções que o pacote fornece, para buscar apenas parte do arquivo:

```go
func readPartialParquet(pageSize, page int) ([]*user, error) {
  fr, err := local.NewLocalFileReader("output.parquet")
  if err != nil {
    return nil, err
  }
  pr, err := reader.NewParquetReader(fr, new(user), int64(pageSize))
  if err != nil {
    return nil, err
  }
  pr.SkipRows(int64(pageSize * page))
  u := make([]*user, pageSize)
  if err = pr.Read(&u); err != nil {
    return nil, err
  }
  pr.ReadStop()
  fr.Close()
  return u, nil
}
```

Outra vantagem deste formato, como a definição deixa bem claro, é o fato dela ser focada no tratamento das colunas do arquivo. Desta forma, podemos pegar apenas a coluna `Score` e calcular sua média:

```go
func calcScoreAVG() (float64, error) {
  fr, err := local.NewLocalFileReader("output.parquet")
  if err != nil {
    return 0.0, err
  }
  pr, err := reader.NewParquetColumnReader(fr, recordNumber)
  if err != nil {
    return 0.0, err
  }
  num := int(pr.GetNumRows())

  data, _, _, err := pr.ReadColumnByPath("parquet_go_root.score", num)
  if err != nil {
    return 0.0, err
  }
  var result float64
  for _, i := range data {
    result += i.(float64)
  }
  return (result / float64(num)), nil
}
```

O objetivo deste post era apresentar este formato relativamente novo e que pode ser muito útil para a transferência de dados, substituindo arquivos `csv` ou `json` em projetos de diferentes escalas. É possível aprofundar-se na documentação do formato e também do pacote para encontrar exemplos mais complexos e detalhados, mas espero ter trazido uma novidade útil para alguns projetos em Go.

O código completo do exemplo apresentado neste post pode ser encontrado [neste repositório](https://github.com/eminetto/parquet-golang).
