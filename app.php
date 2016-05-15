<?php

use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use \Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;


require_once __DIR__ . '/vendor/autoload.php';

$log_q = new Monolog\Logger('req');
$log_q->pushProcessor(new \Monolog\Processor\PsrLogMessageProcessor());

$log_s = new Monolog\Logger('resp');
$log_s->pushProcessor(new \Monolog\Processor\PsrLogMessageProcessor());

$log_q->pushHandler(new StreamHandler('log.log', Logger::DEBUG));
$log_s->pushHandler(new StreamHandler('log.log', Logger::DEBUG));

$app = new \Silex\Application();
$app['debug'] = true;
$app['mitm_remote'] = trim(getenv('mitm_remote'));
$app['log_request'] = $log_q;
$app['log_response'] = $log_s;

$app->match('/{dummy}', function (Application $app, Request $req) {

    $app['log_request']->info("{method}: {uri}\n{content}", [
        'uri' => $req->getRequestUri(),
        'method' => $req->getMethod(),
        'content' => $req->getContent()
    ]);

    $client = new GuzzleHttp\Client([
        'base_uri' => @$app['mitm_remote'] ?: 'http://127.0.0.1/',
        GuzzleHttp\RequestOptions::HTTP_ERRORS => false,
    ]);

    $resp = $client->request($req->getMethod(), $req->getRequestUri(), ['body' => $req->getContent()]);

    $app['log_response']->info("{status}", [
        'status' => $resp->getStatusCode(),
        'headers' => $resp->getHeaders(),
        'body' => $resp->getBody()->getContents(),
    ]);

    return new Response($resp->getBody(), $resp->getStatusCode(), $resp->getHeaders());
})->assert('dummy', '.*');

$app->run();
