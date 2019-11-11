<?php

namespace App\Service;

use App\Service\Calculator\AbilityScoreCalculatorService;
use App\Service\Calculator\ItemAcCalculatorService;

class CharacterCalculatorService {

  protected $itemAcCalculator;

  protected $abilityScoreCalculator;

  public function __construct(
    ItemAcCalculatorService $itemAcCalculator,
    AbilityScoreCalculatorService $abilityScoreCalculator
  ) {
    $this->itemAcCalculator = $itemAcCalculator;
    $this->abilityScoreCalculator = $abilityScoreCalculator;
  }

  public function calculateAc(array $character): int {

    $dex_mod = $this->getStatMod($character, 'dex');
    $character_ac = 10 + $dex_mod;
    $armor_ac = 0;
    $shield_ac = 0;

    foreach ($character['inventory'] as $item) {
      if (empty($item['equipped']) || empty($item['definition'])) {
        continue;
      }

      $definition = $item['definition'];

      if (!empty($definition['baseItemId']) && $this->itemAcCalculator::ARMOR_SHIELD == $definition['baseItemId']) {
        $shield_ac = max($shield_ac,
          $this->itemAcCalculator->calculateItemAc($definition, $dex_mod));
      }
      elseif ($this->itemAcCalculator->isArmorItem($definition)) {
        $armor_ac = max($armor_ac,
          $this->itemAcCalculator->calculateItemAc($definition, $dex_mod));
      }
    }

    return max($character_ac, $armor_ac) + $shield_ac;
  }

  public function getStatMod(array $character, string $statName): int {

    $statName = strtolower($statName);

    $stat_map = [
      1 => 'strength',
      2 => 'dexterity',
      3 => 'constitution',
      4 => 'intelligence',
      5 => 'wisdom',
      6 => 'charisma',
    ];

    $stat_id = FALSE;
    foreach ($stat_map as $dndbeyond_id => $allowed_stat) {
      if ($statName === $allowed_stat || substr($allowed_stat, 0, 3) === $statName) {
        $stat_id = $dndbeyond_id;
        break;
      }
    }

    if ($stat_id === FALSE) {
      return 0;
    }

    $stats = array_column($character['overrideStats'], 'value', 'id');
    if (empty($stats[$stat_id])) {
      $stats = array_column($character['stats'], 'value', 'id');
    }

    // $stats = array_column($character['bonusStats'], 'value', 'id');

    if (empty($stats[$stat_id])) {
      return 0;
    }

    $stats[$stat_id] += $this->getTotalModifierValue($character['modifiers'] ?? [], 'bonus', $stat_map[$stat_id] . '-score');

    return $this->abilityScoreCalculator->calculateModifier($stats[$stat_id]);
  }

  public function getMaxHp(array $character):int {

    if (!empty($character['overrideHitPoints'])) {
      return $character['overrideHitPoints'];
    }

    $con = $this->getStatMod($character, 'con');
    $max_hp = 0;
    if (!empty($character['preferences']['hitPointType'])) {

      foreach ($character['classes'] as $class) {
        $rounded_hp = ceil(($class['definition']['hitDice'] / 2) + 1);
        $max_hp += $class['definition']['hitDice'] + ($con * $class['level']) + ($rounded_hp * ($class['level'] - 1));
      }

    }
    else {
      $max_hp = $character['baseHitPoints'] ?? 0;
    }

    $max_hp += $character['bonusHitPoints'] ?? 0;

    return $max_hp;
  }

  protected function getTotalModifierValue(array $characterModifiers, string $modifierType, string $modifierSubType):int {
    $modifiers = $this->getModifiersByType($characterModifiers, $modifierType, $modifierSubType);
    $modifiers = array_column($modifiers, 'value');
    return array_sum($modifiers);
  }

  protected function getModifiersByType(array $characterModifiers, string $modifierType, string $modifierSubType = NULL) : array {

    $matching = [];

    foreach ($characterModifiers as $group => $modifiers) {
      foreach ($modifiers as $modifier) {
        if (empty($modifier['type']) || empty($modifier['subType'])) {
          continue;
        }
        elseif ($modifier['type'] !== $modifierType) {
          continue;
        }
        elseif (!is_null($modifierSubType) && $modifier['subType'] !== $modifierSubType) {
          continue;
        }

        $matching[] = $modifier;
      }
    }

    return $matching;
  }

  public function getProficiencyModifier(array $character, string $proficiencyName):int {

    $modifiers = $this->getModifiersByType($character['modifiers'] ?? [], 'proficiency', $proficiencyName);
    return !empty($modifiers) ? 2 : 0;
  }


}
