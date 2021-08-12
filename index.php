<?php
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Factory\AppFactory;
use Peru\Jne\DniFactory;
use Peru\Sunat\RucFactory;
use Slim\Exception\HttpNotFoundException;

require 'vendor'. DIRECTORY_SEPARATOR .'autoload.php';

$app = AppFactory::create();

$app->options('/{routes:.+}', function ($request, $response, $args) {
    return $response;
});

$app->add(function ($request, $handler) {
    $response = $handler->handle($request);
    return $response
            ->withHeader('Access-Control-Allow-Origin', '*')
            ->withHeader('Access-Control-Allow-Headers', 'X-Requested-With, Content-Type, Accept, Origin, Authorization')
            ->withHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, PATCH, OPTIONS');
});

$app->get('/', function (Request $request, Response $response, $args) {
    $response->getBody()->write("API v1.0 available...!");
    return $response;
});

$app->post('/ruc', function (Request $request, Response $response, $args) {
    $body = json_decode($request->getBody());

    $factory = new RucFactory();
    $cs = $factory->create();

    $company = $cs->get($body->ruc);
    if (!$company) {
        $obj = new \stdClass();
        $obj->message = 'RUC no encontrado';
        $response->getBody()->write(json_encode($obj));
        return $response
              ->withHeader('Content-Type', 'application/json')
              ->withStatus(400);
    }

    $response->getBody()->write(json_encode($company));
    return $response
        ->withHeader('Content-Type', 'application/json')
        ->withStatus(200);
});

$app->post('/dni', function (Request $request, Response $response, $args) {
    $body = json_decode($request->getBody());

    $factory = new DniFactory();
    $cs = $factory->create();

    $person = $cs->get($body->dni);
    if (!$person) {
        $obj = new \stdClass();
        $obj->message = 'DNI no encontrado';
        $response->getBody()->write(json_encode($obj));
        return $response
              ->withHeader('Content-Type', 'application/json')
              ->withStatus(400);
    }

    $response->getBody()->write(json_encode($person));
    return $response
        ->withHeader('Content-Type', 'application/json')
        ->withStatus(200);
});

$app->map(['GET', 'POST', 'PUT', 'DELETE', 'PATCH'], '/{routes:.+}', function ($request, $response) {
    throw new HttpNotFoundException($request);
});

$app->run();