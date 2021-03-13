# zbMATH dataset harvester

* Downloads all of zbMATHs data

## build

```
docker build -t physikerwelt/zbharvest .
docker run --env-file myEnv physikerwelt/zbharvest
```

Specify
[environment variables](https://docs.docker.com/engine/reference/commandline/run/#set-environment-variables--e---env---env-file)
using the following keys.
```
zbMATHUser=user
zbMATHPassword=secret
zbMATHUrl=https://tb.d/v1
```
