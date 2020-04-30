<?php

namespace App\Form;

use App\Entity\Sortie;
use DateTime;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TimeType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\GreaterThan;
use Symfony\Component\Validator\Constraints\NotEqualTo;
use Symfony\Component\Validator\Constraints\NotNull;

class SortieType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('nom', TextType::class, ['label' => 'Nom de la sortie : '])
            ->add('dateHeureDebut', DateTimeType::class, ['label' => 'Date et heure de la sortie : ', 'data' => new DateTime(), 'constraints' => [new GreaterThan(new DateTime())]])
            ->add('dateLimiteInscription', DateType::class, [
                'label' => 'Date limite d\'inscription : ', 'data' => new DateTime(),
                'constraints' => [new GreaterThan(new DateTime())]
            ])
            ->add('nbInscriptionsMax', IntegerType::class, ['label' => 'Nombre de places : ', 'constraints' => [new GreaterThan(0)]])
            ->add('duree', TimeType::class, ['label' => 'Durée : '])
            ->add('infosSortie', TextareaType::class, ['label' => 'Description et infos : '])
            ->add('lieu', null, ["choice_label" => "nom", 'constraints' => [new NotNull()]])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Sortie::class,
        ]);
    }
}