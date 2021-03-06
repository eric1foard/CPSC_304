<?php

namespace AppBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class PhotoType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
        ->add('file', 'file', array('required' => true))
        ->add('latitude', 'number', array('required' => true))
        ->add('longitude','number', array('required' => true))
        ->add('device','text', array('required' => true))
        ->add('tags', 'entity', array(
            'class' => 'AppBundle:Tag',
            'property' => 'tagName',
            'expanded' => true,
            'multiple' => true
            ));
    }
    
    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'AppBundle\Entity\Photo'
            ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'appbundle_photo';
    }
}
