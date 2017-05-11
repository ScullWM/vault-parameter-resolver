<?php

namespace IED\VaultParameterResolver\Console\Command;

use IED\VaultParameterResolver\ConfigLoader\EnvConfigLoader;
use IED\VaultParameterResolver\ConfigLoader\YamlFileConfigLoader;
use IED\VaultParameterResolver\Processor\FileProcessor;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class ResolverCommand extends Command
{
    /**
     * @see Command
     */
    protected function configure()
    {
        parent::configure();

        $this
            ->setName('resolve')
            ->setDescription('Resolve parameter files.')
            ->addOption('config', 'c', InputOption::VALUE_OPTIONAL, 'Config path')
            ->addOption('file', 'f', InputOption::VALUE_IS_ARRAY ^ InputOption::VALUE_REQUIRED, 'Files to resolve.')
            ;
    }

    /**
     * @see Command
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        if (count($input->getOption('file')) === 0) {
            throw new \LogicException('Enter file(s) via -f option.');
        }

        $config    = $this->buildConfiguration($input);
        $processor = new FileProcessor();
        $errors    = false;

        foreach ($input->getOption('file') as $file) {
            $output->write(sprintf('Process file “<comment>%s</comment>“: ', $file));
            try {
                $nbReplacements = $processor->process($file, $config->getGateway());
                $output->writeln(sprintf('<info>%d</info> resolved parameters.', count($nbReplacements)));
            } catch (\Exception $e) {
                $errors = true;
                $output->writeln(sprintf('<error>Error: %s</error>', $e->getMessage()));
            }
        }

        return $errors ? 1 : 0;
    }

    /**
     * @param InputInterface $input input
     *
     * @return Configuration
     */
    private function buildConfiguration(InputInterface $input)
    {
        $file = $input->getOption('config');

        if (null === $file) {
            return EnvConfigLoader::load();
        }

        if (false === is_file($file)) {
            throw new \InvalidArgumentException(sprintf('%s file not found.', $file));
        }

        return YamlFileConfigLoader::load($file);
    }
}
