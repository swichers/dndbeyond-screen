<?php


namespace App\Service;


use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\HttpKernel\HttpCache\Store;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class DndBeyondClientFactory {

  public static function create(string $cachePath, int $defaultTtl = 300): HttpClientInterface {

    $cache_path = sprintf('%s/http',
      $cachePath ?: sys_get_temp_dir() . '/dndbeyond-dm');

    $store = new Store($cache_path);
    $http_client = HttpClient::create([
      'headers' => [
        'Content-Type' => 'text/json',
        'User-Agent'   => 'Mozilla/5.0 (Windows NT 6.1; Win64; x64; rv:47.0) Gecko/20100101 Firefox/47.0',
        'Referer'      => 'https://www.dndbeyond.com/',
      ],
    ]);
    $client = new DndBeyondClientService($http_client, $store, ['default_ttl' => $defaultTtl]);

    return $client;
  }

}
