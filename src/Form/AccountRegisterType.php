<?php
namespace App\Form;

use App\Entity\Account;
use App\Entity\AccountLocal;
use App\Entity\Country;
use App\Entity\TimeZone;
use App\Entity\User;
use App\Repository\CountryRepository;
use App\Repository\TimeZoneRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;

class AccountRegisterType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('username', TextType::class)
            ->add('title', TextType::class, [
                'mapped'=> false,
            ])
            ->add('default_privacy', ChoiceType::class, [
                'choices'  => [
                    'Public' => 0,
                    'Private' => 10000,
                ]])
            ->add('default_country', EntityType::class, [
                'class'=>Country::class,
                'choice_label' => 'title',
                'query_builder' => function (CountryRepository $cr) {
                    return $cr->createQueryBuilder('c')
                        ->orderBy('c.title', 'ASC');
                },
            ] )
            ->add('default_timezone', EntityType::class, [
                'class'=>TimeZone::class,
                'choice_label' => 'title',
                'query_builder' => function (TimeZoneRepository $tzr) {
                    return $tzr->createQueryBuilder('tz')
                        ->orderBy('tz.title', 'ASC');
                },
            ] )
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => AccountLocal::class,
        ));
    }
}