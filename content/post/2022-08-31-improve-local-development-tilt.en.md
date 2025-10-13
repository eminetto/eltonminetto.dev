---
title: "Accelerate your local development environment with Tilt"
date: 2022-08-31T13:00:19-03:00
draft: false
tags:
  - go
---

We spend hours and hours developing applications on our machines, with more and more requirements and complexity. In addition, any modern application has multiple containers, microservices, deployments in different environments, various stacks, etc. So any tool that can make our flow more agile is handy.

In this post, I want to introduce a powerful tool that can save you a lot of time in your development process. This is [Tilt](https://tilt.dev), which was recently [acquired](https://www.docker.com/blog/welcome-tilt-fixing-the-pains-of-microservice-development-for-kubernetes/) by Docker.

To demonstrate a little bit of what you can do with Tilt, I will use this [repository](https://github.com/eminetto/talk-microservices-go) I used in a talk about [microservices](https://eltonminetto/dev/files/talks/presentation-190914144745.pdf) (in Portuguese). The examples will be made in Go, but in the official documentation, you can see how to use it with [other technologies](https://docs.tilt.dev/example_static_html.html) and scenarios.

## Installation

The first step is to install the application from the command line. For that, on my macOS, I ran:

```bash
curl -fsSL https://raw.githubusercontent.com/tilt-dev/tilt/master/scripts/install.sh | bash
```

The [documentation](https://docs.tilt.dev/install.html) shows how to install it on other operating systems.

## First steps

Tilt works by reading a file called `Tiltfile` at the root of your project. It has a syntax resembling `Python`, and the [documentation](https://docs.tilt.dev/api.html) is very detailed, showing all the options we can use.

The contents of the `Tiltfile` file looked like this:

```python
local_resource('auth', cmd='cd auth; go build -o bin/auth main.go',
               serve_cmd='auth/bin/auth', deps=['auth/main.go', 'auth/security', 'auth/user', 'pkg'])

local_resource('feedbacks', cmd='cd feedbacks; go build -o bin/feedbacks main.go',
               serve_cmd='feedbacks/bin/feedbacks', deps=['feedbacks/main.go', 'feedbacks/feedback', 'pkg'])


local_resource('votes', cmd='cd votes; go build -o bin/votes main.go',
               serve_cmd='votes/bin/votes', deps=['votes/main.go', 'votes/vote', 'pkg'])

```

The `local_resource` function configures actions that will be executed on your local machine, and the first parameter is the name we are giving the resource, which must be unique within the `Tiltfile`.

The `cmd` parameter contains the command to be executed. The information contained within the `serve_cmd` parameter will be performed by Tilt and is expected not to terminate. That is, it is the command that will run our service.

The last parameter, `deps`, is one of the most interesting. It indicates which project directories Tilt will watch; if changes are made, it will automatically run the process. So, for example, if any changes happen to `auth/main.go`, `auth/security`, `auth/user`, or `pkg,` the `auth` service will be recompiled and run again. As it is a compiled language like Go, this is a great help because changing the file will automatically generate it, saving us developers precious time.

As our project consists of three microservices, the rest of the `Tiltfile` configures the same behavior for all.

To run Tilt, just open a terminal and type:

```bash
tilt up
```

The following is presented:

[![tilt](/images/posts/tilt.png)](/images/posts/tilt.png)

Pressing the spacebar brings us to Tilt's graphical interface, where we'll spend a lot of time:

[![tilt](/images/posts/tilt_ui.png)](/images/posts/tilt_ui.png)

We can check each application's compilation log on this interface and execute the desired step again. It also aggregates the application logs and allows us to perform searches on them:

[![tilt](/images/posts/tilt_ui_log.png)](/images/posts/tilt_ui_log.png)

Compilation errors also appear on this screen:

[![tilt](/images/posts/tilt_ui_error.png)](/images/posts/tilt_ui_error.png)

These features alone that I've presented so far should be enough to put Tilt on your list of tools to test, right? But let's delve a little deeper.

## Containers

Let's now improve our environment. Instead of running the binaries locally, we will add the ability to automatically create and update containers for our microservices. After all, they should run this way in the production environment.

The new version of `Tiltfile` looks like this:

```python
local_resource(
    'auth-compile',
    cmd='cd auth; CGO_ENABLED=0 GOOS=linux GOARCH=amd64 go build -o bin/auth main.go',
    deps=['auth/main.go', 'auth/security', 'auth/user', 'pkg'],
)

docker_build(
    'auth-image',
    './auth',
    dockerfile='auth/Dockerfile',
)

local_resource(
    'feedbacks-compile',
    cmd='cd feedbacks; CGO_ENABLED=0 GOOS=linux GOARCH=amd64 go build -o bin/feedbacks main.go',
    deps=['feedbacks/main.go', 'feedbacks/feedback', 'pkg'],
)

docker_build(
    'feedbacks-image',
    './feedbacks',
    dockerfile='feedbacks/Dockerfile',
)

local_resource(
    'votes-compile',
    cmd='cd votes; CGO_ENABLED=0 GOOS=linux GOARCH=amd64 go build -o bin/votes main.go',
    deps=['votes/main.go', 'votes/vote', 'pkg'],
)

docker_build(
    'votes-image',
    './votes',
    dockerfile='votes/Dockerfile',
)

docker_compose('./docker-compose.yml')
```

I added the `docker_build` function. As the name suggests, it generates the container image. For that, creating a `Dockerfile` was necessary for each microservice. For example, the one for the `auth` service looks like this:

```yaml
FROM alpine
ADD bin/auth /
EXPOSE 8081
CMD ["/auth"]
```

The other services were very similar, just changing the name of the executable and the port: `feedbacks` runs on port `8082` and `votes` on `8083`.

When making this change, Tilt will warn that it is necessary to have some way of deploying containers; otherwise, it will not work. One way to do this is to create a `docker-compose.yml` and use it in the `docker_compose` function. Your content looks like this:

```yaml
version: "3"
services:
  auth:
    image: auth-image
    ports:
      - "8081:8081"
    container_name: auth
  feedbacks:
    image: feedbacks-image
    ports:
      - "8082:8082"
    container_name: feedbacks
  votes:
    image: votes-image
    ports:
      - "8083:8083"
    container_name: votes
```

With these changes, Tilt now observes modifications in the project's codes, and if they happen, it does the compilation, generation of containers, and updating of the environment!

[![tilt](/images/posts/tilt_ui_docker.png)](/images/posts/tilt_ui_docker.png)

## Kubernetes!!

Now let's make it a little more serious! Let's have Tilt deploy our application to a Kubernetes cluster. For this, I will use [minikube](https://minikube.sigs.k8s.io/docs/), a solution that installs a local environment for development.

On macOS, just run:

```bash
brew install minikube
minikube start
```

Now that we have our cluster set up, let's change our `Tiltfile` to reflect the new environment:

```python
load('ext://restart_process', 'docker_build_with_restart')
local_resource(
    'auth-compile',
    cmd='cd auth; CGO_ENABLED=0 GOOS=linux GOARCH=amd64 go build -o bin/auth main.go',
    deps=['auth/main.go', 'auth/security', 'auth/user', 'pkg'],
)

docker_build_with_restart(
    'auth-image',
    './auth',
    dockerfile='auth/Dockerfile',
    entrypoint=['/auth'],
    live_update=[
        sync('./auth/bin/auth', '/auth'),
    ],
)

k8s_yaml('auth/kubernetes.yaml')
k8s_resource('ms-auth', port_forwards=8081,
             resource_deps=['auth-compile'])


local_resource(
    'feedbacks-compile',
    cmd='cd feedbacks; CGO_ENABLED=0 GOOS=linux GOARCH=amd64 go build -o bin/feedbacks main.go',
    deps=['feedbacks/main.go', 'feedbacks/feedback', 'pkg'],
)

docker_build_with_restart(
    'feedbacks-image',
    './feedbacks',
    dockerfile='feedbacks/Dockerfile',
    entrypoint=['/feedbacks'],
    live_update=[
        sync('./feedbacks/bin/feedbacks', '/feedbacks'),
    ],
)

k8s_yaml('feedbacks/kubernetes.yaml')
k8s_resource('ms-feedbacks', port_forwards=8082,
             resource_deps=['feedbacks-compile'])


local_resource(
    'votes-compile',
    cmd='cd votes; CGO_ENABLED=0 GOOS=linux GOARCH=amd64 go build -o bin/votes main.go',
    deps=['votes/main.go', 'votes/vote', 'pkg'],
)

docker_build_with_restart(
    'votes-image',
    './votes',
    dockerfile='votes/Dockerfile',
    entrypoint=['/votes'],
    live_update=[
        sync('./votes/bin/votes', '/votes'),
    ],
)

k8s_yaml('votes/kubernetes.yaml')
k8s_resource('ms-votes', port_forwards=8083,
             resource_deps=['votes-compile'])

```

There are a lot of new things where!

The first is the `load` function which loads Tilt [extensions](https://docs.tilt.dev/extensions.html). It's a way to expand the tool's features, and [several](https://github.com/tilt-dev/tilt-extensions) are available. Here we are using `docker_build_with_restart,` which will update the container running inside our Kubernetes cluster.

Another change is related to the application deployment settings within Kubernetes. The `k8s_yaml` function indicates which file contains the "recipe" used for the deployment. And the `k8s_resource` function is used here to forward the cluster port to our local environment, making testing more accessible.

The content of the `auth/kubernetes.yaml` file is:

```yaml
apiVersion: v1
kind: Service
metadata:
  labels:
    app: ms-auth
  name: ms-auth
spec:
  ports:
    - port: 8081
      name: http
      protocol: TCP
      targetPort: 8081
  selector:
    app: ms-auth
---
apiVersion: apps/v1
kind: Deployment
metadata:
  name: ms-auth
  labels:
    app: ms-auth
spec:
  selector:
    matchLabels:
      app: ms-auth
  template:
    metadata:
      labels:
        app: ms-auth
    spec:
      containers:
        - name: ms-auth
          image: auth-image
          ports:
            - containerPort: 8081
```

The other files are practically the same, just changing the name of the binary and the port.

Now Tilt does all the heavy lifting for us:

[![tilt](/images/posts/tilt_ui_k8s.png)](/images/posts/tilt_ui_k8s.png)

To check if our microservices are running on the cluster, we can use the command:

```bash
kubectl get pods -n default
NAME                          READY   STATUS    RESTARTS   AGE
ms-auth-7446897869-89r2j      1/1     Running   0          81s
ms-feedbacks-b5df67d6-wzbj2   1/1     Running   0          81s
ms-votes-76565ddc9c-nkkt7     1/1     Running   0          81s
```

## Conclusions

I don't know if I managed to demonstrate how excited I am about this tool!

I've used Tilt for a few weeks on a complex project, creating a Kubernetes [Controller](https://kubernetes.io/docs/concepts/architecture/controller/). Thanks to all this automation, I can focus on the application logic while the rest is done automatically. And that saves a lot of time.

Thanks to my colleague [Felipe Paes de Oliveira](https://www.linkedin.com/in/felipewebcloud/), who introduced me to this fantastic tool. And if you want to see Tilt being demonstrated by the fabulous [Ellen Korbes](http://ellenkorbes.com), who works at Tilt, check out [this video](https://www.youtube.com/watch?v=9C9BKzyZG_Y).
