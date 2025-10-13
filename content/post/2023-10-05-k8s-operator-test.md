---
title: "Escrevendo testes para um Kubernetes Operator"
date: 2023-10-05T07:30:43-03:00
draft: false
tags:
  - go
---

No [último post](https://eltonminetto.dev/post/2023-09-08-k8s-operator-sdk/) vimos como criar um Kubernetes _operator_ usando o _operator-sdk_. Como aquele texto ficou bem grande resolvi escrever este segundo post, para poder focar na parte dos testes da aplicação.

Quando usamos o _CLI_ do _operator-sdk_ para criar o esqueleto do _operator_ é criado também uma estrutura básica para escrevermos os seus testes. Para este fim vamos usar alguns componentes importantes:

- [envtest](https://pkg.go.dev/sigs.k8s.io/controller-runtime/pkg/envtest): uma biblioteca Go que configura uma instância do _etcd_ e da API do Kubernetes, simulando as principais funcionalidades de um cluster Kubernetes para fins de teste
- [Ginkgo](https://onsi.github.io/ginkgo/): um framework de testes baseado no conceito de _‌"Behavior Driven Development" (BDD)_
- [Gomega](https://onsi.github.io/gomega/): uma biblioteca de asserções de testes, que é uma dependência importante ao _Ginkgo_.

O _scaffolding_ do _CLI_ criou o arquivo _‌controllers/suite_test.go_, que contém a estrutura básica da suite de testes do _Ginkgo_ e a inicialização do _envtest_. O que precisamos fazer é adicionar o código que vai inicializar o nosso _controller_, para que possamos escrever os testes. No _diff_ a seguir é possível ver a alteração feita.

[![diff1](/images/posts/diff_suite_test_1.png)](/images/posts/diff_suite_test_1.png)

[![diff2](/images/posts/diff_suite_test_2.png)](/images/posts/diff_suite_test_2.png)

Precisamos instalar as dependências para executar os testes. Para isso vamos usar os comandos:

```bash
make envtest
./bin/setup-envtest use --bin-dir ./bin/
export PATH=$PATH:bin/k8s/1.28.0-darwin-arm64/
```

O primeiro comando vai instalar o binário do `setup-envtest`, o segundo faz o download dos executáveis para o diretório do nosso projeto, e o terceiro comando adiciona os novos arquivos no _PATH_ do sistema operacional.

O próximo passo é escrevermos o teste. Para isso o recomendado é criarmos um arquivo *kind_controller_test.go* dentro do diretório *controllers*. No nosso caso, o *application_controller_test.go*. A estrutura básica do arquivo é mostrada abaixo. Nos próximos tópicos vamos criar cada um dos testes.

```go
package controllers

import (
	"context"
	"time"

	minettodevv1alpha1 "github.com/eminetto/k8s-operator-talk/api/v1alpha1"
	. "github.com/onsi/ginkgo/v2"
	. "github.com/onsi/gomega"
	appsv1 "k8s.io/api/apps/v1"
	corev1 "k8s.io/api/core/v1"
	v1 "k8s.io/apimachinery/pkg/apis/meta/v1"
	"k8s.io/apimachinery/pkg/types"
	"k8s.io/apimachinery/pkg/util/intstr"
	"sigs.k8s.io/controller-runtime/pkg/controller/controllerutil"
)

// Define utility constants for object names and testing timeouts/durations and intervals.
const (
	ApplicationName      = "test-app"
	ApplicationNamespace = "test-app"

	timeout  = time.Second * 10
	duration = time.Second * 10
	interval = time.Millisecond * 250
)

var _ = Describe("Application controller", func() {
	Context("When creating an Application", func() {
		It("Should create a Deployment and a Service", func() {
		})
	})

	Context("When updating an Application", func() {
		It("Should update the Deployment", func() {
		})
	})

	Context("When deleting an Application", func() {
		It("Should delete the Deployment and Service", func() {
		})
	})
})

```

## Teste da criação da Application

O primeiro teste que vamos preencher é que verifica se, ao criar um _Application_ é criado também um _Deployment_ e um _Service_, conforme o código do nosso _controller_. Para isso adicionamos o seguinte código:

```go
Context("When creating an Application", func() {
		It("Should create a Deployment and a Service", func() {
			ctx := context.Background()

			// precisamos criar uma namespace no cluster
			ns := corev1.Namespace{
				ObjectMeta: v1.ObjectMeta{Name: ApplicationNamespace},
			}
			Expect(k8sClient.Create(ctx, &ns)).Should(Succeed())

			//definimos uma Application
			app := minettodevv1alpha1.Application{
				TypeMeta: v1.TypeMeta{
					Kind:       "Application",
					APIVersion: "v1alpha1",
				},
				ObjectMeta: v1.ObjectMeta{
					Name:      ApplicationName,
					Namespace: ApplicationNamespace,
				},
				Spec: minettodevv1alpha1.ApplicationSpec{
					Image:    "nginx:latest",
					Replicas: 1,
					Port:     80,
				},
			}
			//adicionamos o finalizer, conforme descrito no post anterior
			controllerutil.AddFinalizer(&app, finalizer)
			//garantimos que a criação não teve erros
			Expect(k8sClient.Create(ctx, &app)).Should(Succeed())
			//garantimos que o finalizer foi criado com sucesso
			Expect(controllerutil.ContainsFinalizer(&app, finalizer)).Should(BeTrue())

			// vamos agora verificar se o deployment foi criado com sucesso
			deplName := types.NamespacedName{Name: app.ObjectMeta.Name + "-deployment", Namespace: app.ObjectMeta.Name}
			createdDepl := &appsv1.Deployment{}

			//devido a natureza assíncrona do Kubernetes vamos fazer uso da função Eventually do Ginkgo
			//ele vai executar a função de acordo com o valor do intervalo, até que o timeout tenha terminado,
			//ou o resultado seja true
			Eventually(func() bool {
				err := k8sClient.Get(ctx, deplName, createdDepl)
				if err != nil {
					return false
				}
				return true
			}, timeout, interval).Should(BeTrue())
			//vamos verificar se os dados do Deployment foram criados de acordo com o esperado
			Expect(createdDepl.Spec.Template.Spec.Containers[0].Image).Should(Equal(app.Spec.Image))
			//o Application deve ser o Owner do Deployment
			Expect(createdDepl.ObjectMeta.OwnerReferences[0].Name).Should(Equal(app.Name))

			// vamos fazer o mesmo com o Service, garantindo que o controller criou conforme o esperado
			srvName := types.NamespacedName{Name: app.ObjectMeta.Name + "-service", Namespace: app.ObjectMeta.Name}
			createdSrv := &corev1.Service{}

			Eventually(func() bool {
				err := k8sClient.Get(ctx, srvName, createdSrv)
				if err != nil {
					return false
				}
				return true
			}, timeout, interval).Should(BeTrue())
			Expect(createdSrv.Spec.Ports[0].TargetPort).Should(Equal(intstr.FromInt(int(app.Spec.Port))))
			Expect(createdDepl.ObjectMeta.OwnerReferences[0].Name).Should(Equal(app.Name))
		})
	})
```

Adicionei comentários no código para descrever o que está sendo testado.

## Teste da atualização da Application

O próximo teste verifica se ao alterar uma _Application_ a modificação é refletida nos demais objetos:

```go
Context("When updating an Application", func() {
		It("Should update the Deployment", func() {
			ctx := context.Background()

			//vamos primeiro buscar a Application no cluster
			appName := types.NamespacedName{Name: ApplicationName, Namespace: ApplicationNamespace}
			app := minettodevv1alpha1.Application{}
			Eventually(func() bool {
				err := k8sClient.Get(ctx, appName, &app)
				if err != nil {
					return false
				}
				return true
			}, timeout, interval).Should(BeTrue())

			// vamos buscar o Deployment para garantir que os dados estão iguais aos do Application
			deplName := types.NamespacedName{Name: app.ObjectMeta.Name + "-deployment", Namespace: app.ObjectMeta.Name}
			createdDepl := &appsv1.Deployment{}

			Eventually(func() bool {
				err := k8sClient.Get(ctx, deplName, createdDepl)
				if err != nil {
					return false
				}
				return true
			}, timeout, interval).Should(BeTrue())
			Expect(createdDepl.Spec.Template.Spec.Containers[0].Image).Should(Equal(app.Spec.Image))

			//vamos alterar a Application
			app.Spec.Image = "caddy:latest"
			Expect(k8sClient.Update(ctx, &app)).Should(Succeed())

			//vamos conferir se a alteração no Application se refletiu no Deployment
			Eventually(func() bool {
				err := k8sClient.Get(ctx, deplName, createdDepl)
				if err != nil {
					return false
				}
				if createdDepl.Spec.Template.Spec.Containers[0].Image == "caddy:latest" {
					return true
				}
				return false
			}, timeout, interval).Should(BeTrue())
		})
	})
```

Novamente, os comentários descrevem o que está sendo testado.

## Testando a exclusão de uma Application

```go
Context("When deleting an Application", func() {
		It("Should delete the Deployment and Service", func() {
			appName := types.NamespacedName{Name: ApplicationName, Namespace: ApplicationNamespace}
			//verifica se a exclusão aconteceu com sucesso
			Eventually(func() error {
				a := &minettodevv1alpha1.Application{}
				k8sClient.Get(context.Background(), appName, a)
				return k8sClient.Delete(context.Background(), a)
			}, timeout, interval).Should(Succeed())

			//garante que o Application não existe mais no cluster
			//este teste não é realmente necessário, pois o Delete aconteceu com sucesso
			//mantive este teste aqui apenas para fins didáticos
			Eventually(func() error {
				a := &minettodevv1alpha1.Application{}
				return k8sClient.Get(context.Background(), appName, a)
			}, timeout, interval).ShouldNot(Succeed())

			// de acordo com esta documentação : https://book.kubebuilder.io/reference/envtest.html#testing-considerations
			// não podemos testar o garbage collection do cluster, para garantir que o Deployment e o Service criados foram removidos
			// mas no primeiro teste nós verificamos o ownership, então eles serão removidos de acordo com o esperado em um cluster real
		})
	})
```

## Conclusões

Novamente é possível ver como o _operator-sdk_ facilita o processo de desenvolvimento de _operators_ pois ele cria uma estrutura para que os testes sejam facilmente escritos. O uso do _envtest_ também é muito útil pois nos permite testar a funcionalidade de um cluster Kubernetes sem a necessidade de instalarmos um, o que é bem importante em ambientes de CI/CD. Outro ponto interessante é o uso do _Ginkgo_ que torna os testes bem legíveis e de fácil entendimento. É a primeira vez que uso o _Ginkgo_ e gostei bastante do resultado, devo adicionar ele na minha caixa de ferramentas para próximos projetos.

Espero que este post sirva de introdução aos testes de _operators_. Na [documentação oficial](https://sdk.operatorframework.io/docs/building-operators/golang/testing/) é possível encontrar links com tópicos e exemplos mais avançados e recomendo a leitura como próximos passos.

O código pode ser encontrado [neste repositório](https://github.com/eminetto/k8s-operator-talk).
