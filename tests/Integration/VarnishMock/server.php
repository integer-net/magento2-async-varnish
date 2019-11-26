<?php
declare(strict_types=1);
$autoloadFiles = [__DIR__ . '/../../../vendor/autoload.php', __DIR__ . '/../../../../../autoload.php'];
foreach ($autoloadFiles as $autoloadFile) {
    if (stream_resolve_include_path($autoloadFile)) {
        require $autoloadFile;
        break;
    }
}
$loop = \React\EventLoop\Factory::create();

$server = new \React\Http\Server(function (\Psr\Http\Message\ServerRequestInterface $request) {
    if ($request->getQueryParams()['kill'] ?? false) {
        exit;
    }
    $requestJson = \json_encode(
        [
            'method'  => $request->getMethod(),
            'headers' => $request->getHeaders()
        ]
    );
    \file_put_contents(__DIR__ . '/.requests.log', $requestJson . "\n", FILE_APPEND);

    return new \React\Http\Response(
        200,
        array(
            'Content-Type' => 'text/plain'
        ),
        "OK\n"
    );
});

$socket = new \React\Socket\Server(8082, $loop);
$server->listen($socket);

echo "Started";

$loop->run();
