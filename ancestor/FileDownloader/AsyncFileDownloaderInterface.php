<?php

namespace Ancestor\FileDownloader;

interface AsyncFileDownloaderInterface {


    /**
     * Download url to string asynchronously.
     * @param string $url
     * @param $callback
     */
    public function DownloadUrlToStringAsync(string $url,  $callback);
}