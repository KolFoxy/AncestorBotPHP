<?php

namespace Ancestor\FileDownloader;

use Ancestor\CommandHandler\CommandHelper;
use React\EventLoop\LoopInterface;
use React\Promise\Deferred;
use React\Promise\Promise;

class FileDownloader implements AsyncFileDownloaderInterface {
    /**
     * @var LoopInterface
     */
    public $loop;

    const MAX_RESPONSE_SIZE = 3355444;

    public function __construct(LoopInterface $loop) {
        $this->loop = $loop;
    }

    /**
     * Calls $callback on finish with the single argument, containing either FALSE or a opened file handle.
     * @param string $url
     * @param callable $callback
     */
    public function DownloadUrlAsync(string $url, $callback) {
        $client = new \React\HttpClient\Client($this->loop);
        $request = $client->request('GET', $url);
        $tempFile = null;
        $fileSize = 0;
        $request->on('response',
            function (\React\HttpClient\Response $response) use ($callback, &$tempFile, &$fileSize) {

                $response->on('error', function (\Exception $e) use ($callback, $response) {
                    echo $e->getMessage() . PHP_EOL;
                    $callback(false);
                    $response->close();
                });

                if ($response->getHeaders()['Content-Length'] > self::MAX_RESPONSE_SIZE) {
                    $response->emit('error', [new \Exception('File is too large!')]);
                }

                $response->on('data', function ($chunk) use (&$tempFile, $response, &$fileSize) {
                    if ($tempFile === null) {
                        $tempFile = tmpfile();
                    }
                    $fileSize += strlen($chunk);
                    if ($fileSize > self::MAX_RESPONSE_SIZE) {
                        fclose($tempFile);
                        $response->emit('error', [new \Exception('File is too large!')]);
                    }
                    fwrite($tempFile, $chunk);

                });

                $response->on('end', function () use (&$tempFile, $callback) {
                    fseek($tempFile, 0);
                    $callback($tempFile);
                    fclose($tempFile);
                });

            });
        $request->end();
    }

    public function getDownloadAsyncImagePromise(string $url): Promise {
        $deferred = new Deferred();
        $callback = function ($file) use ($deferred) {
            $imageFile = CommandHelper::ImageFromFileHandler($file);
            if ($imageFile === false){
                $deferred->reject();
                return;
            }
            $deferred->resolve($imageFile);
        };
        $this->DownloadUrlAsync($url, $callback);
        return $deferred->promise();
    }
}