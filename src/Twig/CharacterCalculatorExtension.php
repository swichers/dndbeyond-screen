<?php

namespace App\Twig;

use App\Service\Calculator\CharacterCalculatorService;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class CharacterCalculatorExtension extends AbstractExtension {

  protected $characterCalculatorService;

  public function __construct(CharacterCalculatorService $characterCalculatorService) {
    $this->characterCalculatorService = $characterCalculatorService;
  }

  public function getFilters(): array {
    return [
      new TwigFilter('ability_score', [$this, 'calculateCharacterAbilityScore']),
      new TwigFilter('ac', [$this, 'calculateCharacterAc']),
      new TwigFilter('max_hp', [$this, 'calculateCharacterMaxHp']),
      new TwigFilter('passive', [$this, 'calculatePassive']),
      new TwigFilter('xp_to_level', [$this, 'calculateXpToLevel']),
    ];
  }

  public function calculateCharacterAc(array $character): int {
    return $this->characterCalculatorService->calculateAc($character);
  }

  public function calculateCharacterAbilityScore(array $character, string $statName): int {
    return $this->characterCalculatorService->getStatMod($character, $statName);
  }

  public function calculateCharacterMaxHp(array $character): int {
    return $this->characterCalculatorService->getMaxHp($character);
  }

  public function calculatePassive(array $character, string $proficiencyName): int {
    return $this->characterCalculatorService->getPassiveScore($character, $proficiencyName);
  }

  public function calculateXpToLevel(array $character) :int {
    return $this->characterCalculatorService->getXpNeeded($character);
  }
}
