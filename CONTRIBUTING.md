# Contributing to the HiPay Enterprise module for PrestaShop - 1.7.6 - 8.x

> :warning: This repository is a mirror of a private repository for this plugin, so we are not able to merge your PRs directly in GitHub. Any open PRs will be added to the main repository and closed in GitHub. Any contributor will be credited in the plugin's changelog.

Contributions to the HiPay Enterprise module for PrestaShop - 1.7.6 - 8.x should be made via GitHub [pull requests][pull-requests] and discussed using GitHub [issues][issues].

## Before you start

If you would like to make a significant change, please open
an issue to discuss it, in order to minimize duplication of effort.

### Install

Installation with Docker for testing

If you are a developer or a QA developer, you can use this project with Docker and Docker Compose.
Requirements for your environment:

- Git (<https://git-scm.com/>)
- Docker (<https://docs.docker.com/engine/installation/>)
- Docker Compose (<https://docs.docker.com/compose/>)

Here is the procedure to be applied to a Linux environment:

Open a terminal and select the folder of your choice.

Clone the HiPay Enterprise PrestaShop project in your environment with Git:

```sh
git clone https://github.com/hipay/hipay-enterprise-sdk-prestashop.git
```

Copy the content from the file `bin/docker/conf/development/env.sample` and paste it in `bin/docker/conf/development/env` file.
Then, fill it with your personal vars.

Go in the project root folder and enter this command:

```sh
./prestashop.sh init
```

Your container is loading: wait for a few seconds while Docker installs PrestaShop and the HiPay module.*

You can now test the HiPay Enterprise module in a browser with this URL:

- <http://localhost:8086> (PRESTASHOP 16)
- <http://localhost:8087> (PRESTASHOP 17)
- <http://localhost:8088> (PRESTASHOP 8)

To connect to the back office, go to this URL:

- <http://localhost:8086/admin-hipay> (PRESTASHOP 16)
- <http://localhost:8087/admin-hipay> (PRESTASHOP 17)
- <http://localhost:8088/admin-hipay> (PRESTASHOP 8)

The login and password are <demo@hipay.com> / hipay123.
You can test the module with your account configuration.

### Debug

If you want to debug locally our CMS module, here are the steps :

- Verify the value of `XDEBUG_CONFIG.client_host` in your `env` file you have copied in last step.
  - For Linux users, it should be `172.17.0.1` (value by default)
  - For MacOS users, replace it by `host.docker.internal`
- Then, create a Xdebug launch according to your IDE (here is for VSCode) :

  ```json
  {
    "name": "Prestashop",
    "type": "php",
    "request": "launch",
    "hostname": "172.17.0.1", // Only for Linux users
    "port": 9003,
    "pathMappings": {
        "/var/www/html/modules/hipay_enterprise": "${workspaceFolder}/src/hipay_enterprise",
        "/var/www/html": "${workspaceFolder}/web8",
        // "/var/www/html": "${workspaceFolder}/web17" --> If you start a Prestashop 1.7, uncomment it and comment the web8 line
    }
  }
  ```

### Making the request

Development takes place against the `develop` branch of this repository and pull requests should be opened against that branch.

## Licensing

The HiPay Enterprise module for PrestaShop 1.7.6 - 8.x is released under an [Apache 2.0][project-license] license. Any code you submit will be released under that license.

[project-license]: LICENSE.md

[pull-requests]: https://github.com/hipay/hipay-enterprise-sdk-prestashop/pulls

[issues]: https://github.com/hipay/hipay-enterprise-sdk-prestashop/issues
