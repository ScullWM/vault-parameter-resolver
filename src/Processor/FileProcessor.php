<?php

namespace IED\VaultParameterResolver\Processor;

use Symfony\Component\Yaml\Inline;
use Symfony\Component\Yaml\Parser;
use Symfony\Component\Yaml\Yaml;
use IED\VaultParameterResolver\Gateway\GatewayInterface;
use IED\VaultParameterResolver\VaultKey;

class FileProcessor
{
    public function process($file, GatewayInterface $gateway)
    {
        if (false === is_file($file)) {
            throw new \InvalidArgumentException(sprintf('File %s does not exist.', $file));
        }

        $content      = file_get_contents($file);
        $pcre         = '/\%vault\(.*\)%/';
        preg_match_all($pcre, $content, $matches);
        $replacements = [];

        foreach ($matches[0] as $key) {
            $vaultKey = VaultKey::createFromString(substr($key, 7, -2));
            $replacements[$key] = $gateway->resolve($vaultKey);
        }

        file_put_contents($file, str_replace(array_keys($replacements), array_values($replacements), $content));

        return $replacements;
    }
}
