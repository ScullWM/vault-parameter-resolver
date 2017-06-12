<?php

namespace IED\VaultParameterResolver\Console\Command;

use IED\VaultParameterResolver\ConfigLoader\EnvConfigLoader;
use IED\VaultParameterResolver\ConfigLoader\YamlFileConfigLoader;
use IED\VaultParameterResolver\Processor\FileProcessor;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class SelfUpdateCommand extends Command
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('self-update')
            ->setDescription('Update vault-parameter-resolver.phar to the latest version.')
            ->setHelp(<<<EOT
The <info>self-update</info> command replace your vault-parameter-resolver.phar
by the latest version from github

<info>php vault-parameter-resolver.phar self-update</info>

EOT
        )
            ;
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $remoteFilename = 'https://github.com/inextensodigital/vault-parameter-resolver/raw/master/vault-parameter-resolver.phar';
        $localFilename  = $_SERVER['argv'][0];
        $tempFilename   = basename($localFilename, '.phar').'-temp.phar';

        try {
            copy($remoteFilename, $tempFilename);
            chmod($tempFilename, 0777 & ~umask());

            // test the phar validity
            $phar = new \Phar($tempFilename);
            // free the variable to unlock the file
            unset($phar);
            rename($tempFilename, $localFilename);
        } catch (\Exception $e) {
            if (!$e instanceof \UnexpectedValueException && !$e instanceof \PharException) {
                throw $e;
            }
            unlink($tempFilename);
            $output->writeln('<error>The download is corrupt ('.$e->getMessage().').</error>');
            $output->writeln('<error>Please re-run the self-update command to try again.</error>');
        }

        $output->writeln("<info>vault-parameter-resolver updated.</info>");
    }
}
