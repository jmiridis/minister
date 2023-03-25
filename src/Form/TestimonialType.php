<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Vich\UploaderBundle\Form\Type\VichFileType;
use App\Entity\Testimonial;

class TestimonialType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('content', TextareaType::class, [
                'required' => true,
            ])
            ->add('name', null, [
                'required' => true,
            ])
            ->add('weddingDate', null, [
                'required' => true,
            ])
            ->add('location', null, [
                'required' => true,
            ]);

        if ($options['source'] === 'backend') {
            $builder
                ->add('imageFile', VichFileType::class, [
                    'required' => false
                ])
                ->add('position')
                ->add('isActive');
        }
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Testimonial::class,
            'source'     => 'frontend'
        ]);
    }
}
