<?php namespace Blackbox\Traits;

use Blackbox\Reader;
use Blackbox\Configurator;
use Illuminate\Container\Container;
use Illuminate\Filesystem\Filesystem;

trait ContainerTrait
{
    protected $container;

    protected function loadContainer()
    {
        if ( ! $this->container instanceof Container) {
            $this->container = new Container;
        }

        return $this->container;
    }

    protected function loadFilesystem()
    {
        $container = $this->loadContainer();

        $container->singleton('filesystem', function () {
            return new Filesystem;
        });
    }

    protected function loadConfig()
    {
        $container = $this->loadContainer();
        $this->loadFilesystem();

        $container->singleton('config', function () {
            return new Configurator($this->container->make('filesystem'));
        });
    }

    protected function loadExcelReader()
    {
        $container = $this->loadContainer();

        $container->bind('reader', function () {
            return new Reader;
        });
    }
}