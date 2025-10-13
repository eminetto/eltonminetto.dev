---
title: "Processing parquet files in Golang"
subtitle: ""
date: "2019-12-10T10:54:24+02:00"
bigimg: ""
tags:
  - go
---

In this post, I will talk about a relatively new data file format, and how to use it in Go.

The format is called [Parquet](http://parquet.apache.org) and is currently a project supported by the Apache Foundation. It is a binary file format to store and facilitate data processing a [columnar storage format](http://en.wikipedia.org/wiki/Column-oriented_DBMS). It supports different types of compression and is widely used in data science and big data environment, with tools like Hadoop.

At [Codenation](https://codenation.dev) we are using this format to store statistical data in S3 buckets. That way, we can do parallel processing using Lambda Functions without overloading our database servers.

In this post, I will show how to generate and process files in this format using the Go language.

The first step is to create a `struct` that will represent the data we will process in this example:

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

The important detail in this code is the `tags`, which state how each field of the `struct` will be handled when generating the `parquet` file. To process the data I am using the package [github.com/xitongsys/parquet-go](https://github.com/xitongsys/parquet-go) and in the repository you can see more examples of [available tags](https://github.com/xitongsys/parquet-go#type).

Let's now generate our first file in `parquet` format:

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

The next snippet shows how we read content in a parquet file:

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

The above example is just a didactic one. As I am reading the entire file and putting all 10,000 records into memory this can be a problem when talking about gigabytes of data. In real-life applications we will use functions that the package provides to fetch only part of the file:

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

As the definition makes clear, we are using a columnar storage format. So, we can take just the `Score` column and calculate its average:

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

The purpose of this post was to introduce this format which can be very useful for data transfer, replacing CSV or JSON files in projects of different scales.

You can dig deeper into the format and package documentation to find more complex and detailed examples, but I hope I have brought some useful advice for some Go projects.

The complete example code presented in this post can be found [in this repository](https://github.com/eminetto/parquet-golang).
