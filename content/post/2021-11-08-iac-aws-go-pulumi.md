---
title: "Infraestrutura como Código na AWS usando Go e Pulumi"
date: 2021-11-08T20:14:25-03:00
draft: false
tags:
  - go
---

Quando falamos de Infraestrutura como Código (Infrastructure as Code, ou IaC), a principal ferramenta que nos vem a mente é o [Terraform](https://terraform.io). A ferramenta criada pela HashiCorp tornou-se o padrão para a documentação e gerenciamento de infraestrutura, mas sua linguagem declarativa, a HCL (HashiCorp Configuration Language) tem algumas limitações. A principal delas é exatamente não ser uma linguagem de programação, e sim de configuração.

Para suprir essa necessidade, algumas alternativas vem surgindo, como:

- [AWS Cloud Development Kit](https://aws.amazon.com/pt/cdk/), solução da Amazon que permite usarmos TypeScript, Python e Java para programar a infraestrutura usando as soluções do provedor de cloud;
- [Pulumi](https://www.pulumi.com), que permite usarmos TypeScript, JavaScript, Python, Go e C# para programarmos infraestruturas usando as soluções da AWS, Microsoft Azure, Google Cloud e instalações de Kubernetes.

Neste post vou fazer uma introdução ao Pulumi, usando a linguagem Go para criar alguns exemplos de infraestrutura na AWS.

## Instalação

Para fazer uso do Pulumi precisamos primeiramente instalar seu aplicativo de linha de comando. Seguindo a [documentação](https://www.pulumi.com/docs/get-started/aws/begin/) eu instalei no meu macOS usando o comando:

    brew install pulumi

No site é possível ver as opções de instalação no Windows e Linux.

## Configurar acesso a conta AWS

Como vou usar neste exemplo a AWS, o próximo passo necessário é configurar as credenciais de acesso. Para isso peguei minha chave de acesso e segredo no painel da AWS e configurei as variáveis de ambiente necessárias:

    export AWS_ACCESS_KEY_ID=<YOUR_ACCESS_KEY_ID>
    export AWS_SECRET_ACCESS_KEY=<YOUR_SECRET_ACCESS_KEY>

## Criando o projeto

Com as dependências iniciais configuradas, agora podemos criar o projeto:

    mkdir post-pulumi
    cd post-pulumi
    pulumi new aws-go

Uma das etapas da criação exige a configuração de uma conta no site do Pulumi e para isso o navegador é aberto para que este passo seja finalizado. Eu fiz login com a minha conta no Github e após o cadastro finalizado retornei ao terminal e a criação do projeto continuou sem problemas.

O resultado da execução do comando pode ser visto [neste link](https://app.warp.dev/block/ngaPDDNEPbf5eTuMgDHsqW). É possível ver que no final do processo foram instaladas as dependências necessárias para a criação do projeto em Go.

### Arquivos criados

Analisando o conteúdo do diretório podemos ver que alguns arquivos de configuração e um `main.go` foram criados.

_Pulumi.yaml_

```yaml
name: post-pulumi
runtime: go
description: A minimal AWS Go Pulumi program
```

_Pulumi.dev.yaml_

```yaml
config:
  aws:region: us-east-1
```

_main.go_

```go
package main

import (
	"github.com/pulumi/pulumi-aws/sdk/v4/go/aws/s3"
	"github.com/pulumi/pulumi/sdk/v3/go/pulumi"
)

func main() {
	pulumi.Run(func(ctx *pulumi.Context) error {
		// Create an AWS resource (S3 Bucket)
		bucket, err := s3.NewBucket(ctx, "my-bucket", nil)
		if err != nil {
			return err
		}

		// Export the name of the bucket
		ctx.Export("bucketName", bucket.ID())
		return nil
	})
}
```

Ao executar

    pulumi up

foi criado o _bucket_ no S3, conforme o código indica.

E ao executar:

    pulumi destroy

a estrutura é removida, ou seja, o _bucket_ é destruído.

## Primeiro exemplo - criando uma página estática no S3

Vamos agora fazer alguns exemplos um pouco mais complexos.

O primeiro passo é criar uma página estática, que vamos fazer deploy:

    mkdir static

Dentro deste diretório criei o arquivo:

_static/index.html_

```html
<html>
  <body>
    <h1>Hello, Pulumi!</h1>
  </body>
</html>
```

Alterei o _main.go_ para refletir a nova estrutura:

```go
package main

import (
	"github.com/pulumi/pulumi-aws/sdk/v4/go/aws/s3"
	"github.com/pulumi/pulumi/sdk/v3/go/pulumi"
)

func main() {
	pulumi.Run(func(ctx *pulumi.Context) error {
		// Create an AWS resource (S3 Bucket)
		bucket, err := s3.NewBucket(ctx, "my-bucket", &s3.BucketArgs{
			Website: s3.BucketWebsiteArgs{
				IndexDocument: pulumi.String("index.html"),
			},
		})
		if err != nil {
			return err
		}

		// Export the name of the bucket
		ctx.Export("bucketName", bucket.ID())

		_, err = s3.NewBucketObject(ctx, "index.html", &s3.BucketObjectArgs{
			Acl:         pulumi.String("public-read"),
			ContentType: pulumi.String("text/html"),
			Bucket:      bucket.ID(),
			Source:      pulumi.NewFileAsset("static/index.html"),
		})
		if err != nil {
			return err
		}
		ctx.Export("bucketEndpoint", pulumi.Sprintf("http://%s", bucket.WebsiteEndpoint))
		return nil
	})
}

```

Para atualizar basta executar

    pulumi up

E confirmar a alteração.

O trecho de código:

```go
ctx.Export("bucketEndpoint", pulumi.Sprintf("http://%s", bucket.WebsiteEndpoint))
```

Faz com que seja gerado como saída endereço para acesso ao `index.html`, por exemplo:

```
Outputs:
  + bucketEndpoint: "http://my-bucket-357877e.s3-website-us-east-1.amazonaws.com"
```

Este é um exemplo bem simples mas que já demonstra o poder da ferramenta. Vamos tornar as coisas um pouco mais complexas e divertidas agora.

## Segundo exemplo - site dentro de um container

Vamos criar um _Dockerfile_ com um servidor web para hospedar nosso conteúdo estático:

_static/Dockerfile_

```Dockerfile
FROM golang

ADD . /go/src/foo

WORKDIR /go/src/foo
RUN go build -o /go/bin/main

ENTRYPOINT /go/bin/main

EXPOSE 80
```

Vamos agora criar o arquivo _static/main.go_, que vai ser nosso servidor Web:

```go
package main

import (
	"log"
	"net/http"
)

func main() {
	r := http.NewServeMux()
	fileServer := http.FileServer(http.Dir("./"))
	r.Handle("/", http.StripPrefix("/", fileServer))
	s := &http.Server{
		Addr:    ":80",
		Handler: r,
	}
	log.Fatal(s.ListenAndServe())
}
```

Vamos alterar o _main.go_ para incluir a infraestrutura de um cluster ECS e tudo mais necessário para executar nosso container:

```go
package main

import (
	"encoding/base64"
	"fmt"
	"strings"

	"github.com/pulumi/pulumi-aws/sdk/v4/go/aws/ec2"
	"github.com/pulumi/pulumi-aws/sdk/v4/go/aws/ecr"
	"github.com/pulumi/pulumi-aws/sdk/v4/go/aws/ecs"
	elb "github.com/pulumi/pulumi-aws/sdk/v4/go/aws/elasticloadbalancingv2"
	"github.com/pulumi/pulumi-aws/sdk/v4/go/aws/iam"
	"github.com/pulumi/pulumi-docker/sdk/v3/go/docker"
	"github.com/pulumi/pulumi/sdk/v3/go/pulumi"
)

func main() {
	pulumi.Run(func(ctx *pulumi.Context) error {
		// Read back the default VPC and public subnets, which we will use.
		t := true
		vpc, err := ec2.LookupVpc(ctx, &ec2.LookupVpcArgs{Default: &t})
		if err != nil {
			return err
		}
		subnet, err := ec2.GetSubnetIds(ctx, &ec2.GetSubnetIdsArgs{VpcId: vpc.Id})
		if err != nil {
			return err
		}

		// Create a SecurityGroup that permits HTTP ingress and unrestricted egress.
		webSg, err := ec2.NewSecurityGroup(ctx, "web-sg", &ec2.SecurityGroupArgs{
			VpcId: pulumi.String(vpc.Id),
			Egress: ec2.SecurityGroupEgressArray{
				ec2.SecurityGroupEgressArgs{
					Protocol:   pulumi.String("-1"),
					FromPort:   pulumi.Int(0),
					ToPort:     pulumi.Int(0),
					CidrBlocks: pulumi.StringArray{pulumi.String("0.0.0.0/0")},
				},
			},
			Ingress: ec2.SecurityGroupIngressArray{
				ec2.SecurityGroupIngressArgs{
					Protocol:   pulumi.String("tcp"),
					FromPort:   pulumi.Int(80),
					ToPort:     pulumi.Int(80),
					CidrBlocks: pulumi.StringArray{pulumi.String("0.0.0.0/0")},
				},
			},
		})
		if err != nil {
			return err
		}

		// Create an ECS cluster to run a container-based service.
		cluster, err := ecs.NewCluster(ctx, "app-cluster", nil)
		if err != nil {
			return err
		}

		// Create an IAM role that can be used by our service's task.
		taskExecRole, err := iam.NewRole(ctx, "task-exec-role", &iam.RoleArgs{
			AssumeRolePolicy: pulumi.String(`{
    "Version": "2008-10-17",
    "Statement": [{
        "Sid": "",
        "Effect": "Allow",
        "Principal": {
            "Service": "ecs-tasks.amazonaws.com"
        },
        "Action": "sts:AssumeRole"
    }]
}`),
		})
		if err != nil {
			return err
		}
		_, err = iam.NewRolePolicyAttachment(ctx, "task-exec-policy", &iam.RolePolicyAttachmentArgs{
			Role:      taskExecRole.Name,
			PolicyArn: pulumi.String("arn:aws:iam::aws:policy/service-role/AmazonECSTaskExecutionRolePolicy"),
		})
		if err != nil {
			return err
		}

		// Create a load balancer to listen for HTTP traffic on port 80.
		webLb, err := elb.NewLoadBalancer(ctx, "web-lb", &elb.LoadBalancerArgs{
			Subnets:        toPulumiStringArray(subnet.Ids),
			SecurityGroups: pulumi.StringArray{webSg.ID().ToStringOutput()},
		})
		if err != nil {
			return err
		}
		webTg, err := elb.NewTargetGroup(ctx, "web-tg", &elb.TargetGroupArgs{
			Port:       pulumi.Int(80),
			Protocol:   pulumi.String("HTTP"),
			TargetType: pulumi.String("ip"),
			VpcId:      pulumi.String(vpc.Id),
		})
		if err != nil {
			return err
		}
		webListener, err := elb.NewListener(ctx, "web-listener", &elb.ListenerArgs{
			LoadBalancerArn: webLb.Arn,
			Port:            pulumi.Int(80),
			DefaultActions: elb.ListenerDefaultActionArray{
				elb.ListenerDefaultActionArgs{
					Type:           pulumi.String("forward"),
					TargetGroupArn: webTg.Arn,
				},
			},
		})
		if err != nil {
			return err
		}

		//create a new ECR repository
		repo, err := ecr.NewRepository(ctx, "foo", &ecr.RepositoryArgs{})
		if err != nil {
			return err
		}

		repoCreds := repo.RegistryId.ApplyT(func(rid string) ([]string, error) {
			creds, err := ecr.GetCredentials(ctx, &ecr.GetCredentialsArgs{
				RegistryId: rid,
			})
			if err != nil {
				return nil, err
			}
			data, err := base64.StdEncoding.DecodeString(creds.AuthorizationToken)
			if err != nil {
				fmt.Println("error:", err)
				return nil, err
			}

			return strings.Split(string(data), ":"), nil
		}).(pulumi.StringArrayOutput)
		repoUser := repoCreds.Index(pulumi.Int(0))
		repoPass := repoCreds.Index(pulumi.Int(1))

		//build the image
		image, err := docker.NewImage(ctx, "my-image", &docker.ImageArgs{
			Build: docker.DockerBuildArgs{
				Context: pulumi.String("./static"),
			},
			ImageName: repo.RepositoryUrl,
			Registry: docker.ImageRegistryArgs{
				Server:   repo.RepositoryUrl,
				Username: repoUser,
				Password: repoPass,
			},
		})
		if err != nil {
			return err
		}

		containerDef := image.ImageName.ApplyT(func(name string) (string, error) {
			fmtstr := `[{
				"name": "my-app",
				"image": %q,
				"portMappings": [{
					"containerPort": 80,
					"hostPort": 80,
					"protocol": "tcp"
				}]
			}]`
			return fmt.Sprintf(fmtstr, name), nil
		}).(pulumi.StringOutput)

		// Spin up a load balanced service running NGINX.
		appTask, err := ecs.NewTaskDefinition(ctx, "app-task", &ecs.TaskDefinitionArgs{
			Family:                  pulumi.String("fargate-task-definition"),
			Cpu:                     pulumi.String("256"),
			Memory:                  pulumi.String("512"),
			NetworkMode:             pulumi.String("awsvpc"),
			RequiresCompatibilities: pulumi.StringArray{pulumi.String("FARGATE")},
			ExecutionRoleArn:        taskExecRole.Arn,
			ContainerDefinitions:    containerDef,
		})
		if err != nil {
			return err
		}
		_, err = ecs.NewService(ctx, "app-svc", &ecs.ServiceArgs{
			Cluster:        cluster.Arn,
			DesiredCount:   pulumi.Int(5),
			LaunchType:     pulumi.String("FARGATE"),
			TaskDefinition: appTask.Arn,
			NetworkConfiguration: &ecs.ServiceNetworkConfigurationArgs{
				AssignPublicIp: pulumi.Bool(true),
				Subnets:        toPulumiStringArray(subnet.Ids),
				SecurityGroups: pulumi.StringArray{webSg.ID().ToStringOutput()},
			},
			LoadBalancers: ecs.ServiceLoadBalancerArray{
				ecs.ServiceLoadBalancerArgs{
					TargetGroupArn: webTg.Arn,
					ContainerName:  pulumi.String("my-app"),
					ContainerPort:  pulumi.Int(80),
				},
			},
		}, pulumi.DependsOn([]pulumi.Resource{webListener}))
		if err != nil {
			return err
		}
		// Export the resulting web address.
		ctx.Export("url", webLb.DnsName)
		return nil
	})
}

func toPulumiStringArray(a []string) pulumi.StringArrayInput {
	var res []pulumi.StringInput
	for _, s := range a {
		res = append(res, pulumi.String(s))
	}
	return pulumi.StringArray(res)
}

```

Complexo? Sim, mas essa complexidade é inerente aos recursos da AWS e não do Pulumi. Teríamos uma complexidade similar se estivéssemos usando o Terraform ou o CDK.

Antes de executar o nosso código precisamos fazer o download das novas dependências:

    go get github.com/pulumi/pulumi-docker
    go get github.com/pulumi/pulumi-docker/sdk/v3/go/docker

Agora basta executar o comando:

    pulumi up

No output da execução será gerada a url do _load balancer_, que faremos uso para acessar o conteúdo do nosso container em execução.

## Reorganizando o código

Agora podemos começar a fazer uso das vantagens de estarmos usando uma linguagem de programação completa, como o Go. Poderíamos usar recursos da linguagem como funções, concorrência, condicionais, etc. Neste exemplo vamos organizar melhor nosso código. Para isso os passos abaixo foram realizados:

- Criado o diretório _iac_
- Criado o arquivo _iac/fargate.go_
- Movemos boa parte da lógica do `main.go`para o novo arquivo:

```go
package iac

import (
	"encoding/base64"
	"fmt"
	"strings"

	"github.com/pulumi/pulumi-aws/sdk/v4/go/aws/ec2"
	"github.com/pulumi/pulumi-aws/sdk/v4/go/aws/ecr"
	"github.com/pulumi/pulumi-aws/sdk/v4/go/aws/ecs"
	elb "github.com/pulumi/pulumi-aws/sdk/v4/go/aws/elasticloadbalancingv2"
	"github.com/pulumi/pulumi-aws/sdk/v4/go/aws/iam"
	"github.com/pulumi/pulumi-docker/sdk/v3/go/docker"
	"github.com/pulumi/pulumi/sdk/v3/go/pulumi"
)

func FargateRun(ctx *pulumi.Context) error {

	// Read back the default VPC and public subnets, which we will use.
	t := true
	vpc, err := ec2.LookupVpc(ctx, &ec2.LookupVpcArgs{Default: &t})
	if err != nil {
		return err
	}
	subnet, err := ec2.GetSubnetIds(ctx, &ec2.GetSubnetIdsArgs{VpcId: vpc.Id})
	if err != nil {
		return err
	}

	// Create a SecurityGroup that permits HTTP ingress and unrestricted egress.
	webSg, err := ec2.NewSecurityGroup(ctx, "web-sg", &ec2.SecurityGroupArgs{
		VpcId: pulumi.String(vpc.Id),
		Egress: ec2.SecurityGroupEgressArray{
			ec2.SecurityGroupEgressArgs{
				Protocol:   pulumi.String("-1"),
				FromPort:   pulumi.Int(0),
				ToPort:     pulumi.Int(0),
				CidrBlocks: pulumi.StringArray{pulumi.String("0.0.0.0/0")},
			},
		},
		Ingress: ec2.SecurityGroupIngressArray{
			ec2.SecurityGroupIngressArgs{
				Protocol:   pulumi.String("tcp"),
				FromPort:   pulumi.Int(80),
				ToPort:     pulumi.Int(80),
				CidrBlocks: pulumi.StringArray{pulumi.String("0.0.0.0/0")},
			},
		},
	})
	if err != nil {
		return err
	}

	// Create an ECS cluster to run a container-based service.
	cluster, err := ecs.NewCluster(ctx, "app-cluster", nil)
	if err != nil {
		return err
	}

	// Create an IAM role that can be used by our service's task.
	taskExecRole, err := iam.NewRole(ctx, "task-exec-role", &iam.RoleArgs{
		AssumeRolePolicy: pulumi.String(`{
    "Version": "2008-10-17",
    "Statement": [{
        "Sid": "",
        "Effect": "Allow",
        "Principal": {
            "Service": "ecs-tasks.amazonaws.com"
        },
        "Action": "sts:AssumeRole"
    }]
}`),
	})
	if err != nil {
		return err
	}
	_, err = iam.NewRolePolicyAttachment(ctx, "task-exec-policy", &iam.RolePolicyAttachmentArgs{
		Role:      taskExecRole.Name,
		PolicyArn: pulumi.String("arn:aws:iam::aws:policy/service-role/AmazonECSTaskExecutionRolePolicy"),
	})
	if err != nil {
		return err
	}

	// Create a load balancer to listen for HTTP traffic on port 80.
	webLb, err := elb.NewLoadBalancer(ctx, "web-lb", &elb.LoadBalancerArgs{
		Subnets:        toPulumiStringArray(subnet.Ids),
		SecurityGroups: pulumi.StringArray{webSg.ID().ToStringOutput()},
	})
	if err != nil {
		return err
	}
	webTg, err := elb.NewTargetGroup(ctx, "web-tg", &elb.TargetGroupArgs{
		Port:       pulumi.Int(80),
		Protocol:   pulumi.String("HTTP"),
		TargetType: pulumi.String("ip"),
		VpcId:      pulumi.String(vpc.Id),
	})
	if err != nil {
		return err
	}
	webListener, err := elb.NewListener(ctx, "web-listener", &elb.ListenerArgs{
		LoadBalancerArn: webLb.Arn,
		Port:            pulumi.Int(80),
		DefaultActions: elb.ListenerDefaultActionArray{
			elb.ListenerDefaultActionArgs{
				Type:           pulumi.String("forward"),
				TargetGroupArn: webTg.Arn,
			},
		},
	})
	if err != nil {
		return err
	}

	repo, err := ecr.NewRepository(ctx, "foo", &ecr.RepositoryArgs{})
	if err != nil {
		return err
	}

	repoCreds := repo.RegistryId.ApplyT(func(rid string) ([]string, error) {
		creds, err := ecr.GetCredentials(ctx, &ecr.GetCredentialsArgs{
			RegistryId: rid,
		})
		if err != nil {
			return nil, err
		}
		data, err := base64.StdEncoding.DecodeString(creds.AuthorizationToken)
		if err != nil {
			fmt.Println("error:", err)
			return nil, err
		}

		return strings.Split(string(data), ":"), nil
	}).(pulumi.StringArrayOutput)
	repoUser := repoCreds.Index(pulumi.Int(0))
	repoPass := repoCreds.Index(pulumi.Int(1))

	image, err := docker.NewImage(ctx, "my-image", &docker.ImageArgs{
		Build: docker.DockerBuildArgs{
			Context: pulumi.String("./static"),
		},
		ImageName: repo.RepositoryUrl,
		Registry: docker.ImageRegistryArgs{
			Server:   repo.RepositoryUrl,
			Username: repoUser,
			Password: repoPass,
		},
	})
	if err != nil {
		return err
	}

	containerDef := image.ImageName.ApplyT(func(name string) (string, error) {
		fmtstr := `[{
				"name": "my-app",
				"image": %q,
				"portMappings": [{
					"containerPort": 80,
					"hostPort": 80,
					"protocol": "tcp"
				}]
			}]`
		return fmt.Sprintf(fmtstr, name), nil
	}).(pulumi.StringOutput)

	// Spin up a load balanced service running NGINX.
	appTask, err := ecs.NewTaskDefinition(ctx, "app-task", &ecs.TaskDefinitionArgs{
		Family:                  pulumi.String("fargate-task-definition"),
		Cpu:                     pulumi.String("256"),
		Memory:                  pulumi.String("512"),
		NetworkMode:             pulumi.String("awsvpc"),
		RequiresCompatibilities: pulumi.StringArray{pulumi.String("FARGATE")},
		ExecutionRoleArn:        taskExecRole.Arn,
		ContainerDefinitions:    containerDef,
	})
	if err != nil {
		return err
	}
	_, err = ecs.NewService(ctx, "app-svc", &ecs.ServiceArgs{
		Cluster:        cluster.Arn,
		DesiredCount:   pulumi.Int(5),
		LaunchType:     pulumi.String("FARGATE"),
		TaskDefinition: appTask.Arn,
		NetworkConfiguration: &ecs.ServiceNetworkConfigurationArgs{
			AssignPublicIp: pulumi.Bool(true),
			Subnets:        toPulumiStringArray(subnet.Ids),
			SecurityGroups: pulumi.StringArray{webSg.ID().ToStringOutput()},
		},
		LoadBalancers: ecs.ServiceLoadBalancerArray{
			ecs.ServiceLoadBalancerArgs{
				TargetGroupArn: webTg.Arn,
				ContainerName:  pulumi.String("my-app"),
				ContainerPort:  pulumi.Int(80),
			},
		},
	}, pulumi.DependsOn([]pulumi.Resource{webListener}))
	if err != nil {
		return err
	}
	// Export the resulting web address.
	ctx.Export("url", webLb.DnsName)
	return nil
}

func toPulumiStringArray(a []string) pulumi.StringArrayInput {
	var res []pulumi.StringInput
	for _, s := range a {
		res = append(res, pulumi.String(s))
	}
	return pulumi.StringArray(res)
}

```

O próximo passo foi configurar o diretório `iac` para ser um módulo da linguagem Go:

    cd iac
    go mod init github.com/eminetto/post-pulumi/iac
    cd ..
    go mod edit -replace github.com/eminetto/post-pulumi/iac=./iac
    go mod tidy

O nosso `main.go` agora pode ser simplificado:

```go
package main

import (
	"github.com/eminetto/post-pulumi/iac"
	"github.com/pulumi/pulumi/sdk/v3/go/pulumi"
)

func main() {
	pulumi.Run(func(ctx *pulumi.Context) error {
		return iac.FargateRun(ctx)
	})
}

```

Assim podemos gerenciar melhor a estrutura do código que vai manipular os recursos da AWS. Podemos reaproveitar esse código em outros projetos, usar variáveis de ambiente, escrever [testes](https://www.pulumi.com/docs/guides/testing/), ou o que mais nossa imaginação permitir.

## Conclusão

Usar uma ferramenta como o Pulumi aumenta bastante o [leque de opções](https://www.pulumi.com/docs/) que podemos usar na construção da infraestrutura de um projeto, mantendo a legibilidade, [reaproveitamento de código](https://www.pulumi.com/docs/guides/pulumi-packages/) e organização.
