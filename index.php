<?php

declare(strict_types = 1);

header('Content-type: application/json');

$config = require_once './config.php';

spl_autoload_register(function ($class) {

    $dirs = [
        __DIR__ . '/src/',
        __DIR__ . '/src/controllers/',
        __DIR__ . '/src/repositories/'
    ];

    foreach ($dirs as $dir) {

        if (file_exists($dir . "$class.php")) {

            require_once($dir . "$class.php");

            return true;
        }
    }
});

$errorHandler = new ErrorHandler($config['error_log_path'], true);

// Для автоматической обработки неожиданных, технических ошибок 
set_exception_handler([$errorHandler, 'handleException']);
set_error_handler([$errorHandler, 'handleError']);

$database = new Database($config, $errorHandler);

$bookRepository = new BookRepository($database);

$bookController = new BookController($bookRepository);

$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

$router = new Router();

// Books

$router->add("/books", function() use ($bookController) {
    $bookController->processRequest($_SERVER['REQUEST_METHOD'], null);
});

$router->add("/books/{id}", function($id) use ($bookController) {
    $bookController->processRequest($_SERVER['REQUEST_METHOD'], $id);
});

$router->dispatch($path);