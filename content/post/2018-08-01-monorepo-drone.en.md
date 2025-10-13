+++
title = "Continuous integration in projects using monorepo"
subtitle = ""
date = "2018-08-01T10:54:24+02:00"
bigimg = ""
+++

At the beginning of every project, we have to commit to some important decisions. Among the correct decisions we made at [Codenation](https://www.codenation.dev/) I can cite the use of Go language, the adoption of [Clean Architecture](/en/post/2018-03-05-clean-architecture-using-go/) and JAMStack and our choice to store the code in a monorepo at Github. In this post, I will write about the latter, and how we solved a common challenge that the monorepo architecture brings.

<!--more-->

With the increasing complexity of modern projects, with microservices and distinct user interfaces consuming resources, teams need to decide between splitting the code into multiple repositories or using a monorepo approach. Some companies like Google and DigitalOcean adopted the monorepo, as we can see in the following posts:

- [Why Google Stores Billions of Lines of Code in a Single Repository](https://cacm.acm.org/magazines/2016/7/204032-why-google-stores-billions-of-lines-of-code-in-a-single-repository/fulltext)
- [Cthulhu: Organizing Go Code in a Scalable Repo](https://blog.digitalocean.com/cthulhu-organizing-go-code-in-a-scalable-repo/)

In my humble opinion, the main reasons to use a monorepo are the simplicity of repository management and the reuse of code across the team.

But one of the challenges that this choice brings to the project is the potential complexity of automated build and deploy. Considering that all the code of a complex project is stored in the same repository, a change in a single file could trigger the build that can last minutes (or maybe hours) to be done. And this can be a real pain to the team, slowing the day to day productivity. In the DigitalOcean’s post that I cited before, they developed an internal solution, called gta (Go Test Auto), that is not open source. To solve this problem we created a similar solution but using Shell Script.

Currently, this is our project’s directory structure:


```bash
api = API and documentation 
chatbots = telegram, facebook and slack chatbots 
cli = codenation cli, used by developers to run the challenges 
cmd = utils and fixtures 
core = Go core packages, used by all the project 
docs = source code of internal docs (hosted at Github Pages) frontend = Vue.js project and templates used by Sam 
infra = configuration files used by staging and production servers lambda = lambda functions 
research = Python notebooks and other research assets 
sam = cli tool used by us to generate pages, include challenges and other admin tasks 
scripts = shell scrips used by CI/CD and other admin tasks 
web = ReactJS project (Signin, Signup, Forgot password) - IN PROCESS OF DEPRECATION 
workers = workers that consume SQS queues 
.drone.yml = CI/CD configuration file 
.goreleaser.yml = Goreleaser configuration file. Used to deploy the codenation-cli to Github, Homebrew 
docker-compose.yml = Docker configuration used by local and staging environments 
Gopkg.* = Go dependencies configuration files 
Makefile = build and admin tasks
```

We are using [Drone.io](http://drone.io/) as our CI/CD solution, that can we consider another good choice that we made. Our build pipeline can be seen in the image below:

[![pipeline](/images/posts/drone_pipeline.png)](/images/posts/drone_pipeline.png) 

And this is a sample of our .drone.yml config file:

[![config](/images/posts/drone_config.png)](/images/posts/drone_config.png) 

As you can see, the step golang-build-api executes the script **drone_go_build_api.sh**, that is:

```bash
#!/bin/bash -e
watch="api core"
. scripts/shouldIBuild.sh
shouldIBuild
if [[ $SHOULD_BUILD = 0 ]]; then
    exit 0
fi
make linux-binaries-api
BUILD_EXIT_STATUS=$?
exit $BUILD_EXIT_STATUS
```

In the variable watch we store the list of directories that need to be monitored to our pipeline decide if the build needs to be run. This decision is made by the script shouldIBuild.sh:

```bash
#!/bin/bash -e
SHOULD_BUILD=0
shouldIBuild() {
    if [[ "${DRONE_DEPLOY_TO}" ]]; then 
        SHOULD_BUILD=1
    else
        . scripts/detectChangedFolders.sh
        detect_changed_folders
        toW=($(echo "$watch" | tr ' ' '\n'))
        changed=($(echo "$changed_components"))
        for i in "${toW[@]}"
        do
            for j in "${changed[@]}"
            do
                if [[ $i = $j ]]; then
                SHOULD_BUILD=1 
                fi
            done
        done
    fi
}
```

In this script, the first thing being tested is the variable DRONE_DEPLOY_TO, that defines if the current execution is a deploy or a build. If so, the step should run. Otherwise, the script will check if one of the directories listed in the “watch” variable has been altered by this commit. If so, the step should run. The code of detectChangedFolders.sh is:

```bash
#!/bin/bash -e
export IGNORE_FILES=$(ls -p | grep -v /)

detect_changed_folders() {
    if [[ "${DRONE_PULL_REQUEST}" ]]; then 
        folders=$(git --no-pager diff --name-only FETCH_HEAD FETCH_HEAD~1 | sort -u | awk 'BEGIN {FS="/"} {print $1}' | uniq); 
    else 
        folders=$(git --no-pager diff --name-only HEAD~1 | sort -u | awk 'BEGIN {FS="/"} {print $1}' | uniq); 
    fi
    export changed_components=$folders
}
```

The same configuration exists in all our scripts used by Drone. That way, a change in the frontend won’t trigger the build of the API or the chatbots part of the code. Using this approach we decrease our build time from more than five minutes to a few seconds, depending on the change being commited in the repository.

I believe that this approach can be used with other tools different than Drone and I hope this can help more teams to stick to the monorepo decision with confidence.