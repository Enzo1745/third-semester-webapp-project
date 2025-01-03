<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class FilterTrierRoomsType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('filter', ChoiceType::class, [
                'choices' => [
                    'Aucun filtre'    => 'none',
                    'Salles avec SA'  => 'withSA',
                    'Salles sans SA'  => 'withoutSA',

                ],
                'expanded' => false,
                'multiple' => false,
                'data' => 'none',
                'attr' => [
                    'onchange' => 'this.form.submit()',
                ],
                'label' => 'Filtre : ',
            ])
            ->add('trier', ChoiceType::class, [
                'choices' => [
                    'Diagnostic bon d\'abord'  => 'DiaGood',
                    'Diagnostic pas bon d\'abord' => 'DiaBad',
                ],
                'data' => 'Tri par nom',
                'placeholder' => 'trie par nom',
                'required'    => false,
                'mapped'      => false,
                'attr'        => [
                    'onchange' => 'this.form.submit()'
                ],
                'label' => 'Trier : '
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            // pas de data_class, ou null
            'data_class' => null,
        ]);
    }
}
