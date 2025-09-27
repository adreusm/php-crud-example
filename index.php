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

$parts = explode("/", $_SERVER['REQUEST_URI']);

if ($parts[1] != "books") {
    http_response_code(404);
    exit;
}

$bookId = $parts[2] ?? null;

$bookRepository = new BookRepository($database);

$bookController = new BookController($bookRepository);

$bookController->processRequest($_SERVER['REQUEST_METHOD'], $bookId);