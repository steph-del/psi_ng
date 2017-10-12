<?php

namespace AppBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class NodeType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('frontId')
                ->add('level')
                ->add('repository')
                ->add('repositoryIdNomen')
                ->add('name')
                ->add('coef')
                ->add('geoJson')
                ->add('lft')
                ->add('lvl')
                ->add('rgt')
                ->add('children')
                ->add('root')
                ->add('parentTree');
    }
    
    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'AppBundle\Entity\Node'
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'appbundle_node';
    }


}
