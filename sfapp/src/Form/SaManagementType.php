<?php

namespace App\Form;

use App\Entity\Sa;
use App\Entity\Room;
use App\Repository\RoomRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use function Sodium\add;

class SaManagementType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('room', EntityType::class, [
                'class' => Room::class,
                'label' => 'Salle',
                'choice_label' => 'roomName',
                'query_builder' => function (RoomRepository $er) { // Query to get room where no sa is associated
                    return $er->createQueryBuilder('s')
                        ->leftJoin('App\Entity\Sa', 'sa', 'WITH', 'sa.room = s.id')
                        ->where('sa.room IS NULL');
                },
            ])
            ->add('save', SubmitType::class, [
                'label' => 'Associer',
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Sa::class,
            'room' => null,
        ]);
    }
}
