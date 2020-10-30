<?php
namespace App\Form;

use App\Entity\Country;
use App\Entity\Event;
use App\Entity\TimeZone;
use App\Repository\CountryRepository;
use App\Repository\TimeZoneRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;

class EventEditDetailsType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        $builder->add('title', TextType::class, array('required'=>false, 'empty_data' => null));


        $builder->add('description', TextAreaType::class, array('required' => false, 'empty_data' => null));
        $builder->add('url', UrlType::class, array('required' => false, 'empty_data' => null));
        $builder->add('url_tickets', UrlType::class, array('required' => false, 'empty_data' => null));

        $builder->add('start_at', DateTimeType::class, [
            'label'=>'Start',
            'date_widget'=> 'single_text',
            'model_timezone' => $options['timeZoneName'],
            'view_timezone' => $options['timeZoneName'],
            'attr' => array('class' => 'dateInput'),
            'required'=>true,
            'mapped'=>false
        ]);


        $builder->add('end_at', DateTimeType::class, [
            'label'=>'End',
            'date_widget'=> 'single_text',
            'model_timezone' => $options['timeZoneName'],
            'view_timezone' => $options['timeZoneName'],
            'attr' => array('class' => 'dateInput'),
            'required'=>true,
            'mapped'=>false
        ]);

        // TODO a text field is NOT a user friendly way of editing a RRULE! Better UI needed :-)
        $builder->add('rrule', TextType::class, array('required' => false, 'empty_data' => null));

        $builder->add('privacy', ChoiceType::class, [
            'choices' => [
                'Public' => 0,
                'Private' => 10000,
            ],
        ]);

        $builder->add('country', EntityType::class, [
            'class' => Country::class,
            'choice_label' => 'title',
            'query_builder' => function (CountryRepository $er) {
                return $er->createQueryBuilder('u')
                    ->orderBy('u.title', 'ASC');
            },
        ]);
        $builder->add('timezone', EntityType::class, [
                'class' => TimeZone::class,
                'choice_label' => 'title',
                'query_builder' => function (TimeZoneRepository $er) {
                    return $er->createQueryBuilder('u')
                        ->orderBy('u.title', 'ASC');
                },
        ]);

        foreach ($options['edit_extra_fields'] as $edit_extra_field) {
            $builder->add(
                'extra_field_' . md5($edit_extra_field),
                TextAreaType::class,
                array(
                    'label' => 'Extra Field: ' . $edit_extra_field,
                    'required' => false,
                    'mapped' => false,
                    'data' => $builder->getData()->getExtraField($edit_extra_field),
                )
            );
        }

        $builder->add(
            'new_extra_field_key',
            TextType::class,
            array(
                'label' => 'New Extra Field: Called?',
                'required'=>false,
                'mapped' => false,
                'empty_data' => null
            )
        );

        $builder->add(
            'new_extra_field_value',
            TextAreaType::class,
            array(
                'label' => 'New Extra Field: Contents?',
                'required' => false,
                'mapped' => false,
                'empty_data' => null
            )
        );

        /** @var \closure $myExtraFieldValidator **/
        $myExtraFieldValidator = function(FormEvent $event) {
            $form = $event->getForm();
            $myExtraFieldStart = $form->get('start_at')->getData();
            $myExtraFieldEnd = $form->get('end_at')->getData();
            // Validate end is not the same as start
            if ($myExtraFieldStart == $myExtraFieldEnd) {
                $form['end_at']->addError(new FormError("The end can not be the same as the start!"));
            }
            // Validate end is after start?
            if ($myExtraFieldStart > $myExtraFieldEnd) {
                $form['start_at']->addError(new FormError("The start can not be after the end!"));
            }
            // TODO validate years
        };

        // adding the validator to the FormBuilderInterface
        $builder->addEventListener(FormEvents::POST_SUBMIT, $myExtraFieldValidator);

    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => Event::class,
            'timeZoneName' => null,
            'edit_extra_fields' => array(),
        ));
    }
}