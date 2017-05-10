<?php

namespace IED\VaultParameterResolver\Compiler;

use Symfony\Component\Finder\Finder;

/**
 * Compiler
 *
 * @author Stephane PY <py.stephane1@gmail.com>
 */
class Compiler
{
    public function compile($pharFile = 'vault-parameter-resolver.phar')
    {
        if (file_exists($pharFile)) {
            unlink($pharFile);
        }

        $phar = new \Phar($pharFile, 0, 'vault-parameter-resolver.phar');
        $phar->setSignatureAlgorithm(\Phar::SHA1);

        $phar->startBuffering();

        // CLI Component files
        foreach ($this->getFiles() as $file) {
            $path = str_replace(__DIR__.'/', '', $file);
            $phar->addFromString($path, file_get_contents($file));
        }
        $this->addVaultParameterResolver($phar);
        $this->unsetCompileCommand($phar);

        $phar->setStub($this->getStub());

        $phar->stopBuffering();

        unset($phar);

        chmod($pharFile, 0777);
    }

    protected function addVaultParameterResolver(\Phar $phar)
    {
        $content = file_get_contents(__DIR__ . '/../../vault-parameter-resolver');
        $content = preg_replace('{^#!/usr/bin/env php\s*}', '', $content);

        $phar->addFromString('vault-parameter-resolver', $content);
    }

    protected function unsetCompileCommand(\Phar $phar)
    {
        $content = file_get_contents(__DIR__ . '/../Console/Application.php');
        $content = preg_replace('{\$this\-\>add\(new Command\\\CompileCommand\(\)\)\;\s*}', '', $content);

        $phar->addFromString('src/Console/Application.php', $content);
    }

    protected function getStub()
    {
        return "#!/usr/bin/env php\n<?php Phar::mapPhar('vault-parameter-resolver.phar'); require 'phar://vault-parameter-resolver.phar/vault-parameter-resolver'; __HALT_COMPILER();";
    }

    protected function getFiles()
    {
        $iterator = Finder::create()->files()->name('*.php')->in(array('vendor', 'src'));

        return array_merge(array('vault-parameter-resolver'), iterator_to_array($iterator));
    }
}
