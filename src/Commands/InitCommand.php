<?php namespace Blackbox\Commands;

use Blackbox\Contracts\Console;
use Blackbox\Traits\CommandTrait;
use Blackbox\Traits\ContainerTrait;
use Illuminate\Filesystem\Filesystem;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class InitCommand extends Command implements Console
{
    use CommandTrait, ContainerTrait;

    const BLACKBOX = 'blackbox.conf';

    /**
     * Configure the command option
     * 
     * @return void
     */
    protected function configure()
    {
        $this->setName('init')
            ->setDescription('Generate the blackbox test suite skeleton');
    }

    /**
     * {@inheritdoc}
     */
    public function fire()
    {
        $this->loadFilesystem();
        $filesystem = $this->container->make('filesystem');

        if ( ! $filesystem->exists(getcwd().'/'.self::BLACKBOX)) {
            $filesystem->copy(__DIR__.'/../../stubs/'.self::BLACKBOX, getcwd().'/'.self::BLACKBOX);
        }

        if ( ! $filesystem->exists(getcwd().'/phpunit.xml')) {
            $xml = $filesystem->get(__DIR__.'/../../stubs/phpunit.xml');
            $autoloader = str_replace('src/Commands', '', __DIR__);
            $xml = str_replace('bootstrap=""', 'bootstrap="'.$autoloader.'vendor/autoload.php"', $xml);
            $filesystem->put(getcwd().'/phpunit.xml', $xml);
        }

        $directories = ['features', 'coverage', 'tests'];
        foreach ($directories as $directory) {
            if ( ! $filesystem->isDirectory(getcwd().'/'.$directory)) {
                $filesystem->makeDirectory(getcwd().'/'.$directory);
            }
        }

        $this->say('info', 'Blackbox is now configured. You can start writing test features.');
    }
}