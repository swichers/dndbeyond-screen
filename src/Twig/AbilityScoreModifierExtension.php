<?php

namespace App\Twig;

use App\Service\Calculator\AbilityScoreCalculatorService;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class AbilityScoreModifierExtension extends AbstractExtension {

  protected $abilityScoreCalculator;
  public function __construct(AbilityScoreCalculatorService $abilityScoreCalculator) {
    $this->abilityScoreCalculator = $abilityScoreCalculator;
  }

  public function getFilters() {
    return [
      new TwigFilter('ability_mod', [$this, 'calculateMod']),
    ];
  }

  public function calculateMod(int $rawStat) {
    return $this->abilityScoreCalculator->calculateModifier($rawStat);
  }

}
