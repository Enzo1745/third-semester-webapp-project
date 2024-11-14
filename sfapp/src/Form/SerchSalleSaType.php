<?php

namespace App\Form;

use App\Entity\Salle;
use App\Repository\SalleRepository;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SerchSalleSaType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('filtre', ChoiceType::class, [
                'choices' => [
                    'Aucun filtre' => 'Aucun',
                    'salles equipées d\'un SA' => 'SallesAvecSa',
                ],
                'expanded' => false,
                'multiple' => false,
                'data' => 'Aucun',
                'attr' => [
                    'onchange' => 'this.form.submit()' // Soumission automatique lors du changement
                ],
                'label' => 'Filtre : ',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => null,
        ]);
    }
}
