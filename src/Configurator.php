<?php namespace Blackbox;

use Illuminate\Support\Fluent;
use Illuminate\Support\Arr as A;
use Illuminate\Filesystem\Filesystem;

class Configurator
{
    const BLACKBOX = 'blackbox.conf';

    protected $filesystem;

    protected $configs = [];

    public function __construct(Filesystem $filesystem)
    {
        $this->filesystem = $filesystem;
    }

    public function load()
    {
        $configs = $this->readConfigFile();

        foreach ($configs as $config) {
            $items = explode('=', $config);
            $this->configs = A::add($this->configs, $items[0], $items[1]);
        }
    }

    public function getConfigs()
    {
        return $this->configs;
    }

    protected function readConfigFile()
    {
        $configs = $this->filesystem->get(getcwd().'/'.self::BLACKBOX);
        $configs = explode("\n", $configs);

        return $configs;
    }
}