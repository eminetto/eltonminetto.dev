---
title: "Creating Kubernetes Operators with operator-sdk"
date: 2023-09-08T08:30:43-03:00
draft: false
tags:
  - go
---

If you develop APIs or microservices, especially in medium to large environments, you probably use Kubernetes.

[Kubernetes](https://kubernetes.io/) is a project created by Google in mid-2015 that quickly became the standard for managing container execution. You can host it on your machines or use a solution delivered by one of the big cloud players like [AWS](https://aws.amazon.com/pt/eks/), [Google](https://cloud.google.com/kubernetes-engine), and [DigitalOcean](https://docs.digitalocean.com/products/kubernetes/).

In this post, I want to talk about another functionality: the possibility of extending it to create new capabilities. Let's start with the essential concepts for understanding this article.

## Resources and Controllers

One of the most fundamental concepts is that K8s manage resources. According to official [documentation](https://kubernetes.io/pt-br/docs/home/),

> A resource is an endpoint in the Kubernetes API that stores a collection of API objects of a specific type; for example, the built-in "pods" resource contains a collection of Pod objects.

K8s manages these resources using another concept: controllers. When we use a k8s feature, we need to define, in a yaml file, what state we expect. For example:

```yaml
apiVersion: apps/v1
kind: Deployment
metadata:
  name: nginx-deployment
spec:
  selector:
    matchLabels:
      app: nginx
  replicas: 2 # tells deployment to run 2 pods that match the template
  template:
    metadata:
      labels:
        app: nginx
    spec:
      containers:
        - name: nginx
          image: nginx:1.14.2
          ports:
            - containerPort: 80
```

The information inside the _spec_ key corresponds to the desired state of the resource.

What k8s does is ensure that the current state of the object contained in the cluster is equal to the desired one that was declared. In this case, two Nginx containers, version 1.14.2, are running on port 80. It does this using what is called a _control loop_:

[![operator-reconciliation-kube-only](/images/posts/operator-reconciliation-kube-only.png)](/images/posts/operator-reconciliation-kube-only.png)

It checks whether the current state of the resource differs from the desired state, and if so, it executes the _Reconcile_ function of the controller linked to the object. This way, we can define a controller like this:

> A controller tracks at least one type of Kubernetes resource. These objects have a spec field that represents the desired state. This resource's controller(s) are responsible for bringing the current state closer to that desired state.

K8s has a series of built-in resources such as _Pod_, _Deployment_, _Service_, and controllers that track the lifecycle of each of them. But in addition to them, we can create our resources through _Custom Resource Definitions (CRD)_. The combination of a CRD and a controller is what we call an [_operator_](https://kubernetes.io/docs/concepts/extend-kubernetes/operator/), and it is what we will explore in this text.

## operator-sdk

To illustrate what we can do with an operator, I will create a proof of concept using operator-sdk. According to the [official website](https://sdk.operatorframework.io/)::

> The Operator SDK makes it easy to build Kubernetes-native applications, a process that can require deep, application-specific operational knowledge. This project is a component of the [Operator Framework](https://github.com/operator-framework), an open-source toolkit for managing native Kubernetes applications called Operators in a practical, automated, and scalable way.

Creating an operator using [Go](https://sdk.operatorframework.io/docs/building-operators/golang/quickstart/), [Ansible](https://sdk.operatorframework.io/docs/building-operators/ansible/quickstart/) or [Helm](https://sdk.operatorframework.io/docs/building-operators/helm/quickstart/) is possible. In this article, I will use Go.

The first step is to install the SDK CLI on the machine. I used brew, but the other options are in the [documentation](https://sdk.operatorframework.io/docs/installation/).

```bash
brew install operator-sdk
```

The next step is to use the CLI to generate the project scaffolding using the commands:

```bash
operator-sdk init --domain minetto.dev --repo github.com/eminetto/k8s-operator-talk
operator-sdk create api --version v1alpha1 --kind Application --resource --controller
```

The first command initializes the project by indicating the domain, information that k8s will use to identify the resource and the repository name used for the Go package name. The second command creates a new _Application_ resource in the _alpha1_ version and a controller skeleton.

Before we get into the code, it's essential to understand the purpose of the proof of concept. In its native form, getting an application running on K8s requires the developer to understand concepts such as Deployment, Pod, Service, etc. My goal is to reduce this cognitive load to just two resources: a [_namespace_](https://kubernetes.io/docs/concepts/overview/working-with-objects/namespaces/), where the Application will reside within the cluster, and an _Application_, which will define the desired state of an application. For example, the team only needs to create the following _yaml_ :

```yaml
apiVersion: v1
kind: Namespace
metadata:
  name: application-sample
---
apiVersion: minetto.dev/v1alpha1
kind: Application
metadata:
  name: application-sample
  namespace: application-sample
spec:
  image: nginx:latest
  replicas: 2
  port: 80
```

Apply it to the cluster using the command:

```bash
kubectl apply -f application.yaml
```

And the rest will be created by our controller.

The first step is configuring our resource to have fields related to _spec_. To do this, you must change the _api/v1alpha/application_types.go_ file and add the fields to the struct:

```go
type ApplicationSpec struct {
	Image    string `json:"image,omitempty"`
	Replicas int32  `json:"replicas,omitempty"`
	Port     int32  `json:"port,omitempty"`
}
```

Later, we will use this information to generate the files necessary to install the CRD on our cluster. We will also use this structure to create the required resources.

The next step is to create the logic for our controller. _operator-sdk_ made the controllers/application_controller.go file and the ‌Reconcile function signature. This function is called by the control loop each time k8s detects a difference between the current state of the object and the desired state. In the _main.go_ file that the SDK generated, we have the link between the Application resource and our controller, and we don't need to worry about it now. One of the advantages of operator-sdk is that it allows us to focus on the controller logic and abstracts all the massive details necessary for it to work.

The _Reconcile_ function code and auxiliaries are below. I tried to document the most important excerpts:

```go
func (r *ApplicationReconciler) Reconcile(ctx context.Context, req ctrl.Request) (ctrl.Result, error) {
	l := log.FromContext(ctx)
	var app minettodevv1alpha1.Application
	//recupera os detalhes do objeto sendo gerenciado
	if err := r.Get(ctx, req.NamespacedName, &app); err != nil {
		if apierrors.IsNotFound(err) {
			return ctrl.Result{}, nil
		}
		l.Error(err, "unable to fetch Application")
		return ctrl.Result{}, err
	}
	/*
	The finalizer is essential because it tells K8s we need control over object deletion.
	After all, how we will create other resources must be excluded together.
	Without the finalizer, there is no time for the K8s garbage collector to delete,
	and we risk having useless resources in the cluster.
	*/
	if !controllerutil.ContainsFinalizer(&app, finalizer) {
		l.Info("Adding Finalizer")
		controllerutil.AddFinalizer(&app, finalizer)
		return ctrl.Result{}, r.Update(ctx, &app)
	}

	if !app.DeletionTimestamp.IsZero() {
		l.Info("Application is being deleted")
		return r.reconcileDelete(ctx, &app)
	}
	l.Info("Application is being created")
	return r.reconcileCreate(ctx, &app)
}

func (r *ApplicationReconciler) reconcileCreate(ctx context.Context, app *minettodevv1alpha1.Application) (ctrl.Result, error) {
	l := log.FromContext(ctx)
	l.Info("Creating deployment")
	err := r.createOrUpdateDeployment(ctx, app)
	if err != nil {
		return ctrl.Result{}, err
	}
	l.Info("Creating service")
	err = r.createService(ctx, app)
	if err != nil {
		return ctrl.Result{}, err
	}
	return ctrl.Result{}, nil
}

func (r *ApplicationReconciler) createOrUpdateDeployment(ctx context.Context, app *minettodevv1alpha1.Application) error {
	var depl appsv1.Deployment
	deplName := types.NamespacedName{Name: app.ObjectMeta.Name + "-deployment", Namespace: app.ObjectMeta.Name}
	if err := r.Get(ctx, deplName, &depl); err != nil {
		if !apierrors.IsNotFound(err) {
			return fmt.Errorf("unable to fetch Deployment: %v", err)
		}
		/*If there is no Deployment, we will create it.
		An essential section in the definition is OwnerReferences, as it indicates to k8s that
		an Application is creating this resource.
		This is how k8s knows that when we remove an Application, it must also remove
		all the resources it created.
		Another important detail is that we use data from our Application to create the Deployment,
		such as image information, port, and replicas.
		*/
		if apierrors.IsNotFound(err) {
			depl = appsv1.Deployment{
				ObjectMeta: metav1.ObjectMeta{
					Name:        app.ObjectMeta.Name + "-deployment",
					Namespace:   app.ObjectMeta.Name,
					Labels:      map[string]string{"label": app.ObjectMeta.Name, "app": app.ObjectMeta.Name},
					Annotations: map[string]string{"imageregistry": "https://hub.docker.com/"},
					OwnerReferences: []metav1.OwnerReference{
						{
							APIVersion: app.APIVersion,
							Kind:       app.Kind,
							Name:       app.Name,
							UID:        app.UID,
						},
					},
				},
				Spec: appsv1.DeploymentSpec{
					Replicas: &app.Spec.Replicas,
					Selector: &metav1.LabelSelector{
						MatchLabels: map[string]string{"label": app.ObjectMeta.Name},
					},
					Template: v1.PodTemplateSpec{
						ObjectMeta: metav1.ObjectMeta{
							Labels: map[string]string{"label": app.ObjectMeta.Name, "app": app.ObjectMeta.Name},
						},
						Spec: v1.PodSpec{
							Containers: []v1.Container{
								{
									Name:  app.ObjectMeta.Name + "-container",
									Image: app.Spec.Image,
									Ports: []v1.ContainerPort{
										{
											ContainerPort: app.Spec.Port,
										},
									},
								},
							},
						},
					},
				},
			}
			err = r.Create(ctx, &depl)
			if err != nil {
				return fmt.Errorf("unable to create Deployment: %v", err)
			}
			return nil
		}
	}
	/*The controller also needs to manage the update because if the dev changes any information
	in an existing Application, this must impact other resources.*/
	depl.Spec.Replicas = &app.Spec.Replicas
	depl.Spec.Template.Spec.Containers[0].Image = app.Spec.Image
	depl.Spec.Template.Spec.Containers[0].Ports[0].ContainerPort = app.Spec.Port
	err := r.Update(ctx, &depl)
	if err != nil {
		return fmt.Errorf("unable to update Deployment: %v", err)
	}
	return nil
}

func (r *ApplicationReconciler) createService(ctx context.Context, app *minettodevv1alpha1.Application) error {
	srv := v1.Service{
		ObjectMeta: metav1.ObjectMeta{
			Name:      app.ObjectMeta.Name + "-service",
			Namespace: app.ObjectMeta.Name,
			Labels:    map[string]string{"app": app.ObjectMeta.Name},
			OwnerReferences: []metav1.OwnerReference{
				{
					APIVersion: app.APIVersion,
					Kind:       app.Kind,
					Name:       app.Name,
					UID:        app.UID,
				},
			},
		},
		Spec: v1.ServiceSpec{
			Type:                  v1.ServiceTypeNodePort,
			ExternalTrafficPolicy: v1.ServiceExternalTrafficPolicyTypeLocal,
			Selector:              map[string]string{"app": app.ObjectMeta.Name},
			Ports: []v1.ServicePort{
				{
					Name:       "http",
					Port:       app.Spec.Port,
					Protocol:   v1.ProtocolTCP,
					TargetPort: intstr.FromInt(int(app.Spec.Port)),
				},
			},
		},
		Status: v1.ServiceStatus{},
	}
	_, err := controllerutil.CreateOrUpdate(ctx, r.Client, &srv, func() error {
		return nil
	})
	if err != nil {
		return fmt.Errorf("unable to create Service: %v", err)
	}
	return nil
}

func (r *ApplicationReconciler) reconcileDelete(ctx context.Context, app *minettodevv1alpha1.Application) (ctrl.Result, error) {
	l := log.FromContext(ctx)

	l.Info("removing application")

	controllerutil.RemoveFinalizer(app, finalizer)
	err := r.Update(ctx, app)
	if err != nil {
		return ctrl.Result{}, fmt.Errorf("Error removing finalizer %v", err)
	}
	return ctrl.Result{}, nil
}
```

To deploy our customized resource and its controller, the SDK provides commands in its _Makefile_ :

```bash
make manifests
make docker-build docker-push IMG=registry.hub.docker.com/eminetto/k8s-operator-talk:latest
make deploy IMG=registry.hub.docker.com/eminetto/k8s-operator-talk:latest
```

The first command generates all the files necessary to create the CRD. The second generates a docker container and pushes it to the indicated repository. The last command installs the generated container on the cluster. Tip: You can automate controller generation and installation in your development environment using Tilt. This project's [repository](https://github.com/eminetto/k8s-operator-talk) has a Tiltfile that does all this work. To learn more about Tilt, check out [my post](https://eltonminetto.dev/en/post/2022-08-31-improve-local-development-tilt/) about the tool.

Now, apply the _yaml_ with the _Application_ definition to the cluster, and the controller will generate the _Deployment_ and _Service_ necessary for the Application to run.

We can check that the controller created the resources with the following commands.

```bash
kubectl -n application-sample get applications
NAME                 AGE
application-sample   18s
```

```bash
kubectl -n application-sample get deployments
NAME                            READY   UP-TO-DATE   AVAILABLE   AGE
application-sample-deployment   2/2     2            2           41s
```

```bash
kubectl -n application-sample get pods
NAME                                             READY   STATUS    RESTARTS   AGE
application-sample-deployment-65b96554f8-8vv64   1/1     Running   0          56s
application-sample-deployment-65b96554f8-v54gp   1/1     Running   0          56s
```

```bash
kubectl -n application-sample get services
NAME                         TYPE       CLUSTER-IP     EXTERNAL-IP   PORT(S)        AGE
application-sample-service   NodePort   10.43.63.164   <none>        80:32591/TCP   66s
```

This post ended up being quite long, so there are other topics that I will leave for a future text, such as the testing part. But I hope I was able to spark interest in this subject. I'm very excited about it and believe it has incredible potential to help create automation that makes the lives of development and operations teams much more effortless.
