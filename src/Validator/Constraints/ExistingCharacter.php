<?php

namespace App\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class ExistingCharacter extends Constraint {

  public $message = 'Could not find a public character with the ID "{{ id }}".';

}
