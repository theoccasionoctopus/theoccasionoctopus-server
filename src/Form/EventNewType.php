<?php
namespace App\Form;

use App\Constants;
use App\Entity\Country;
use App\Entity\Event;
use App\Entity\TimeZone;
use App\Library;
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
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TimeType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;

class EventNewType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('title', TextType::class, array('required' => false, 'empty_data' => null));
        $builder->add('description', TextAreaType::class, array('required' => false, 'empty_data' => null));
        $builder->add('url', UrlType::class, array('required' => false, 'empty_data' => null, 'label'=> 'Website'));
        $builder->add('url_tickets', UrlType::class, array('required' => false, 'empty_data' => null, 'label'=> 'Website for tickets'));

        $builder->add('all_day', CheckboxType::class, [
            'label' => 'All Day Event',
            'required' => false,
            'mapped' => false
        ]);

        $builder->add('start_date', DateType::class, [
            'label' => 'Start',
            'widget' => 'single_text',
            'attr' => array('class' => 'dateInput'),
            'input'=>'array',
            'required' => true,
            'mapped' => false
        ]);

        $builder->add('start_time', TimeType::class, [
            'label' => 'Start',
            'input'=>'array',
            'required' => true,
            'mapped' => false,
            'with_seconds' => true,
        ]);

        $builder->add('end_date', DateType::class, [
            'label' => 'End',
            'widget' => 'single_text',
            'attr' => array('class' => 'dateInput'),
            'input'=>'array',
            'required' => true,
            'mapped' => false
        ]);

        $builder->add('end_time', TimeType::class, [
            'label' => 'End',
            'input'=>'array',
            'required' => true,
            'mapped' => false,
            'with_seconds' => true,
        ]);

        $builder->add('country', EntityType::class, [
            'class' => Country::class,
            'choice_label' => 'title',
            'query_builder' => function (CountryRepository $er) {
                return $er->createQueryBuilder('u')
                    ->orderBy('u.title', 'ASC');
            },
            'data' => $options['account']->getAccountLocal()->getDefaultCountry(),
        ]);
        $builder->add('timezone', EntityType::class, [
            'class' => TimeZone::class,
            'choice_label' => 'title',
            'query_builder' => function (TimeZoneRepository $er) {
                return $er->createQueryBuilder('u')
                    ->orderBy('u.title', 'ASC');
            },
            'data' => $options['account']->getAccountLocal()->getDefaultTimezone(),
        ]);
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

        // TODO a text field is NOT a user friendly way of editing a RRULE! Better UI needed :-)
        $builder->add('rrule', TextType::class, array('required' => false, 'empty_data' => null));

        /** @var \closure $myExtraFieldValidator **/
        $myExtraFieldValidator = function (FormEvent $event) {
            $form = $event->getForm();
            $startDate = $form->get('start_date')->getData();
            $startTime = $form->get('all_day')->getData() ? null : $form->get('start_time')->getData();
            $endDate = $form->get('end_date')->getData();
            $endTime = $form->get('all_day')->getData() ? null : $form->get('end_time')->getData();
            // validate end is not before start
            if (Library::isEndBeforeStartByArrays($startDate, $startTime, $endDate, $endTime)) {
                $form['start_date']->addError(new FormError("The start can not be after the end!"));
            }
            // TODO validate years
            // TODO validate years
        };

        // adding the validator to the FormBuilderInterface
        $builder->addEventListener(FormEvents::POST_SUBMIT, $myExtraFieldValidator);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => Event::class,
            'account' => null,
        ));
    }
}
