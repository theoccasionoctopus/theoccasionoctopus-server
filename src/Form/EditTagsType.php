<?php
namespace App\Form;

use App\Entity\Country;
use App\Entity\Event;
use App\Entity\Tag;
use App\Entity\TimeZone;
use App\Repository\CountryRepository;
use App\Repository\TagRepository;
use App\Repository\TimeZoneRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;

class EditTagsType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        $builder
            ->add('tags', EntityType::class, [
                'class' => Tag::class,
                'choice_label' => 'title',
                'multiple'=> True,
                'expanded' => True,
                'query_builder' => function (TagRepository $er) use ($options) {
                    return $er->createQueryBuilder('t')
                        ->where('t.account = :account')
                        ->setParameter('account', $options['account'])
                        ->orderBy('t.title', 'ASC');
                },
                'data'=>$options['currentTags'],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'account' => null,
            'currentTags' => array(),
        ));
    }
}
