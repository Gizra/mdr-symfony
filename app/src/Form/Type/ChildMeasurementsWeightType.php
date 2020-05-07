<?php


namespace App\Form\Type;


use App\Entity\ChildMeasurements;
use App\Entity\ChildMeasurementsWeight;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;

class ChildMeasurementsWeightType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
          ->add('value', NumberType::class, ['required' => true])
          ->add('save', SubmitType::class,
            [
              'attr' => ['class' => 'pure-button pure-button-primary'],
            ]
          );
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
          'data_class' => ChildMeasurementsWeight::class,
        ]);
    }

}
