---
title: "Go Cloud Development Kit"
date: 2023-01-10T09:00:19-03:00
draft: false
tags:
  - go
---

In this post, I will talk about an exciting project maintained by the team that develops the Go language: the [Go Cloud Development Kit](https://gocloud.dev/), also known as the **Go CDK**.

The Go CDK provides a series of abstractions for many features often used in applications that run in the cloud, such as databases, storage, messaging, secrets, etc. The project's primary goal in creating these abstractions is to make the code cloud-vendor independent. Rather than making your code dependent on one solution, says _AWS S3_, using the Go CDK, you could easily switch to another vendor like _Google Cloud Storage_.

But you might be wondering something like:

> OK, nice. But in practice, I will hardly change suppliers. So why is it worth using something like this?

I can see some advantages of using the Go CDK:

- **Test writing**. Using the abstractions is effortless to use [in-memory](https://gocloud.dev/howto/blob/#local) storage in the tests, while in the production environment, we can use the cloud provider.
- **Different environments**. We can use a cheaper supplier in a test/homologation environment and one more robust and expensive in the production environment.
- **Evolution**. Your application may start with a more straightforward solution, say [SQS](https://gocloud.dev/howto/pubsub/publish/#sqs) for _pub/sub_, and as the load and complexity increase, you can change your decision and start using [Kafka](https://gocloud.dev/howto/pubsub/publish/#kafka).

For example, let's look at the following code:

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

In the code, we are writing and reading from a document stored in a memory `bucket`. Therefore, to change the decision and use `S3`, it is only necessary to change the snippet below in the `main` function:

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

We are setting up the `S3` connection and creating a `bucket` on this provider. The rest of the code doesn't change. We continue using the `read` and `write` functions.

Currently, the project has abstractions for:

- [Blob](https://gocloud.dev/howto/blob/), i.e., file storage. It supports _Google Cloud Storage_, _S3_, _Azure Blob Storage_, and local storage.
- [Docstore](https://gocloud.dev/howto/docstore/), i.e., document databases, with support for _Google Cloud Firestore_, _Amazon DynamoDB_, _Azure Cosmos DB_, _MongoDB_, and in-memory storage.
- [MySQL/PostgreSQL](https://gocloud.dev/howto/sql/), supporting local databases, _GCP Cloud SQL_, _AWS RDS_, and _Azure Database_.
- [Pub/Sub](https://gocloud.dev/howto/pubsub/). Perhaps the most complete, with support for _Google Cloud Pub/Sub_, _Amazon Simple Notification Service (SNS)_, _Amazon Simple Queue Service (SQS)_, _Azure Service Bus_, _RabbitMQ_, _NATS_, _Kafka_ and memory storage.
- And the list goes on.

And in addition to code, the official website has an area with some important [concepts](https://gocloud.dev/concepts/).

Despite being a project still in its early stages (the last release at the time of writing this post is 0.28), it is a project that is evolving and very active, in addition to being maintained by the language team itself. That's why it's worth the investment and use to abstract the complexities I mentioned in this post.
