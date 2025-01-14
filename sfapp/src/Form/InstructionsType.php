<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class InstructionsType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $instructions = $options['instructions'] ?? [];

        foreach ($instructions as $instruction) {
            $builder->add('instruction_' . $instruction->getId(), CheckboxType::class, [
                'label' => $instruction->getComfortInstruction()->getInstruction(),
                'required' => false,
                'mapped' => false,
            ]);
        }
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => null,
            'instructions' => [],
        ]);
    }
}