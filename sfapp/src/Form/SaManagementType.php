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

/**
 * @brief form used by the charge to link SA and rooms
 */
class SaManagementType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add(
                'room', EntityType::class, [
                'class' => Room::class,
                'label' => 'Salle',
                'choice_label' => 'roomName',
                'attr' => [
                    'class' => 'form-control mb-3'
                ],
                'label_attr' => [
                    'class' => 'form-label'
                ],
                'query_builder' => function (RoomRepository $er) {
                    return $er->createQueryBuilder('s')
                        ->leftJoin('App\Entity\Sa', 'sa', 'WITH', 'sa.room = s.id')
                        ->where('sa.room IS NULL');
                },
            ])
            ->add('save', SubmitType::class, [
                'label' => 'Associer',
                'attr' => [
                    'class' => 'btn btn-primary w-100',
                    'icon' => 'fas fa-link'  // Cette classe sera utilisée pour l'icône
                ],
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