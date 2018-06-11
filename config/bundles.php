<?php

return [
	// framework stuff
	Symfony\Bundle\FrameworkBundle\FrameworkBundle::class => ['all' => true],
	Symfony\Bundle\TwigBundle\TwigBundle::class => ['all' => true],
	Symfony\Bundle\SecurityBundle\SecurityBundle::class => ['all' => true],
	Symfony\Bundle\SwiftmailerBundle\SwiftmailerBundle::class => ['all' => true],
	Symfony\Bundle\MonologBundle\MonologBundle::class => ['all' => true],
	Sensio\Bundle\FrameworkExtraBundle\SensioFrameworkExtraBundle::class => ['all' => true],

	// doctrine bundles
	Doctrine\Bundle\DoctrineCacheBundle\DoctrineCacheBundle::class => ['all' => true],
	Doctrine\Bundle\DoctrineBundle\DoctrineBundle::class => ['all' => true],
	Doctrine\Bundle\MigrationsBundle\DoctrineMigrationsBundle::class => ['all' => true],
	Stof\DoctrineExtensionsBundle\StofDoctrineExtensionsBundle::class => ['all' => true],

	// additional other stuff
	Gregwar\ImageBundle\GregwarImageBundle::class => ['all' => true],

	// dev / debug bundles
	Symfony\Bundle\DebugBundle\DebugBundle::class => ['dev' => true, 'test' => true],
	Symfony\Bundle\MakerBundle\MakerBundle::class => ['dev' => true],
	Symfony\Bundle\WebProfilerBundle\WebProfilerBundle::class => ['dev' => true, 'test' => true],
];
