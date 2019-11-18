# D&D Beyond DM Screen

This project provides a DM screen for D&D Beyond <https://dndbeyond.com>. This screen brings all of a character's public campaign members into a single page with DM-relevant stats. The idea here is to reduce the number of tabs a DM may need to open.

![DM Screen](https://user-images.githubusercontent.com/5890607/69019100-fc728f80-0963-11ea-9305-9f603e261585.jpg)

**!! Important: D&D Beyond does not currently provide an official API. The functionality for this screen is based upon using endpoints that may change or go away at any point. Additionally, D&D Beyond implements rate limiting for accessing these endpoints. Accessing too many characters too frequently may cause a temporary block which requires you to complete a captcha on the main D&D Beyond website.**

## Requirements

* PHP 7.2 or greater
* Composer <https://getcomposer.org/>
* Symfony CLI <https://symfony.com/download> -OR- an existing webserver
  * If using Symfony CLI, you must have version 4.10.1 or higher

## Installation

```sh
git clone git@github.com:swichers/dndbeyond-screen.git
cd dndbeyond-screen
composer install
symfony serve
```

If not using the Symfony CLI then you must configure your webserver to point to the project folder. After the site is accessible, open it in your browser.

## Usage

Open `http://127.0.0.1:8000` in your browser. Enter the Character ID for a public D&D Beyond character. You will be presented with a list of all characters in the campaign. The Character ID is the number available when viewing a character on D&D Beyond.
