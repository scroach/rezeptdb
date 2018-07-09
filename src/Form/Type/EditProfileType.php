<?php


namespace App\Form\Type;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;


class EditProfileType extends AbstractType {

	public function buildForm(FormBuilderInterface $builder, array $options) {
		$builder->add('email', TextType::class, ['label' => 'Email']);
		$builder->add('username', TextType::class, ['label' => 'Username']);
	}

	public function setDefaultOptions(OptionsResolver $resolver) {
		$resolver->setDefaults(['data_class' => User::class]);
	}

}