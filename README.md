# Scripd [![Build Status](https://travis-ci.org/Samshal/Scripd.svg?branch=master)](https://travis-ci.org/Samshal/Scripd) [![Latest Stable Version](https://poser.pugx.org/samshal/scripd/v/stable)](https://packagist.org/packages/samshal/scripd) [![Total Downloads](https://poser.pugx.org/samshal/scripd/downloads)](https://packagist.org/packages/samshal/scripd) [![Latest Unstable Version](https://poser.pugx.org/samshal/scripd/v/unstable)](https://packagist.org/packages/samshal/scripd) [![License](https://poser.pugx.org/samshal/scripd/license)](https://packagist.org/packages/samshal/scripd) [![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/Samshal/Scripd/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/Samshal/Scripd/?branch=master) [![StyleCI](https://styleci.io/repos/56755520/shield)](https://styleci.io/repos/56755520)
A robust SQL Generator. Parses database structures defined in json based on a custom jsyn file format and generates corresponding sql queries.

## Class Features

- A json like file format to define database structure
- Support for multiple sql vendors / dialects
- Compatible with PHP 5.0+
- Support for UTF-8 content and 8bit, base64, binary, and quoted-printable encodings
- Much More!

## Why you might need it

This project was birthed as a result of the need to give users an opportunity to `create` their own custom database structure in a PHP Application.
I wanted users to be able to modify database structures while there is support for multiple db vendors such as mysql, sqlite and sql server.

This library offers the ability to create database structures in a json-like format and generate sql compatible with several database vendors.

It is also very easy to use and integrate into your php based projects

## License

This software is distributed under the [MIT](https://opensource.org/licenses/MIT) license. Please read LICENSE for information on the
software availability and distribution.

## Installation & loading
Scripd is available via [Composer/Packagist](https://packagist.org/packages/samshal/scripd), so just add this line to your `composer.json` file:

```json
"samshal/scripd": "~1.0"
```

or

```sh
composer require samshal/scripd
```

## A Simple Example

#### JSON DB Structure (structure.json)
```json
{
	":database":{
		":crud-action":"create",
		"name":"dbname",

		":table":[
			{
				":crud-action":"create",
				"name":"students",
				"columns":[
					{
						"name":"id",
						"data-type":"int",
						"primary-key":true
					},
					{
						"name":"first_name",
						"data-type":"varchar(20)",
						"default":"'samuel'"
					},
					{
						"name":"last_name",
						"data-type":"varchar(20)"
					},
					{
						"name":"class",
						"data-type":"varchar(10)"
					}
				]
			}
		]
	}
}
```

#### PHP (index.php)
```php
<?php
    require 'vendor/autoload.php';

    $jsonDBStructure = new Samshal\Scripd\JsonDbStructure('./structure.json', 'mysql');

    $jsonDBStructure->parseStructure();

    $sql = $jsonDBStructure->getGeneratedSql();
    
    echo $sql;
```
