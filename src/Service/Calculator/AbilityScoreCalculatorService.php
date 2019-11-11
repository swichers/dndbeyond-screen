<?php


namespace App\Service\Calculator;


class AbilityScoreCalculatorService {

  public function calculateModifier(int $stat): int {
    return floor(($stat - 10) / 2);
  }

}
