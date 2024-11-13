<?php

namespace App\Form;

use App\Entity\Sa;
use App\Entity\Salle;
use App\Repository\SalleRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use function Sodium\add;

class GestionSaType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('salle', EntityType::class, [
                'class' => Salle::class,
                'choice_label' => 'nom_salle',
                'query_builder' => function (SalleRepository $er) {
                    return $er->createQueryBuilder('s')
                        ->leftJoin('App\Entity\Sa', 'sa', 'WITH', 'sa.salle = s.id')  // Jointure avec la table sa
                        ->where('sa.salle IS NULL ');  // Filtre sur les sa qui n'ont pas de salle
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
        ]);
    }
}
