<?php
namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;

class APIAccessTokenNewType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        $builder
            ->add('note', TextAreaType::class, array('required'=>false, 'empty_data' => null))
            ->add('write', CheckboxType::class, [
                'label'    => 'Can Write?',
                'required' => false,
            ]);

    }

    public function configureOptions(OptionsResolver $resolver)
    {

    }
}
