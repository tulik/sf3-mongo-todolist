<?php
namespace AppBundle\Form;

use AppBundle\Document\Task;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class TaskType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('value', TextType::class, [
                'label' => 'What:'
                    ])
            ->add('scheduled',TextType::class, [
                'label' => 'When:'
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'task_class' => Task::class,
        ));
    }
}