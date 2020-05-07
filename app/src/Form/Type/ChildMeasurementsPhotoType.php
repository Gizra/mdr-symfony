<?php


namespace App\Form\Type;


use App\Entity\ChildMeasurements;
use App\Entity\ChildMeasurementsPhoto;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;

class ChildMeasurementsPhotoType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
          ->add('file', FileType::class, [
            'label' => 'Photo',

              // unmapped means that this field is not associated to any entity property
            'mapped' => false,
            'required' => false,

              // unmapped fields can't define their validation using annotations
              // in the associated entity, so you can use the PHP constraint classes
            'constraints' => [
              new File([
                'mimeTypes' => [
                  'image/jpeg',
                  'image/png',
                ],
                'mimeTypesMessage' => 'Please upload a valid image',
              ])
            ],
          ])
          ->add('save', SubmitType::class,
            [
              'attr' => ['class' => 'pure-button pure-button-primary'],
            ]
          );
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
          'data_class' => ChildMeasurementsPhoto::class,
        ]);
    }

}
