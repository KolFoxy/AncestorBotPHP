<?php

namespace Ancestor\ImageDownloader;

use GuzzleHttp\RequestOptions;

class ImageDownloader implements ImageDownloaderInterface {
    /**
     * @var \GuzzleHttp\Client
     */
    private $client = null;

    const MAX_RESPONSE_SIZE = 36700160;

    /**
     * @param \GuzzleHttp\ClientInterface $client
     * @return void
     */
    public function SetClient(\GuzzleHttp\ClientInterface $client) {
        $this->client = $client;
    }

    /**
     * @return \GuzzleHttp\ClientInterface
     */
    public function GetClient(): \GuzzleHttp\ClientInterface {
        if ($this->client === null) {
            $this->client = $this->GetDefaultClient();
        }
        return $this->client;
    }

    private function GetDefaultClient(): \GuzzleHttp\ClientInterface {
        return new \GuzzleHttp\Client([
            'connect_timeout' => 8,
            'decode_content' => false,
            'read_timeout' => 8,
            'stream' => true,
            'timeout' => 10
        ]);
    }


    public function GetImageFromURL(string $url) {
        if (!$file = $this->DownloadUrlToString($url)) {
            return false;
        }
        return imagecreatefromstring($file);
    }

    public function DownloadUrlToString(string $url) {
        $client = $this->GetClient();
        try {
            $response = $client->request('GET', $url, [RequestOptions::VERIFY => false]);
        } catch (\GuzzleHttp\Exception\GuzzleException $e) {
            echo $e->getMessage() . PHP_EOL;
            return false;
        }
        
        $body = $response->getBody();
        $file = '';
        $blockSize = 128;
        while (!$body->eof()) {
            $file .= $body->read($blockSize);
            if (strlen($file) > self::MAX_RESPONSE_SIZE) {
                echo 'File is too big!';
                return false;
            }
        }
        return $file;
    }

}