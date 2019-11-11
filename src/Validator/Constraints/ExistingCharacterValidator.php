<?php
// src/Validator/Constraints/ContainsAlphanumericValidator.php
namespace App\Validator\Constraints;

use App\Service\CharacterFetcherService;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Exception\UnexpectedValueException;

class ExistingCharacterValidator extends ConstraintValidator {

  protected $characterFetcher;

  public function __construct(CharacterFetcherService $characterFetcher) {
    $this->characterFetcher = $characterFetcher;
  }

  public function validate($value, Constraint $constraint) {
    if (!$constraint instanceof ExistingCharacter) {
      throw new UnexpectedTypeException($constraint,
        ExistingCharacter::class);
    }

    // custom constraints should ignore null and empty values to allow
    // other constraints (NotBlank, NotNull, etc.) take care of that
    if (NULL === $value || '' === $value) {
      return;
    }

    if (!is_integer($value)) {
      // throw this exception if your validator cannot handle the passed type so that it can be marked as invalid
      throw new UnexpectedValueException($value, 'int');
    }

    if (!$this->characterFetcher->isValidId($value)) {
      $this->context->buildViolation($constraint->message)
        ->setParameter('{{ id }}', $value)
        ->addViolation();
    }
  }

}
