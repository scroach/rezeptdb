<?php

namespace App\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class ImageExtension extends AbstractExtension
{
    public function __construct(
        private string $publicDir,
        private string $cacheDir,
    ) {
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('image', [$this, 'image']),
        ];
    }

    public function image(string $path): ImageReference
    {
        return new ImageReference($this->publicDir, $this->cacheDir, $path);
    }
}
