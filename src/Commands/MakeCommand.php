<?php namespace Blackbox\Commands;

use Blackbox\Compiler;
use Blackbox\Contracts\Console;
use Blackbox\Traits\CommandTrait;
use Blackbox\Traits\ContainerTrait;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class MakeCommand extends Command implements Console
{
    use CommandTrait, ContainerTrait;

    /**
     * Configure the command option
     * 
     * @return void
     */
    protected function configure()
    {
        $this->setName('compile')
            ->setDescription('Compile feature excel sheets to PHPUnit test cases')
            ->addArgument('feature-excel-file', InputArgument::OPTIONAL, 'Feature excel filename');
    }

    /**
     * {@inheritdoc}
     */
    public function fire()
    {
        $this->loadFilesystem();
        $this->loadConfig();
        $this->loadExcelReader();

        $compiler = new Compiler(
            $this,
            $this->container->make('filesystem'),
            $this->container->make('config'),
            $this->container->make('reader')
        );
        
        $compiler->run();
    }
}