<?php

namespace App\Service\Calculator;

class ItemAcCalculatorService {

  const ARMOR_PADDED = 17;

  const ARMOR_LEATHER = 10;

  const ARMOR_STUDDED_LEATHER = 3;

  const ARMOR_HIDE = 11;

  const ARMOR_CHAIN_SHIRT = 12;

  const ARMOR_SCALE_MAIL = 6;

  const ARMOR_BREASTPLATE = 13;

  const ARMOR_HALF_PLATE = 14;

  const ARMOR_RING_MAIL = 15;

  const ARMOR_CHAIN_MAIL = 16;

  const ARMOR_SPLINT = 17;

  const ARMOR_PLATE = 18;

  const ARMOR_SHIELD = 8;

  public function isArmorItem(array $itemDefinition) {
    return in_array($itemDefinition['baseItemId'] ?? 0, [
      self::ARMOR_PADDED,
      self::ARMOR_LEATHER,
      self::ARMOR_STUDDED_LEATHER,
      self::ARMOR_HIDE,
      self::ARMOR_CHAIN_SHIRT,
      self::ARMOR_SCALE_MAIL,
      self::ARMOR_BREASTPLATE,
      self::ARMOR_HALF_PLATE,
      self::ARMOR_RING_MAIL,
      self::ARMOR_CHAIN_MAIL,
      self::ARMOR_SPLINT,
      self::ARMOR_PLATE,
      self::ARMOR_SHIELD,
    ]);
  }

  /**
   * @param array $itemDefinition
   * @param int $dexMod
   *
   * @return int
   *
   * https://merricb.com/2014/09/13/armour-class-in-dungeons-dragons-5e/
   */
  public function calculateItemAc(array $itemDefinition, int $dexMod = 0): int {
    if (empty($itemDefinition['armorClass']) || empty($itemDefinition['baseItemId'])) {
      return 0;
    }

    $armor_ac = $itemDefinition['armorClass'];

    if (in_array($itemDefinition['baseItemId'],
      [self::ARMOR_PADDED, self::ARMOR_LEATHER, self::ARMOR_STUDDED_LEATHER])
    ) {
      $armor_ac = $itemDefinition['armorClass'] + $dexMod;
    }
    elseif (in_array($itemDefinition['baseItemId'], [
      self::ARMOR_HIDE,
      self::ARMOR_CHAIN_SHIRT,
      self::ARMOR_SCALE_MAIL,
      self::ARMOR_BREASTPLATE,
      self::ARMOR_HALF_PLATE,
    ])
    ) {
      $armor_ac = $itemDefinition['armorClass'] + max($dexMod, 2);
    }
    elseif (in_array($itemDefinition['baseItemId'], [
      self::ARMOR_RING_MAIL,
      self::ARMOR_CHAIN_MAIL,
      self::ARMOR_SPLINT,
      self::ARMOR_PLATE,
    ])
    ) {
      $armor_ac = $itemDefinition['armorClass'];
    }
    elseif ($itemDefinition['baseItemId'] == self::ARMOR_SHIELD) {
      $armor_ac = $itemDefinition['armorClass'];
    }

    if (!empty($itemDefinition['grantedModifiers'])) {
      $granted_modifiers = array_filter($itemDefinition['grantedModifiers'],
        function ($modifier) {
          return $modifier['type'] == 'bonus'
            && $modifier['subType'] == 'armor-class';
        });
      $granted_modifiers = array_column($granted_modifiers, 'value');

      $armor_ac += array_sum($granted_modifiers);
    }

    return $armor_ac;
  }

}
