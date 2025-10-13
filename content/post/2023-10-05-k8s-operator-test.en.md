---
title: "Writing tests for a Kubernetes Operator"
date: 2023-10-05T07:30:43-03:00
draft: false
tags:
  - go
---

In the [last post](https://eltonminetto.dev/en/post/2023-09-08-k8s-operator-sdk/), we saw how to create a Kubernetes operator using operator-sdk. As that text was quite long, I decided to write this second post to focus on the application's testing part.

Using the operator-sdk CLI to create the operator skeleton makes an initial structure for us to write your tests. For this purpose, we will use some components:

- [envtest](https://pkg.go.dev/sigs.k8s.io/controller-runtime/pkg/envtest): a Go library that configures an instance of etcd and the Kubernetes API, simulating the main functionalities of a Kubernetes cluster for testing purposes
- [Ginkgo](https://onsi.github.io/ginkgo/): a testing framework based on the concept of ‌"Behavior Driven Development" (BDD)
- [Gomega](https://onsi.github.io/gomega/): is a test assertion library, a vital dependency on Ginkgo.

The CLI created the file ‌controllers/suite_test.go, which contains the basic structure of the Ginkgo test suite and the envtest initialization. We need to add the code that will initialize our controller so that we can write the tests. In the following diff, you can see the change made.

[![diff1](/images/posts/diff_suite_test_1.png)](/images/posts/diff_suite_test_1.png)

[![diff2](/images/posts/diff_suite_test_2.png)](/images/posts/diff_suite_test_2.png)

We need to install the dependencies to run the tests. To do this, we will use the commands:

```bash
make envtest
./bin/setup-envtest use --bin-dir ./bin/
export PATH=$PATH:bin/k8s/1.28.0-darwin-arm64/
```

The first command will install the binary setup-envtest, the second downloads the executables to our project directory, and the third adds the new files to the operating system's PATH.

The next step is to write the test. The operator-sdk documentation recommends creating a *kind_controller_test.go* file within the "controllers" directory. In our case, *application_controller_test.go*. Below, you can see the file's basic structure. In the following topics, we will create each of the tests.

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

## Application creation test

The first test we will complete is to check whether, when creating an Application, our controller also builds a Deployment and a Service. To do this, we add the following code:

```go
Context("When creating an Application", func() {
		It("Should create a Deployment and a Service", func() {
			ctx := context.Background()

			// we need to create a namespace in the cluster
			ns := corev1.Namespace{
				ObjectMeta: v1.ObjectMeta{Name: ApplicationNamespace},
			}
			Expect(k8sClient.Create(ctx, &ns)).Should(Succeed())

			// we define an Application
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
			// we added the finalizer, as described in the previous post
			controllerutil.AddFinalizer(&app, finalizer)
			// we guarantee that the creation was error-free
			Expect(k8sClient.Create(ctx, &app)).Should(Succeed())
			// we guarantee that the finalizer was created successfully
			Expect(controllerutil.ContainsFinalizer(&app, finalizer)).Should(BeTrue())

			// let's now check if the deployment was created successfully
			deplName := types.NamespacedName{Name: app.ObjectMeta.Name + "-deployment", Namespace: app.ObjectMeta.Name}
			createdDepl := &appsv1.Deployment{}

			// due to the asynchronous nature of Kubernetes, we will
			// make use of Ginkgo's Eventually function. It will
			// execute the function according to the interval value,
			// until the timeout has ended, or the result is true
			Eventually(func() bool {
				err := k8sClient.Get(ctx, deplName, createdDepl)
				if err != nil {
					return false
				}
				return true
			}, timeout, interval).Should(BeTrue())

			// let's check if the Deployment data was created as expected
			Expect(createdDepl.Spec.Template.Spec.Containers[0].Image).Should(Equal(app.Spec.Image))
			// the Application must be the Owner of the Deployment
			Expect(createdDepl.ObjectMeta.OwnerReferences[0].Name).Should(Equal(app.Name))

			// let's do the same with the Service, ensuring that the controller created it as expected
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

I added comments to the code to describe the details.

## Application update test

The subsequent test checks whether, when changing an Application, the controller reflects the modification in the other objects:

```go
Context("When updating an Application", func() {
		It("Should update the Deployment", func() {
			ctx := context.Background()

			// let's first retrieve the Application in the cluster
			appName := types.NamespacedName{Name: ApplicationName, Namespace: ApplicationNamespace}
			app := minettodevv1alpha1.Application{}
			Eventually(func() bool {
				err := k8sClient.Get(ctx, appName, &app)
				if err != nil {
					return false
				}
				return true
			}, timeout, interval).Should(BeTrue())

			// let's retrieve the Deployment to ensure that the data is the same as the Application
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

			// let's change the Application
			app.Spec.Image = "caddy:latest"
			Expect(k8sClient.Update(ctx, &app)).Should(Succeed())

			// let's check if the change in the Application was reflected in the Deployment
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

## Testing the deletion of an Application

```go
Context("When deleting an Application", func() {
		It("Should delete the Deployment and Service", func() {
			appName := types.NamespacedName{Name: ApplicationName, Namespace: ApplicationNamespace}
			// checks whether the deletion was successful
			Eventually(func() error {
				a := &minettodevv1alpha1.Application{}
				k8sClient.Get(context.Background(), appName, a)
				return k8sClient.Delete(context.Background(), a)
			}, timeout, interval).Should(Succeed())

			// ensures that the Application no longer exists in the cluster
			// this test is not really necessary as the Delete happened successfully
			// I kept this test here for educational purposes only.
			Eventually(func() error {
				a := &minettodevv1alpha1.Application{}
				return k8sClient.Get(context.Background(), appName, a)
			}, timeout, interval).ShouldNot(Succeed())

			// according to this documentation: https://book.kubebuilder.io/reference/envtest.html#testing-considerations
			// we cannot test the cluster's garbage collection to ensure that the Deployment and Service created were removed,
			// but in the first test we check the ownership, so they will be removed as expected in a real cluster
		})
	})
```

## Conclusions

Once again, it is possible to see how operator-sdk facilitates the operator development process by creating a structure to make it easy to test our controller.

Using envtest is also very useful as it allows us to test the functionality of a Kubernetes cluster without the need to install one, which is very important in CI/CD environments.

Another interesting point is the use of Ginkgo, which makes the tests readable and easy to understand. This project was my first time using Ginkgo, and I liked the results. I should add it to my toolbox for future projects.

I hope this post serves as an introduction to operator testing. You can find links with more advanced topics and examples in the [official documentation](https://sdk.operatorframework.io/docs/building-operators/golang/testing/), and I recommend reading them as the next steps.

You can find the code in this post on [GitHub](https://github.com/eminetto/k8s-operator-talk).
