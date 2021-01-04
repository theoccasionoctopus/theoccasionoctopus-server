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
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;

class AccountRegisterType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('username', TextType::class, [
                'label'=>'User name',
            ])
            ->add('default_privacy', ChoiceType::class, [
                'expanded'=>true,
                'multiple'=>false,
                'label'=>'Privacy',
                'choices'  => [
                    'Public' => Constants::PRIVACY_LEVEL_PUBLIC,
                    'Protected; Only Followers can see events; followers must be approved' => Constants::PRIVACY_LEVEL_ONLY_FOLLOWERS,
                ],
                'data'=>Constants::PRIVACY_LEVEL_PUBLIC,
            ])
            ->add('default_country', EntityType::class, [
                'class'=>Country::class,
                'choice_label' => 'title',
                'query_builder' => function (CountryRepository $cr) {
                    return $cr->createQueryBuilder('c')
                        ->orderBy('c.title', 'ASC');
                },
                'label'=>'Country',
            ])
            ->add('default_timezone', EntityType::class, [
                'class'=>TimeZone::class,
                'choice_label' => 'title',
                'query_builder' => function (TimeZoneRepository $tzr) {
                    return $tzr->createQueryBuilder('tz')
                        ->orderBy('tz.title', 'ASC');
                },
                'label'=>'Timezone',
            ])
        ;

        /** @var \closure $myExtraFieldValidator **/
        $myExtraFieldValidator = function (FormEvent $event) {
            $form = $event->getForm();
            $username = $form->get('username')->getData();
            // Validate only allowed characters
            // TODO should allow UTF-8 chars so foreign languages are supported, now you can have them in URL's. Need to also check things like webfinger spec.
            if (!preg_match("/^[a-zA-Z0-9_]+$/", $username)) {
                $form['username']->addError(new FormError("The username can only have letters, numbers and underscores."));
            }
        };
        $builder->addEventListener(FormEvents::POST_SUBMIT, $myExtraFieldValidator);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => AccountLocal::class,
        ));
    }
}
