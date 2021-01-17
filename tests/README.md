# Testing

Build the docker container

```bash
$ docker-compose build
```

Then from another window run

```bash
$ docker-compose run app bash
```

You need to create `chronos_test` database in both `postgres` and `mysql` which you can access
your database manager on `3306` and `5432`.