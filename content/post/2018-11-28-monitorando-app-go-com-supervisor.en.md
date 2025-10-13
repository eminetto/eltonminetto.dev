---
title: "Monitoring a Golang application with Supervisor"
subtitle: ""
date: "2018-11-28T10:54:24+02:00"
bigimg: ""
tags:
  - go
---

Dear reader… If you are reading this post a few years after his publication date you must understand that in 2018 we were very excited about things like micro services, Docker, Kubernetes and related technologies.

<!--more-->

So, our first reaction when thinking about “application deployment” is put all this magic together and run our API, or a hand full of micro services, in a complex environment with Kubernetes, Istio, and so on. I’ve ‘Been there, done that’…

But, in moments like these, we can always count on internet wisdom:

[![XhK8RJv](/images/posts/XhK8RJv.jpg)](/images/posts/XhK8RJv.jpg)

In this post, I will present a simple solution that I believe will fit for a lot of projects out there: Supervisor. According to the Supervisor’s site:

> Supervisor is a client/server system that allows its users to monitor and control a number of processes on UNIX-like operating systems.

The first time I used Supervisor was in 2008, to monitor some queue consumer workers, developed in PHP. This can prove two facts:

1. I’m getting old;
2. Supervisor is a battle tested tool.

Let’s jump to the example.

## Installation

In this post, I used a Linux box with Ubuntu 18.04, but in the Supervisor’s site, we can find instructions regarding the installation process in other distros.

I executed:

    sudo apt-get update
    sudo apt-get install -y supervisor
    sudo service supervisor start

We can use the following command to check the status of Supervisor:

    sudo supervisorctl status

We don’t have any service been monitored, so the command result is empty.

Let’s create an API in Go (version 1.11) to use as our service. I created a _main.go_ with:

[![main](/images/posts/main.png)](/images/posts/main.png)

We can now generate the binary with the command:

    sudo go build -o /usr/local/bin/api main.go

The next step is to configure Supervisor to manage our API. We need to create a config file in:

    sudo vim /etc/supervisor/conf.d/api.conf

With the content:

    [program:api]
    directory=/usr/local
    command=/usr/local/bin/api
    autostart=true
    autorestart=true
    stderr_logfile=/var/log/api.err
    stdout_logfile=/var/log/api.log
    environment=CODENATION_ENV=prod

We need to create a file like this for each process that Supervisor will manage. In this file, we define the name of our process (_[program:api]_), the command that will be executed (_command=/usr/local/bin/api_), if Supervisor should restart the service if any kind of error occurs (_autorestart=true_) and the log destination (_stderr_logfile_ and _stdout_logfile_). We can configure environment variables that will be used by the process (environment) and other options that can be found in the documentation.

Now we need to tell Supervisor to reload the configuration files, including the one we just created:

    ubuntu@7648e3e0ef2b:~ sudo supervisorctl reload
    Restarted supervisord

And let’s check the process status:

    root@759cc81a91f0:~ sudo supervisorctl status
    api            RUNNING   pid 3032, uptime 0:00:03

As we can see, the process is alive and running.

Let’s change our API to write some access logs :

[![main_stdout](/images/posts/main_stdout.png)](/images/posts/main_stdout.png)

To update our service we need to execute:

    sudo supervisorctl stop api
    sudo go build -o /usr/local/bin/api main.go
    sudo supervisorctl start api

After a few access, we can see that the logs are been stored in the file /_var/log/api.log_, as configured in /_etc/supervisor/conf.d/api.conf_:

```
cat /var/log/api.log
2018/11/28 23:22:12 main.go:28: 127.0.0.1:42282 GET /
2018/11/28 23:22:13 main.go:28: 127.0.0.1:42284 GET /
2018/11/28 23:22:14 main.go:28: 127.0.0.1:42286 GET /
2018/11/28 23:22:14 main.go:28: 127.0.0.1:42288 GET /
2018/11/28 23:22:14 main.go:28: 127.0.0.1:42290 GET /
2018/11/28 23:22:15 main.go:28: 127.0.0.1:42292 GET /
2018/11/28 23:22:17 main.go:28: 127.0.0.1:42294 GET /
```

As a final test, let’s change our API again, this time to emulate an error:

[![main_stderr](/images/posts/main_stderr.png)](/images/posts/main_stderr.png)

Updating the service again:

    sudo go build -o /usr/local/bin/api main.go
    sudo supervisorctl restart api

Let’s access the error generation URL:

    root@759cc81a91f0:~ curl http://localhost:8080/erro
    curl: (52) Empty reply from server

As we can see, the error log was generated in the expected place:

    root@759cc81a91f0:~ cat /var/log/api.err
    ERROR 2018/11/28 23:42:29 main.go:29: Something wrong happened

As Supervisor is monitoring our process, we know that the API is running again, what we can prove with the command:

    root@759cc81a91f0:~ supervisorctl status
    api          RUNNING   pid 3857, uptime 0:00:22

With Supervisor you can count on a simple infrastructure to manage your services, at least until the project became more complex and you need to move to something like Kubernetes.
