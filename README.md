# router
# Routing system for PHP
## Requries PHP >= 7.0
## Installation
- `composer require alineisi/router`
## To get started
- `require_once __DIR__ . '/vendor/autoload.php';`
- `use Alineisi\Route;`
- be sure putting `Route::dispatch();` end of index.php
## Usage
### ex.1
- `Route::get('/', function () {`
- `return 'Hello World';`
- `});`
### ex.2
- `Route::get('/', [HomeController::class, 'index']);`
- ### ex.3
- `Route::post('/submit', function () {`
- `return $_POST['name'];`
- `});`
## Features
### you can set not found page by using 
- `Route::notFoundPath($path);`
### set named routes by using name method after routing, for example:
- ``` Route::get('/', function () { ```
- ```   return 'Hello World'; ```
- ``` })->name('index'); ```
#### and to use it:
- `\Alineisi\routeName('index');`
### you can also returning views by using
- `Route::view('index');`
#### or
- `Route::view('index.php');`
