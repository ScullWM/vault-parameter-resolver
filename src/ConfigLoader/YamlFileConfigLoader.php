<?php

namespace IED\VaultParameterResolver\ConfigLoader;

use Symfony\Component\Yaml\Yaml;

class YamlFileConfigLoader extends ArrayConfigLoader
{
    public static function load($config)
    {
        return parent::load(
            Yaml::parse(file_get_contents($config))
        );
    }
}
