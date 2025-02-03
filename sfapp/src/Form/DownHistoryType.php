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

/**
 * @brief the form used to filter the results in he SA history
 */
class DownHistoryType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('filtrer', EntityType::class, [
                'class' => Sa::class,
                'label' => 'SA',
                'choice_label' => 'name',
                'placeholder' => 'Filter par un SA',
                'attr' => [
                    'class' => 'form-select',
                    'onchange' => 'this.form.submit()',
                ],
            ])
            ->add('dateBeg', DateType::class, [
                'widget' => 'single_text',
                'input'  => 'datetime',
                'required' => true,
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Choisissez une date',
                    'onchange' => 'this.form.submit()',
                ],
            ])
            ->add('dateEnd', DateType::class, [
                'widget' => 'single_text',
                'input'  => 'datetime',
                'required' => true,
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Choisissez une date',
                    'onchange' => 'this.form.submit()',
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([]);
    }
}
