<?php

namespace GabeSullice\PhpFanout;

use hollodotme\FastCGI\Requests\PostRequest;
use hollodotme\FastCGI\SocketConnections\UnixDomainSocket;
use hollodotme\FastCGI\Client as FcgiClient;

final class FcgiJob {

    protected UnixDomainSocket $connection;

    public function __construct(
        string $socketPath,
    ) {
        $this->client = new FcgiClient();
        $this->connection = new UnixDomainSocket($socketPath);
    }

    public function execute(): void {
        $socketIds = [];
        for ($i = 0; $i < 100; $i++) {
            $content = http_build_query(['itemID' => $i]);
            $request = new PostRequest(dirname(__DIR__) . '/worker.php', $content);
            $socketIds[] = $this->client->sendAsyncRequest($this->connection, $request);
        }
        foreach ($socketIds as $socketId) {
            $this->client->readResponse($socketId, 10000);
        }
    }

}