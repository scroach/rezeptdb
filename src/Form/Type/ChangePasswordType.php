<?php


namespace App\Form\Type;

use App\Entity\ChangePassword;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;


class ChangePasswordType extends AbstractType {

	public function buildForm(FormBuilderInterface $builder, array $options) {
		$builder->add('oldPassword', PasswordType::class, ['label' => 'Altes Passwort']);
		$builder->add('newPassword', RepeatedType::class, [
			'type' => PasswordType::class,
			'label' => 'Neues Passwort',
			'invalid_message' => 'Die Passwörter müssen übereinstimmen!',
			'required' => true,
			'first_options' => ['label' => 'Einmal ...'],
			'second_options' => ['label' => '... und nochmal bitte :)']
		]);
	}

	public function setDefaultOptions(OptionsResolver $resolver) {
		$resolver->setDefaults(['data_class' => ChangePassword::class]);
	}

}