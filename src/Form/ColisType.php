<?php

namespace App\Form;

use App\Entity\Agence;
use App\Entity\Client;
use App\Entity\Colis;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ColisType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('numeroSuivi')
            ->add('statut')
            ->add('destinataire')
            ->add('adresseLivraison')
            ->add('villeLivraison')
            ->add('codePostalLivraison')
            ->add('poidsKg')
            ->add('createdAt', null, [
                'widget' => 'single_text'
            ])
            ->add('agenceDepot', EntityType::class, [
                'class' => Agence::class,
                'choice_label' => 'id',
            ])
            ->add('client', EntityType::class, [
                'class' => Client::class,
                'choice_label' => 'id',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Colis::class,
        ]);
    }
}
