<?php

namespace App\Service;

use App\Service\Calculator\AbilityScoreCalculatorService;
use App\Service\Calculator\ItemAcCalculatorService;

class CharacterCalculatorService {

  protected $dataModifier;

  protected $itemAcCalculator;

  protected $abilityScoreCalculator;

  public function __construct(DataModifierService $dataModifier, ItemAcCalculatorService $itemAcCalculator, AbilityScoreCalculatorService $abilityScoreCalculator) {
    $this->dataModifier = $dataModifier;
    $this->itemAcCalculator = $itemAcCalculator;
    $this->abilityScoreCalculator = $abilityScoreCalculator;
  }

  public function calculateAc(array $character): int {

    $dex_mod = $this->getStatMod($character, 'dex');
    $character_ac = 10 + $dex_mod;
    $armor_ac = 0;
    $shield_ac = 0;

    $item_ac_calculator = $this->itemAcCalculator;

    $equipped_items = array_filter($character['inventory'], function ($item) {
      return !empty($item['equipped']) && !empty($item['definition']);
    });

    $equipped_shields = array_filter($equipped_items, function ($item) use ($item_ac_calculator) {
      return !empty($item['definition']['armorTypeId']) && $item_ac_calculator::ARMOR_TYPE_SHIELD === $item['definition']['armorTypeId'];
    });

    $equipped_armor = array_filter($equipped_items, function ($item) use ($item_ac_calculator) {
      return $item_ac_calculator->isArmorItem($item['definition']) && $item_ac_calculator::ARMOR_TYPE_SHIELD !== $item['definition']['armorTypeId'];
    });

    if (!empty($equipped_shields)) {
      $shield_ac = max(array_map(function ($item) use ($item_ac_calculator, $dex_mod) {
        return $this->itemAcCalculator->calculateItemAc($item['definition'], $dex_mod, FALSE);
      }, $equipped_shields) ?: [0]);
    }

    if (!empty($equipped_armor)) {
      $armor_ac = max(array_map(function ($item) use ($item_ac_calculator, $dex_mod) {
        return $item_ac_calculator->calculateItemAc($item['definition'], $dex_mod, FALSE);
      }, $equipped_armor) ?: [0]);
    }
    else {
      $unarmored_modifiers = $this->dataModifier->getModifiersByType($character['modifiers'], 'set', 'unarmored-armor-class') ?: [];
      $armor_ac = max(array_map(function ($modifier) use ($character, $character_ac) {
        $ac = $character_ac;
        if (!empty($modifier['statId'])) {
          $ac += $this->getStatMod($character, intval($modifier['statId']));
        }

        if (!empty($modifier['value'])) {
          $ac += $modifier['value'];
        }

        return $ac;
      }, $unarmored_modifiers) ?: [0]);
    }

    // Character modifiers will include item modifiers.
    $bonus_ac = $this->dataModifier->getTotalModifierValue($character['modifiers'], 'bonus', 'armor-class');

    return max($character_ac, $armor_ac) + $shield_ac + $bonus_ac ;
  }

  public function getStatMod(array $character, $statName): int {

    $stat_map = [
      1 => 'strength',
      2 => 'dexterity',
      3 => 'constitution',
      4 => 'intelligence',
      5 => 'wisdom',
      6 => 'charisma',
    ];

    if (is_int($statName)) {
      $statName = $stat_map[$statName] ?? '';
    }

    $statName = strtolower($statName);

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

    $stats[$stat_id] += $this->dataModifier->getTotalModifierValue($character['modifiers'] ?? [], 'bonus', $stat_map[$stat_id] . '-score');

    return $this->abilityScoreCalculator->calculateModifier($stats[$stat_id]);
  }

  public function getMaxHp(array $character):int {

    if (!empty($character['overrideHitPoints'])) {
      return $character['overrideHitPoints'];
    }

    $max_hp = 0;
    if (!empty($character['preferences']['hitPointType'])) {
      $bonus_per_level_hp = $this->dataModifier->getTotalModifierValue($character['modifiers'], 'bonus', 'hit-points-per-level');

      $con = $this->getStatMod($character, 'con');

      foreach ($character['classes'] as $class) {
        $hit_die = $class['definition']['hitDice'];
        $adjusted_level = $class['level'];

        if (!empty($class['isStartingClass'])) {
          $max_hp += $hit_die + $con;
          $adjusted_level--;
        }

        $max_hp += (ceil(($hit_die / 2) + 1) + $con) * $adjusted_level + $bonus_per_level_hp * $class['level'];
      }
    }
    else {
      $max_hp = $character['baseHitPoints'] ?? 0;
    }

    $max_hp += $character['bonusHitPoints'] ?? 0;

    return $max_hp;
  }

  public function getPassiveScore(array $character, string $proficiencyName):int {
    switch ($proficiencyName) {
      case 'insight':
      case 'perception':
        $stat_mod = $this->getStatMod($character, 'wis');
        break;
      case 'investigation':
        $stat_mod = $this->getStatMod($character, 'int');
        break;
      default:
        $stat_mod = 0;
    }

    $skill_mod = 0;

    $active_bonuses = $this->dataModifier->getModifiersByType($character['modifiers'], 'proficiency', $proficiencyName);
    if (!empty($active_bonuses)) {
      $skill_mod = $this->getProficiencyBonus($character);
    }

    $passive_bonuses = $this->dataModifier->getModifiersByType($character['modifiers'], 'bonus', 'passive-' . $proficiencyName);
    $skill_mod += array_sum(array_column($passive_bonuses, 'value'));

    return 10 + $stat_mod + $skill_mod;
  }

  public function getXpNeeded(array $character) {
    $xp_per_level = [
      0,
      300,
      900,
      2700,
      6500,
      14000,
      23000,
      34000,
      48000,
      64000,
      85000,
      100000,
      120000,
      140000,
      165000,
      195000,
      225000,
      265000,
      305000,
      355000,
    ];

    $level = 0;
    foreach ($character['classes'] as $class) {
      $level += $class['level'];
    }

    return $xp_per_level[ min($level, count($xp_per_level) - 1) ];
  }

  public function getProficiencyBonus(array $character) {
    $skill_proficiency_by_level = [
      2,
      2,
      2,
      2,
      3,
      3,
      3,
      3,
      4,
      4,
      4,
      4,
      5,
      5,
      5,
      5,
      6,
      6,
      6,
      6,
    ];

    $level = array_sum(array_column($character['classes'], 'level'));
    $level = max(1, min($level, 20));

    return $skill_proficiency_by_level[$level - 1];
  }

}
