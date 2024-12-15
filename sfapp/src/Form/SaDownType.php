<?php

namespace App\Form;

use App\Entity\Down;
use App\Entity\Sa;
use App\Repository\Model\SAState;
use App\Repository\SaRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;

class SaDownType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('sa', EntityType::class, [
                'class' => Sa::class,
                'choice_label' => 'id',
                'query_builder' => function (SaRepository $er) {
                    return $er->createQueryBuilder('s')
                        ->where('s.state = :state')
                        ->setParameter('state', SAState::Installed);
                },
            ])
            ->add('temperature', CheckboxType::class, [
                'label' => 'Température',
                'required' => false,
            ])
            ->add('humidity', CheckboxType::class, [
                'label' => 'Humidité',
                'required' => false,
            ])
            ->add('CO2', CheckboxType::class, [
                'label' => 'CO2',
                'required' => false,
            ])
            ->add('microcontroller', CheckboxType::class, [
                'label' => 'Microcontrôleur',
                'required' => false,
            ])
            ->add('reason', TextareaType::class, [
                'label' => 'Remarque',
                'required' => false,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Down::class,
        ]);
    }
}
