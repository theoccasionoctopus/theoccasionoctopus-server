<?php
namespace App\Form;

use App\Constants;
use App\Entity\Account;
use App\Entity\AccountLocal;
use App\Entity\Country;
use App\Entity\TimeZone;
use App\Entity\User;
use App\Repository\CountryRepository;
use App\Repository\TimeZoneRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;

class AccountEditSettingsType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('title', TextType::class, [
                'mapped'=>false,
                'label'=>'Account name',
                'data'=>$options['account']->getTitle(),
            ])
            ->add('description', TextAreaType::class, [
                'label'=>'Description',
                'required' => false,
            ])
            ->add('default_privacy', ChoiceType::class, [
                'expanded'=>true,
                'multiple'=>false,
                'choices'  => [
                    'Public' => Constants::PRIVACY_LEVEL_PUBLIC,
                    'Only Followers'=>Constants::PRIVACY_LEVEL_ONLY_FOLLOWERS,
                    'Only You' => Constants::PRIVACY_LEVEL_PRIVATE,
                ]
            ])
            ->add('default_country', EntityType::class, [
                'class'=>Country::class,
                'choice_label' => 'title',
                'query_builder' => function (CountryRepository $cr) {
                    return $cr->createQueryBuilder('c')
                        ->orderBy('c.title', 'ASC');
                },
            ])
            ->add('default_timezone', EntityType::class, [
                'class'=>TimeZone::class,
                'choice_label' => 'title',
                'query_builder' => function (TimeZoneRepository $tzr) {
                    return $tzr->createQueryBuilder('tz')
                        ->orderBy('tz.title', 'ASC');
                },
            ])
            ->add('seo_index_follow', ChoiceType::class, [
                'expanded'=>true,
                'multiple'=>false,
                'label'=>'Search Engines',
                'choices'  => [
                    'Request to include' => true,
                    'Request not to list' => false,
                ],
            ])
            ->add('listInDirectory', ChoiceType::class, [
                'expanded'=>true,
                'multiple'=>false,
                'label'=>'List in Directory',
                'choices'  => [
                    'Listed' => true,
                    'Do not list' => false,
                ],
            ])
            ->add('manuallyApprovesFollowers', ChoiceType::class, [
                'expanded'=>true,
                'multiple'=>false,
                'label'=>'New Followers',
                'choices'  => [
                    'Anyone can follow' => false,
                    'Must be approved' => true,
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => AccountLocal::class,
            'account'=>null,
        ));
    }
}
