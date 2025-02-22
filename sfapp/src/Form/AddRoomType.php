<?php

namespace App\Form;

use App\Entity\Room;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @brief the form used to add rooms
 */
class AddRoomType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('roomName', TextType::class, [
                'label' => 'Nom de la salle',
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Entrez le nom de la salle',
                ],
            ])
            ->add('nbRadiator', IntegerType::class, [
                'label' => 'Nombre de radiateurs',
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Entrez le nombre de radiateurs',
                    'min' => 0,
                ],
            ])
            ->add('nbWindows', IntegerType::class, [
                'label' => 'Nombre de fenêtres',
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Entrez le nombre de fenêtres',
                    'min' => 0,
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Room::class,
        ]);
    }
}
