<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class FilterTrier extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('filter', ChoiceType::class, [
                'choices' => [
                    'Aucun filtre' => 'none',
                    'SA en panne' => 'SADown',
                    'SA en attente' => 'SAWaiting',
                    'SA installer ' => 'SAInstall',
                    'SA disponible' => 'SAAvailable',
                ],
                'expanded' => false,
                'multiple' => false,
                'data' => 'none', // Default choice
                'attr' => [
                    'onchange' => 'this.form.submit()', // Automatically submit the form on change
                ],
                'label' => 'Filtre : ',
            ])
            ->add('trier', ChoiceType::class, [
                'choices' => [
                    'Association' => 'Asso',
                    'Diagnostic'  => 'Dia'
                ],
                'data' => 'Asso',
                'required'    => false,
                'placeholder' => 'Aucun tri',
                'mapped'      => false,
                'attr'        => [
                    'onchange' => 'this.form.submit()'
                ],
            ]);


    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            // No data class binding here
        ]);
    }
}

