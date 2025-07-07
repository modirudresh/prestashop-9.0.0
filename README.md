# About PrestaShop
--------

[![PHP checks and unit tests](https://github.com/PrestaShop/PrestaShop/actions/workflows/php.yml/badge.svg)](https://github.com/PrestaShop/PrestaShop/actions/workflows/php.yml)
[![Integration tests](https://github.com/PrestaShop/PrestaShop/actions/workflows/integration.yml/badge.svg)](https://github.com/PrestaShop/PrestaShop/actions/workflows/integration.yml)
[![UI tests](https://github.com/PrestaShop/PrestaShop/actions/workflows/sanity.yml/badge.svg)](https://github.com/PrestaShop/PrestaShop/actions/workflows/sanity.yml)
[![Nightly Status](https://img.shields.io/endpoint?url=https%3A%2F%2Fapi-nightly.prestashop-project.org%2Fdata%2Fbadge&label=Nightly%20Status&cacheSeconds=3600)](https://nightly.prestashop-project.org/)
[![Minimum PHP Version](https://img.shields.io/badge/php-%3E%3D%208.1-8892BF.svg?style=flat-square)](https://php.net/)
[![GitHub release](https://img.shields.io/github/v/release/prestashop/prestashop)](https://github.com/PrestaShop/PrestaShop/releases)
[![Slack chat](https://img.shields.io/badge/Chat-on%20Slack-red)](https://www.prestashop-project.org/slack/)
[![GitHub forks](https://img.shields.io/github/forks/PrestaShop/PrestaShop)](https://github.com/PrestaShop/PrestaShop/network)
[![GitHub stars](https://img.shields.io/github/stars/PrestaShop/PrestaShop)](https://github.com/PrestaShop/PrestaShop/stargazers)

PrestaShop is an Open Source e-commerce web application, committed to providing the best shopping cart experience for merchants and customers. It is written in PHP, highly customizable, supports all major payment services, is translated into many languages, localized for many countries, and has a fully responsive design for both front and back office.  
[See all the available features][available-features].

<p align="center"> <img src="https://user-images.githubusercontent.com/2137763/201319765-9157f702-4970-4258-8390-1187de2ad587.png" alt="PrestaShop 9.0 back office" width="600"/> </p>

This repository contains the source code of PrestaShop intended for development and preview only. To download the latest stable public version (currently PrestaShop 8.1), please visit [the releases page][download].

---

## About the `develop` branch
The `develop` branch contains work-in-progress code for the next major version: PrestaShop 9.0.

For more details on branch usage and installing PrestaShop for development, see the [installing PrestaShop for development guide][install-guide-dev].

---

## Server Configuration
To install PrestaShop 9.0, you need:

- PHP 8.1 or higher
- MySQL 5.6+ (or compatible like MariaDB, Percona Server)
- Apache or Nginx (see [example Nginx config][example-nginx])
- Database tool like phpMyAdmin recommended

More info on [System Requirements][system-requirements] and [System Administrator Guide][sysadmin-guide].

---

## Installation
If you cloned the repo, read [installing PrestaShop for development][install-guide-dev]. For production, download from [our releases page][download] and follow the [user installation guide][install-guide].

---

## Docker Compose
PrestaShop supports Docker with Docker Compose.

Run:

```bash
docker compose up
