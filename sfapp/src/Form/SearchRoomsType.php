<?php

namespace App\Form;

use App\Entity\Room;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

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
