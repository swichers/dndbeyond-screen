<?php

namespace App\Service\Calculator;

use App\Service\DataModifierService;

class ItemAcCalculatorService {

  const ARMOR_TYPE_LIGHT = 1;

  const ARMOR_TYPE_MEDIUM = 2;

  const ARMOR_TYPE_HEAVY = 3;

  const ARMOR_TYPE_SHIELD = 4;

  protected $dataModifier;

  public function __construct(DataModifierService $dataModifier) {
    $this->dataModifier = $dataModifier;
  }

  public function isArmorItem(array $itemDefinition) {
    return 'Armor' === ($itemDefinition['filterType'] ?? NULL);
  }

  /**
   * @param array $itemDefinition
   * @param int $dexMod
   *
   * @return int
   *
   * https://merricb.com/2014/09/13/armour-class-in-dungeons-dragons-5e/
   */
  public function calculateItemAc(array $itemDefinition, int $dexMod = 0, bool $includeItemMods = TRUE): int {

    $item_ac = $itemDefinition['armorClass'] ?? 0;

    switch($itemDefinition['armorTypeId'] ?? 0) {
      case self::ARMOR_TYPE_LIGHT:
        $item_ac += $dexMod;
        break;
      case self::ARMOR_TYPE_MEDIUM:
        $item_ac += max($dexMod, 2);
        break;
      case self::ARMOR_TYPE_HEAVY:
      case self::ARMOR_TYPE_SHIELD:
      default:
        break;
    }

    if ($includeItemMods && !empty($itemDefinition['grantedModifiers'])) {
      $item_ac += $this->dataModifier->getTotalModifierValue(['item' => $itemDefinition['grantedModifiers']], 'bonus', 'armor-class');
    }

    return $item_ac;
  }

}
