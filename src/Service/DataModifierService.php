<?php


namespace App\Service;


class DataModifierService {

  public function getModifiersByType(array $groupedModifiers, string $type, string $subType = NULL) : array {
    $matching = [];

    foreach ($groupedModifiers as $group => $modifiers) {

      $filtered = array_filter($modifiers, function ($item) use ($type) {
        return !empty($item['type']) && $type === $item['type'];
      });

      if (!empty($subType)) {
        $filtered = array_filter($filtered, function ($item) use ($subType) {
          return !empty($item['subType']) && $subType === $item['subType'];
        });
      }

      $matching = array_merge($matching, $filtered ?: []);
    }

    return $matching;
  }

  public function getTotalModifierValue(array $groupedModifiers, string $type, string $subType):int {
    $modifiers = $this->getModifiersByType($groupedModifiers, $type, $subType);
    $modifiers = array_column($modifiers, 'value');
    return array_sum($modifiers);
  }

}
