<?php

namespace App\Form;

use App\Entity\Room;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Doctrine\ORM\EntityRepository;

class SearchRoomsType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('salle', EntityType::class, [
                'class' => Room::class,
                'choice_label' => 'roomName',
                'placeholder' => 'Sélectionner une salle',
                'label' => 'Choisir une salle :',
                'required' => true,
                'query_builder' => function (EntityRepository $er) { // Selects the room that have at least one working data collector
                    return $er->createQueryBuilder('r')
                        ->innerJoin('r.sa', 's')
                        ->andWhere('s.Temperature IS NOT NULL')
                        ->andWhere('s.Humidity IS NOT NULL')
                        ->andWhere('s.CO2 IS NOT NULL')
                        ->andWhere('s.state = :state')
                        ->setParameter('state', \App\Repository\Model\SAState::Installed);
                },
                'attr' => [
                    'onchange' => 'this.form.submit()',
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
        ]);
    }
}
