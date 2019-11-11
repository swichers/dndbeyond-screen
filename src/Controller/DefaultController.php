<?php

namespace App\Controller;

use App\Form\CharacterIdRequestType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class DefaultController extends AbstractController {

  /**
   * @Route(
   *   "/",
   *   methods={"GET", "POST"},
   *   name="default"
   * )
   */
  public function index(Request $request) {

    $form = $this->createForm(CharacterIdRequestType::class, NULL, [
      'method' => 'GET',
    ]);

    $form->handleRequest($request);
    if ($form->isSubmitted() && $form->isValid()) {
      $characterId = $form->getData()['characterId'] ?? 0;

      return $this->redirectToRoute('campaign_by_character',
        ['characterId' => $characterId]);
    }

    return $this->render('form/request_character_id.html.twig', [
      'form' => $form->createView(),
    ]);
  }

}
