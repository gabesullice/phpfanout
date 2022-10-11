<?php

$autoload_path = __DIR__ . '/vendor/autoload.php';
global $log_file;
$log_file = __DIR__ . '/smoke-signals/requests.log';

if (!is_dir(dirname($log_file))) {
    mkdir(dirname($log_file), 0777, TRUE);
}

require_once $autoload_path;

use Laminas\Diactoros\ServerRequestFactory;
use Laminas\Diactoros\Response\EmptyResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use function Http\Response\send;

final class Server implements RequestHandlerInterface {

    /**
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     */
    public function handle(ServerRequestInterface $request): ResponseInterface {
        global $log_file;
        $log = fopen($log_file, 'a');
        fwrite($log, $request->getBody() . "\n");
        fclose($log);
        return new EmptyResponse();
    }

}

$server = new Server();
$request = ServerRequestFactory::fromGlobals();
send($server->handle($request));