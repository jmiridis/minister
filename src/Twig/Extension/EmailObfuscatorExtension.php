<?php

namespace App\Twig\Extension;

use App\Twig\Runtime\EmailObfuscatorExtensionRuntime;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Twig\TwigFunction;

class EmailObfuscatorExtension extends AbstractExtension
{
    public function getFilters(): array
    {
        return [
            // If your filter generates SAFE HTML, you should add a third
            // parameter: ['is_safe' => ['html']]
            // Reference: https://twig.symfony.com/doc/3.x/advanced.html#automatic-escaping
            new TwigFilter(
                'obfuscateEmailLink',
                [$this, 'obfuscateEmailLink'],
                ['is_safe' => ['html']]
            ),
        ];
    }

    public function getFunctions(): array
    {
        return [
        ];
    }

    function obfuscateEmailLink($email, $params = []): string
    {
        if (!is_array($params)) {
            $params = [];
        }

        // Tell search engines to ignore obfuscated uri
        if (!isset($params['rel'])) {
            $params['rel'] = 'nofollow';
        }

        $neverEncode = ['.', '@', '+']; // Don't encode those as not fully supported by IE & Chrome

        $urlEncodedEmail = '';
        for ($i = 0; $i < strlen($email); $i++) {
            // Encode 25% of characters
            if (!in_array($email[$i], $neverEncode) && mt_rand(1, 100) < 25) {
                $charCode        = ord($email[$i]);
                $urlEncodedEmail .= '%';
                $urlEncodedEmail .= dechex(($charCode >> 4) & 0xF);
                $urlEncodedEmail .= dechex($charCode & 0xF);
            } else {
                $urlEncodedEmail .= $email[$i];
            }
        }

        $obfuscatedEmail    = $this->obfuscateEmailAddress($email);
        $obfuscatedEmailUrl = $this->obfuscateEmailAddress('mailto:' . $urlEncodedEmail);

        $attribs = [];
        foreach ($params as $param => $value) {
            $attribs[] = $param . '="' . htmlspecialchars($value) . '"';
        }

        return sprintf("<a href=\"%s\" %s>%s</a>", $obfuscatedEmailUrl, join(' ', $attribs), $obfuscatedEmail);
    }

    function obfuscateEmailAddress(?string $email): string
    {
        $alwaysEncode = ['.', ':', '@'];

        $result = '';

        // Encode string using oct and hex character codes
        for ($i = 0; $i < strlen($email); $i++) {
            // Encode 25% of characters including several that always should be encoded
            if (in_array($email[$i], $alwaysEncode) || mt_rand(1, 100) < 25) {
                if (mt_rand(0, 1)) {
                    $result .= '&#' . ord($email[$i]) . ';';
                } else {
                    $result .= '&#x' . dechex(ord($email[$i])) . ';';
                }
            } else {
                $result .= $email[$i];
            }
        }

        return $result;
    }
}
