<?php
namespace App\Form;

use App\Constants;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ImportNewType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('title', TextType::class);

        $builder->add('description', TextAreaType::class, array('required' => false, 'empty_data' => null));

        $builder->add('url', UrlType::class, array('required' => true));

        $builder->add('privacy', ChoiceType::class, [
                'choices'  => [
                    'Public' => Constants::PRIVACY_LEVEL_PUBLIC,
                    'Only Followers'=>Constants::PRIVACY_LEVEL_ONLY_FOLLOWERS,
                    'Only You' => Constants::PRIVACY_LEVEL_PRIVATE,
                ],
                'data' => $options['account']->getAccountLocal()->getDefaultPrivacy(),
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'account' => null,
        ));
    }
}
