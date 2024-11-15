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
                'choices' => [ //filter list
                    'Aucun filtre' => 'Aucun',
                    'salles equipées d\'un SA' => 'SallesAvecSa',
                    'salles avec aucun SA' => 'SalleSansSa',
                ],
                'expanded' => false,
                'multiple' => false,
                'data' => 'Aucun', //the default choice
                'attr' => [
                    'onchange' => 'this.form.submit()' //Automatic submit when the Choice has changed
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
