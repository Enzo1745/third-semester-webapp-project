<?php

namespace App\Form;

use App\Entity\Room;
use App\Repository\RoomRepository;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SerchRoomASType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('filter', ChoiceType::class, [
                'choices' => [ //filter list
                    'Aucun filtre' => 'none',
                    'salles equipées d\'un SA' => 'RoomsWithAS',
                    'salles avec aucun SA' => 'RoomsWithoutAS',
                ],
                'expanded' => false,
                'multiple' => false,
                'data' => 'none', //the default choice
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
