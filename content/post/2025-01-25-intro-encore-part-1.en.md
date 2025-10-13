---
title: Getting Started with Encore.go
date: 2025-01-25T07:10:00-03:00
draft: false
tags:
  - go
---

[Encore.go](https://encore.dev/go) has been on my radar for quite some time, when its beautiful website and examples caught my attention in a news article on Hacker News. But my excitement really increased after [this post](https://encore.dev/blog/open-source-decoupled) was published in December 2024. It announced that the framework would become an independent project, separated from the Encore Cloud tool.
This decision can make the framework more attractive to companies and developers who want to use it in their existing environments. I have nothing against [Encore Cloud](https://encore.cloud), which seems to be a very interesting and robust solution, but this freedom of choice favors adoption in companies of different sizes.

Given this initial context, I decided to port a project that I use to write texts and lectures about microservices to Encore, and the result is a series of posts, this being the first. The initial idea is to divide the series as follows:


1. Creating an API with a database (<— you are here)
2. Creating an API with authentication
3. Communication via Pub/Sub
4. Deploy

I may create new posts while writing the following parts, but the initial plan is defined. So, let's go to the first part.

## Creating an API with a database

To use Encore, we need to install its CLI, which we will use throughout the development cycle. Since I use macOS, I installed it using the command:

	brew install encoredev/tap/encore

In the [documentation](https://encore.dev/docs/ts/install), you can see the other possible installation methods.


With the CLI installed, we can use it to create the project:

{{< youtube FeCORBTdn6I >}}

Since I chose the option to create a project from scratch, the created directory only contains the files with the dependencies  (`go.mod` and `go.sum`) and `encore.app`, which will be used by the CLI to manipulate the project. Its initial content is straightforward:


```json
{
	// The app is not currently linked to the encore.dev platform.
	// Use "encore app link" to link it.
	"id": "",
}
```

For now, we won't need to change anything in `encore.app`, so let's create the structure of the first microservice:


```bash
❯ cd post-encore/
❯ mkdir user
❯ touch user/api.go
```

In addition to creating the directory, we initialize a file called `api.go`, where we will define our API. The content of the first version was as follows:

```go
package user

import (
	"context"
)

// API defines the API for the user service
// encore: service
type API struct{}

func initAPI() (*API, error) {
	return &API{}, nil
}

// AuthParams are the parameters to the Auth method
type AuthParams struct {
	Email    string `json:"email"`
	Password string `json:"password"`
}

// AuthResponse is the response to the Auth method
type AuthResponse struct {
	Token string `json:"token"`
}

// Auth authenticates a user and returns a token
//
//encore:api public method=POST path=/v1/auth
func (a *API) Auth(ctx context.Context, p *AuthParams) (*AuthResponse, error) {
	var response AuthResponse
	return &response, nil
}

// ValidateTokenParams are the parameters to the ValidateToken method
type ValidateTokenParams struct {
	Token string `json:"token"`
}

// ValidateTokenResponse is the response to the ValidateToken method
type ValidateTokenResponse struct {
	Email string `json:"email"`
}

// ValidateToken validates a token
//
//encore:api public method=POST path=/v1/validate-token
func (a *API) ValidateToken(ctx context.Context, p *ValidateTokenParams) (*ValidateTokenResponse, error) {
	response := ValidateTokenResponse{}
	return &response, nil
}

```

Encore extensively uses the concept of `annotations` to define the application's behavior and generate the code necessary for execution. The first example of this is the creation of a `service`:


```go
// API defines the API for the user service
// encore: service
type API struct{}

```

Another common feature of frameworks like Encore is the existence of conventions. The first one we will find here is the initialization of services. Since we have defined a service called `API`, we can create a function called `initAPI` that will be invoked by the framework. In this function, we will inject the service dependencies, as we will do later in the project.

The following annotation we see in the code is the one that defines an API, as in the example:

```go
// Auth authenticates a user and returns a token
//
//encore:api public method=POST path=/v1/auth
func (a *API) Auth(ctx context.Context, p *AuthParams) (*AuthResponse, error) {
	var response AuthResponse
	return &response, nil
}
```

This declaration tells the framework that this is a public API (we'll see more about this in the following parts of this series) whose path is `/v1/auth` and that it will be accessed via the `POST` method.
An API is a function that receives a context and a parameter struct (in this case, `AuthParams`) and returns a response struct (in this case, `AuthResponse`).

One of the interesting features of the framework is the ease of accessing the parameter values: we can use the values ​​as `p.Email`, which is part of the received struct, without needing to convert the received JSON. More details about parameter handling and examples can be found in the official [documentation](https://encore.dev/docs/go/primitives/defining-apis).

We can now run the project using the CLI:

```bash
❯ encore run
  ✔ Building Encore application graph... Done!
  ✔ Analyzing service topology... Done!
  ✔ Generating boilerplate code... Done!
  ✔ Compiling application source code... Done!
  ✔ Starting Encore application... Done!

  Encore development server running!

  Your API is running at:     http://127.0.0.1:4000
  Development Dashboard URL:  http://127.0.0.1:9400/wst7a

  New Encore release available: v1.46.1 (you have v1.45.6)
  Update with: encore version update

11:07AM INF registered API endpoint endpoint=Auth path=/v1/auth service=user
11:07AM INF registered API endpoint endpoint=ValidateToken path=/v1/validate-token service=user
11:07AM INF listening for incoming HTTP requests

```

We can see that the API is available at the URL `http://127.0.0.1:4000` and the development dashboard, which is one of the biggest attractions of the framework. How it works can be seen below:

{{< youtube RbB1xfXjDfM >}}

In the video, you can see the interesting features available, such as testing the API and viewing traces and application components. The dashboard will become even more useful as we add layers of complexity to the project.

### Setting up the database

The next step in developing the application is to define a database. We need to have Docker installed and running to do this since Encore will use it to create the database image. At the time of writing, the available database is PostgreSQL. In the `api.go`, we will make the following changes:


```go
package user

import (
	"context"

	"encore.dev/storage/sqldb"
)

var db = sqldb.NewDatabase("user", sqldb.DatabaseConfig{
	Migrations: "./migrations",
})
```

As you can see in the excerpt, Encore uses the concept of `migrations`, which is very useful. It was necessary to create the directory `user/migrations` and the file `user/migrations/1_create_tables.up.sql` with the following content:


```sql
create table users (id varchar(50) PRIMARY KEY ,email varchar(255),password varchar(255),first_name varchar(100), last_name varchar(100), created_at date, updated_at date);
INSERT INTO users (id, email, password, first_name, last_name, created_at, updated_at) values ('8cb2237d0679ca88db6464eac60da96345513964','eminetto@email.com','8cb2237d0679ca88db6464eac60da96345513964', 'Elton', 'Minetto', now(), null);

```

The database is created in the file, and a record is also inserted for use in tests via the dashboard. More details about migrations can be seen in the [documentation](https://encore.dev/docs/go/primitives/databases#database-migrations).

Another interesting feature of the command `encore run` is that it auto-reloads the application. Whenever something is changed in one of the project files, the application is recompiled and executed again, so your database should have been created successfully.

To finalize this first version of the project, I created other files to complement the functionality. Using a framework like Encore makes repetitive tasks much more straightforward, such as configuring routes, converting parameters and responses, etc. However, it does not eliminate the need to use good development practices, such as abstractions, decoupling, etc. With this in mind, I created other files that are important for our project:

- [user.go](https://github.com/eminetto/post-encore/blob/main/user/user.go), which defines what a "user" in the application is
- [service.go](https://github.com/eminetto/post-encore/blob/main/user/service.go), which contains the application's business rule and will be used by the API
- [security/jwt.go](https://github.com/eminetto/post-encore/blob/main/user/security/jwt.go), which includes the logic for generating and validating JWT tokens

With these auxiliary files, the final version of our API looks like this:

```go
package user

import (
	"context"

	"encore.app/user/security"
	"encore.dev/beta/errs"
	"encore.dev/storage/sqldb"
)

var db = sqldb.NewDatabase("user", sqldb.DatabaseConfig{
	Migrations: "./migrations",
})

// API defines the API for the user service
// encore: service
type API struct {
	Service UseCase
}

func initAPI() (*API, error) {
	return &API{Service: NewService(db)}, nil
}

// AuthParams are the parameters to the Auth method
type AuthParams struct {
	Email    string `json:"email"`
	Password string `json:"password"`
}

// AuthResponse is the response to the Auth method
type AuthResponse struct {
	Token string `json:"token"`
}

// Auth authenticates a user and returns a token
//
//encore:api public method=POST path=/v1/auth
func (a *API) Auth(ctx context.Context, p *AuthParams) (*AuthResponse, error) {
	// Construct a new error builder with errs.B()
	eb := errs.B().Meta("auth", p.Email)

	err := a.Service.ValidateUser(ctx, p.Email, p.Password)
	if err != nil {
		return nil, eb.Code(errs.Unauthenticated).Msg("invalid credentials").Err()
	}
	var response AuthResponse
	response.Token, err = security.NewToken(p.Email)
	if err != nil {
		return nil, eb.Code(errs.Internal).Msg("internal error").Err()
	}
	return &response, nil
}

// ValidateTokenParams are the parameters to the ValidateToken method
type ValidateTokenParams struct {
	Token string `json:"token"`
}

// ValidateTokenResponse is the response to the ValidateToken method
type ValidateTokenResponse struct {
	Email string `json:"email"`
}

// ValidateToken validates a token
//
//encore:api public method=POST path=/v1/validate-token
func (a *API) ValidateToken(ctx context.Context, p *ValidateTokenParams) (*ValidateTokenResponse, error) {
	// Construct a new error builder with errs.B()
	eb := errs.B().Meta("validate_token", p.Token)
	t, err := security.ParseToken(p.Token)
	if err != nil {
		return nil, eb.Code(errs.Internal).Msg("internal error").Err()
	}
	tData, err := security.GetClaims(t)
	if err != nil {
		return nil, eb.Code(errs.Internal).Msg("internal error").Err()
	}
	response := ValidateTokenResponse{
		Email: tData["email"].(string),
	}
	return &response, nil
}

```

In this new version, you can see that we initialize the service (in the function `initAPI`) with the injection of the business rule and the error handling provided by the framework.


### Tests

Another advantage of using a framework like Encore is that it provides features that help with the critical task of writing tests. In this first version, we have two crucial components to test:

**service_test.go**

```go
package user_test

import (
	"context"
	"testing"

	"encore.app/user"
	"encore.dev/et"
)

func TestService(t *testing.T) {
	ctx := context.Background()
	et.EnableServiceInstanceIsolation()
	testDB, err := et.NewTestDatabase(ctx, "user")
	if err != nil {
		t.Fatalf("unexpected error: %v", err)
	}

	s := user.NewService(testDB)
	t.Run("valid user", func(t *testing.T) {
		err := s.ValidateUser(ctx, "eminetto@email.com", "12345")
		if err != nil {
			t.Fatalf("unexpected error: %v", err)
		}
	})
	t.Run("invalid user", func(t *testing.T) {
		err := s.ValidateUser(ctx, "e@email.com", "12345")
		if err == nil {
			t.Fatalf("unexpected error: %v", err)
		}
	})
	t.Run("invalid password", func(t *testing.T) {
		err := s.ValidateUser(ctx, "eminetto@email.com", "111")
		if err == nil {
			t.Fatalf("unexpected error: %v", err)
		}
	})
}

```

The highlight is the use of the package `encore.dev/et`, which provides a way to ensure that tests can be executed in parallel (`et.EnableServiceInstanceIsolation()`) and the ease of using an exclusive database for tests (`testDB, err:= et.NewTestDatabase(ctx, "user")`).

The interesting thing is that migrations are used automatically, making the test easier to write and execute.

**api_test.go**

```go
package user_test

import (
	"context"
	"testing"

	"encore.app/user"
)

type ServiceMock struct{}

func (s *ServiceMock) ValidateUser(ctx context.Context, email string, password string) error {
	return nil
}

func (s *ServiceMock) ValidatePassword(ctx context.Context, u *user.User, password string) error {
	return nil
}

func TestIntegration(t *testing.T) {
	api := &user.API{
		Service: &ServiceMock{},
	}
	email := "eminetto@email.com"
	resp, err := api.Auth(context.Background(), &user.AuthParams{
		Email:    email,
		Password: "12345",
	})
	if err != nil {
		t.Fatalf("unexpected error: %v", err)
	}
	if resp.Token == "" {
		t.Fatalf("expected token to be non-empty")
	}
	r, err := api.ValidateToken(context.Background(), &user.ValidateTokenParams{
		Token: resp.Token,
	})
	if err != nil {
		t.Fatalf("unexpected error: %v", err)
	}
	if r.Email != email {
		t.Fatalf("expected email to be %q, got %q", email, r.Email)
	}
}

```

In this test, nothing from the framework was necessary; just good old Go, with its native advantages, was used.

An important detail: to run the tests, you must use the CLI. Therefore, instead of running them using the command:

	go test ./...

You must use:

	encore test ./...

There is a plugin for Goland that allows execution directly through the IDE, but the same is not yet true for VSCode, as can be seen in the official testing [documentation](https://encore.dev/docs/go/develop/testing).


## Conclusion

The objective of this first post was to present the basics of the framework and spark your curiosity about the next chapters in this series.

I'll leave my opinions on the framework for the last part of the series so I can provide more arguments to say whether I liked the experience or not. But I can say that I'm having a lot of fun with the first steps. What about you, dear reader? What do you think of Encore so far? Leave your impressions in the comments.
