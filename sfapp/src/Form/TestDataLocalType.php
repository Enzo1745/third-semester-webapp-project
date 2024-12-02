<?php

namespace App\Form;

use App\Entity\Sa;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;


class TestDataLocalType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('sa', ChoiceType::class, [
                'choices' => $options['sa_ids'],
                'multiple' => false, // ou true si vous voulez permettre la sélection multiple
                'expanded' => false, // ou true si vous voulez des boutons radio ou des cases à cocher
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'sa_ids' => [],
        ]);
    }
}
