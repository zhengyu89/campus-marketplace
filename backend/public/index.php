<?php

declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use App\Core\Application\Exception\ApiException;
use App\Core\Application\Middleware\CorsMiddleware;
use App\Core\Application\Responder\JsonResponder;
use Dotenv\Dotenv;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Factory\AppFactory;

$dotenv = Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->safeLoad();

$app = AppFactory::create();

$app->addRoutingMiddleware();
$errorMiddleware = $app->addErrorMiddleware(true, true, true);
$errorMiddleware->setDefaultErrorHandler(
    static function (
        ServerRequestInterface $request,
        \Throwable $exception,
        bool $displayErrorDetails,
        bool $logErrors,
        bool $logErrorDetails
    ) use ($app): ResponseInterface {
        $status = 500;
        $message = 'An unexpected error occurred';
        $errors = [];

        if ($exception instanceof ApiException) {
            $status = $exception->getStatusCode();
            $message = $exception->getMessage();
            $errors = $exception->getErrors();
        } elseif ($exception instanceof \PDOException) {
            $message = 'Database connection failed';
        }

        $response = $app->getResponseFactory()->createResponse($status);

        return JsonResponder::error($response, $message, $status, $errors);
    }
);
$app->addBodyParsingMiddleware();
$app->add(new CorsMiddleware());

(require __DIR__ . '/../config/routes.php')($app);

$app->run();
