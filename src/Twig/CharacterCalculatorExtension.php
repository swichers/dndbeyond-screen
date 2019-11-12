<?php

namespace App\Twig;

use App\Service\CharacterCalculatorService;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Twig\TwigFunction;

class CharacterCalculatorExtension extends AbstractExtension {

  protected $characterCalculatorService;

  public function __construct(
    CharacterCalculatorService $characterCalculatorService
  ) {
    $this->characterCalculatorService = $characterCalculatorService;
  }
  //  public function getFunctions():array {
  //    return [
  //      new TwigFunction('character_ac', [$this, 'calculateCharacterAc']),
  //      new TwigFunction('character_ability_score', [$this, 'calculateCharacterAbilityScore']),
  //      new TwigFunction('character_max_hp', [$this, 'calculateCharacterMaxHp']),
  //    ];
  //  }

  public function getFilters(): array {
    return [
      new TwigFilter('ability_score',
        [$this, 'calculateCharacterAbilityScore']),
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

  public function calculatePassive(array $character, string $passiveName): int {

    switch ($passiveName) {
      case 'insight':
      case 'perception':
        $mod = $this->characterCalculatorService->getStatMod($character, 'wis');
        break;
      case 'investigation':
        $mod = $this->characterCalculatorService->getStatMod($character, 'int');
        break;
      default:
        $mod = 0;
    }

    return 10 + $mod + $this->characterCalculatorService->getProficiencyModifier($character, $passiveName);
  }

  public function calculateXpToLevel(array $character) :int {
    return $this->characterCalculatorService->getXpNeeded($character);
  }
}
