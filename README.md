Vault parameter resolver
========================

[Vault by HashiCorp](https://www.vaultproject.io/) parameter resolver. A tool for managing secrets.

This application will resolve your vault parameters in files.
`%vault(secret/mynamespace#myfield)%` will be replaced by the content of this command: `vault read -field=myfield secret/mynamespace`

## Installation

`wget --no-check-certificate https://github.com/inextensodigital/vault-parameter-resolver/raw/master/vault-parameter-resolver.phar`

**or**

`curl -O -sL https://github.com/inextensodigital/vault-parameter-resolver/raw/master/vault-parameter-resolver.phar`

**via composer**

`composer global require "inextensodigital/vault-parameter-resolver=~1.0"`

VaultParameterResolver binary path: `~/.composer/vendor/bin/vault-parameter-resolver`

## Commands

```sh
$ ./vault-parameter-resolver.phar resolve -f myfile.yml -f myfile2.txt
# You can specify a configuration file
$ ./vault-parameter-resolver.phar resolve -f myfile.yml -f myfile2.txt -c /path/to/my-config-file.yml
# You can enter value when they are not exist.
$ ./vault-parameter-resolver.phar resolve -f myfile.yml -f myfile2.txt --ask-if-not-found
```

## Configuration

If you don't define configuration, it'll use env variables `VAULT_TOKEN` and `VAULT_ADDR`.

You can use other backends via configuration file:

```yaml
vault:
    host:  "http://127.0.0.1:8200"
    auth:
      app_role:
          role_id:   "%env(VAULT_ROLE_ID)%"
          secret_id: "%env(VAULT_SECRET_ID)%"
```

Backend auth supporteds:

- app_role
- ... please contribute.
