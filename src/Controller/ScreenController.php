<?php

namespace App\Controller;

use App\Service\CharacterFetcherService;
use DateTime;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;

class ScreenController extends AbstractController {

  protected $characterFetcher;

  public function __construct(CharacterFetcherService $characterFetcher) {
    $this->characterFetcher = $characterFetcher;
  }

  /**
   * @Route(
   *   "/{characterId}",
   *   methods={"GET"},
   *   name="campaign_by_character",
   *   requirements={"characterId"="\d+"}
   * )
   *
   * @param int $characterId
   *
   * @return \Symfony\Component\HttpFoundation\Response
   * @throws \App\Exception\MissingCharacterException
   * @throws \App\Exception\PrivateCharacterException
   * @throws \Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface
   */
  public function campaignByCharacter(int $characterId) {
    $character = $this->characterFetcher->get($characterId);

    $characters = [$character];

    foreach ($character['campaign']['characters'] as $campaign_character) {
      if ($campaign_character['characterId'] != $characterId) {
        try {
          $characters[] = $this->characterFetcher->get($campaign_character['characterId']);
        }
        catch (ClientExceptionInterface $x) {
          continue;
        }
      }
    }

    return $this->render('sheet/sheet-cards.html.twig', [
      'characters' => $characters,
      'campaign' => $character['campaign'],
    ]);
  }

  /**
   * @Route(
   *   "/{characterId}/update",
   *   methods={"GET"},
   *   name="sheet_update_character",
   *   requirements={"characterId"="\d+"}
   * )
   *
   * @param int $characterId
   */
  public function updateCharacter(int $characterId) {
    $character = $this->characterFetcher->get($characterId);

    $response = new Response($this->renderView('sheet/character-card.html.twig', ['character' => $character]), 200, [
      'X-Character-Id' => $characterId,
    ]);
    $response->setMaxAge(0);
    $response->setLastModified(new DateTime());
    $response->headers->addCacheControlDirective('no-cache', true);

    return $response;
  }

}
