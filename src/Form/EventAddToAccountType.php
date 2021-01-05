<?php
namespace App\Form;

use App\Constants;
use App\Entity\Country;
use App\Entity\Event;
use App\Entity\TimeZone;
use App\Repository\CountryRepository;
use App\Repository\TimeZoneRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;

class EventAddToAccountType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('privacy', ChoiceType::class, [
            'expanded'=>true,
            'multiple'=>false,
            'label'=>'Can be seen by',
            'choices' => [
                'Public' => Constants::PRIVACY_LEVEL_PUBLIC,
                'Only Followers'=>Constants::PRIVACY_LEVEL_ONLY_FOLLOWERS,
                'Only You' => Constants::PRIVACY_LEVEL_PRIVATE,
            ],
            'data' => $options['account']->getAccountLocal()->getDefaultPrivacy(),
        ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => Event::class,
            'account' => null,
        ));
    }
}
