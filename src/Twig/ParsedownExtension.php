<?php

namespace App\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

/**
 * Allows the usage of parsedown in twig templates with activated safe mode so html will be escaped.
 */
class ParsedownExtension extends AbstractExtension {

    public function getFilters(): array
    {
        return [
            new TwigFilter('parsedown', [$this, 'parsedown'], ['is_safe' => ['html']]),
        ];
    }

    public function parsedown($text): string
    {
        $parsedown = new \Parsedown();
        $parsedown->setSafeMode(true);

        return (string) $parsedown->text((string) $text);
    }

}