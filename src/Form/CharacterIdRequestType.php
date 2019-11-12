<?php

namespace App\Form;

use App\Validator\Constraints\ExistingCharacter;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Positive;

class CharacterIdRequestType extends AbstractType {

  public function buildForm(FormBuilderInterface $builder, array $options) {
    $builder
      ->add('characterId', IntegerType::class, [
        'required'    => TRUE,
        'label'       => 'Enter a character ID',
        'constraints' => [
          new NotBlank(),
          new Positive(),
          new ExistingCharacter(),
        ],
      ])
      ->add('submit', SubmitType::class, [
        'label' => 'Load character campaign',
      ]);
  }

}
