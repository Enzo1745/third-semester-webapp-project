<?php

namespace App\Form;

use App\Entity\Sa;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\BirthdayType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\WeekType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class DownHistoryType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('filtrer', EntityType::class, [
                'class' => Sa::class,
                'label' => 'SA',
                'choice_label' => 'id',
                'placeholder' => 'Filter par un SA',
                'attr' => [
                    'onchange' => 'this.form.submit()',
                ],
            ])
            ->add('dateBeg', DateType::class, [
                'widget' => 'single_text', // Utilisation d'un seul champ pour la date
                'input'  => 'datetime',  // Format d'entrée
                'required' => true, // Date obligatoire
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Choisissez une date', // Texte d’aide
                    'onchange' => 'this.form.submit()',
                ],
            ])
            ->add('dateEnd', DateType::class, [
                'widget' => 'single_text', // Utilisation d'un seul champ pour la date
                'input'  => 'datetime',  // Format d'entrée
                'required' => true, // Date obligatoire
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Choisissez une date', // Texte d’aide
                    'onchange' => 'this.form.submit()',
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            // Configure your form options here
        ]);
    }
}
