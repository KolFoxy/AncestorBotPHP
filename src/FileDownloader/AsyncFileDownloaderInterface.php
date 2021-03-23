<?php

namespace Ancestor\FileDownloader;

use React\Promise\Promise;

interface AsyncFileDownloaderInterface {


    /**
     * Download url to string asynchronously.
     * @param string $url
     * @param $callback
     */
    public function DownloadUrlAsync(string $url, $callback);

    public function getDownloadAsyncImagePromise(string $url): Promise;
}