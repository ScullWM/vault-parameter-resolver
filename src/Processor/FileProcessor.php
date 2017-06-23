<?php

namespace IED\VaultParameterResolver\Processor;

use IED\VaultParameterResolver\Exception\NotFoundVaultKeyException;
use IED\VaultParameterResolver\Gateway\GatewayInterface;
use IED\VaultParameterResolver\VaultKey;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Yaml\Inline;
use Symfony\Component\Yaml\Parser;
use Symfony\Component\Yaml\Yaml;

class FileProcessor
{
    private $questionHelper;
    private $input;
    private $output;

    public function __construct(QuestionHelper $questionHelper = null, InputInterface $input = null, OutputInterface $output = null)
    {
        $this->questionHelper = $questionHelper;
        $this->input = $input;
        $this->output = $output;
    }

    public function process($file, GatewayInterface $gateway, $writeChanges = true)
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
            try {
                $replacements[$key] = $gateway->resolve($vaultKey);
            } catch (NotFoundVaultKeyException $e) {
                if (null === $this->questionHelper) {
                    throw $e;
                }

                $question = new Question(sprintf('No value found for key "<info>%s</info>", '.PHP_EOL.'please set a value:', $vaultKey->getNamespace().'#'.$vaultKey->getField()));
                $value = $this->questionHelper->ask($this->input, $this->output, $question);

                $gateway->write($vaultKey, $value);

                $replacements[$key] = $value;
            }
        }

        if ($writeChanges) {
            file_put_contents($file, str_replace(array_keys($replacements), array_values($replacements), $content));
        }

        return $replacements;
    }
}
