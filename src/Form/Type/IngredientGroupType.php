<?php


namespace App\Form\Type;

use App\Entity\IngredientGroup;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class IngredientGroupType extends AbstractType {

	public function buildForm(FormBuilderInterface $builder, array $options) {
		$builder->add('label', TextType::class, [
			'label' => 'Gruppenname',
			'required' => false,
			'attr' => ['placeholder' => 'Teig, Sauce, ...']]);

		$builder->add('ingredients', CollectionType::class, array(
			'label' => 'Zutaten',
			'allow_add' => true,
			'allow_delete' => true,
			'delete_empty' => true,
			'by_reference' => false,
			'prototype_name' => '__ingredientcounter__',
			'attr' => ['class' => 'ingredientList'],
			'entry_type' => IngredientType::class,
			'entry_options' => array(
				'required' => false,
				'attr' => ['placeholder' => '100 g Mehl, 2 EL Zucker, ...']
			),
		));
	}

	public function configureOptions(OptionsResolver $resolver) {
		$resolver->setDefaults(array(
			'data_class' => IngredientGroup::class,
		));
	}

}