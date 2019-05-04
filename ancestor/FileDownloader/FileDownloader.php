<?php

namespace Ancestor\FileDownloader;

use Clue\React\Buzz\Browser;
use GuzzleHttp\RequestOptions;
use React\EventLoop\Factory;
use React\EventLoop\LoopInterface;

class FileDownloader implements AsyncFileDownloaderInterface {
    /**
     * @var LoopInterface
     */
    public $loop;

    const MAX_RESPONSE_SIZE = 36700160;

    public function __construct(LoopInterface $loop) {
        $this->loop = $loop;
    }

    /**
     * @param string $url
     * @param $callback
     */
    public function DownloadUrlToStringAsync(string $url,  $callback) {
        $client = new \React\HttpClient\Client($this->loop);
        $file = '';
        $request = $client->request('GET', $url);

        $request->on('response',
            function (\React\HttpClient\Response $response) use (&$file, $callback) {

                $response->on('error', function (\Exception $e) use ($callback, $response) {
                    echo $e->getMessage() . PHP_EOL;
                    $callback(false);
                    $response->close();
                });

                if ($response->getHeaders()['Content-Length'] > self::MAX_RESPONSE_SIZE) {
                    $response->emit('error', [new \Exception('File is too large!')]);
                }

                $response->on('data', function ($chunk) use (&$file, $response) {
                    $file .= $chunk;
                    if (strlen($file) > self::MAX_RESPONSE_SIZE) {
                        $response->emit('error', [new \Exception('File is too large!')]);
                    }
                });

                $response->on('end', function () use (&$file, $callback) {
                    $callback($file);
                });

            });
        $request->end();
    }

}