<?php

namespace Ancestor\ImageDownloader;

interface ImageDownloaderInterface {

    /**
     * Returns either a resource image or FALSE if image is unavailable or unsupported.
     * @param string $url
     * @return bool|resource
     */
    public function GetImageFromURL(string $url);
}