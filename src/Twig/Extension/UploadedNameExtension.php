<?php

namespace App\Twig\Extension;

use App\Twig\Runtime\UploadedNameExtensionRuntime;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Twig\TwigFunction;

class UploadedNameExtension extends AbstractExtension
{
    public function getFilters(): array
    {
        return [
            // If your filter generates SAFE HTML, you should add a third
            // parameter: ['is_safe' => ['html']]
            // Reference: https://twig.symfony.com/doc/3.x/advanced.html#automatic-escaping
            new TwigFilter('unrename', [UploadedNameExtensionRuntime::class, 'unrename'], ['is_safe' => ['html']]),
        ];
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('function_name', [UploadedNameExtensionRuntime::class, 'doSomething']),
        ];
    }
}
