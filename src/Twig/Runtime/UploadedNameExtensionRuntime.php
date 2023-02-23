<?php

namespace App\Twig\Runtime;

use Twig\Extension\RuntimeExtensionInterface;

class UploadedNameExtensionRuntime implements RuntimeExtensionInterface
{
    public function __construct()
    {
        // Inject dependencies if needed
    }

    public function unrename($value)
    {
        preg_match('/^(.*)-[a-z0-9]+(\.[a-zA-Z]{3,4})$/', $value, $matches);

        return ($matches[1] ?? '') . ($matches[2] ?? '');
    }
}
