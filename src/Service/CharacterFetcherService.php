<?php

namespace App\Service;

use App\Exception\MissingCharacterException;
use App\Exception\PrivateCharacterException;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;

class CharacterFetcherService {

  protected $baseApiUrl = 'https://www.dndbeyond.com/character/';

  protected $httpClient;

  public function __construct(DndBeyondClientService $httpClient) {
    $this->httpClient = $httpClient;
  }

  public function isValidId(int $characterId): bool {
    $response = $this->httpClient->request('HEAD', $characterId . '/json', [
      'base_uri' => $this->baseApiUrl,
    ]);

    return $response->getStatusCode() === 200;
  }

  public function get(int $characterId) {
    try {
      $response = $this->httpClient->request('GET', $characterId . '/json', [
        'base_uri' => $this->baseApiUrl,
      ]);
    } catch (ClientExceptionInterface $x) {
      if (403 === $x->getResponse()->getStatusCode()) {
        throw new PrivateCharacterException(sprintf('Character %d is not accessible.', $characterId));
      }
      elseif (404 === $x->getResponse()->getStatusCode()) {
        throw new MissingCharacterException(sprintf('No character exists with ID %d', $characterId));
      }

      throw $x;
    }

    return $response->toArray();
  }

}
