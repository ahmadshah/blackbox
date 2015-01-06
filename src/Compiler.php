<?php namespace Blackbox;

use Blackbox\Reader;
use Blackbox\Configurator;
use Illuminate\Support\Str as S;
use Illuminate\Support\Arr as A;
use Blackbox\Contracts\Command;
use Illuminate\Filesystem\Filesystem;
use Blackbox\Commands\MakeCommand;

class Compiler implements Command
{
    const FEATUREDIR = 'features';

    const TESTDIR = 'tests';

    protected $reader;

    protected $command;

    protected $filesystem;

    protected $config;

    protected $blackbox;

    public function __construct(MakeCommand $command, Filesystem $filesystem, Configurator $config, Reader $reader)
    {
        $this->command = $command;
        $this->filesystem = $filesystem;
        $this->config = $config;
        $this->reader = $reader;

        $this->loadConfig();
    }

    public function run()
    {
        $features = $this->filesystem->files(getcwd().'/'.self::FEATUREDIR);

        if (count($features) < 1) {
            $this->command->say('error', 'No features were found. How about creating one first.');
            exit();
        }

        foreach ($features as $feature) {
            $this->extractScenarios($feature);
        }

        $this->createAbstractTestCase();
    }

    protected function loadConfig()
    {
        $this->config->load();
        $this->blackbox = $this->config->getConfigs();
    }

    protected function createAbstractTestCase()
    {
        $testCase = $this->filesystem->get(__DIR__.'/../stubs/BlackboxTestCase.php');

        foreach ($this->blackbox as $key => $value) {
            $testCase = str_replace("@{$key}", $value, $testCase);
        }

        $this->filesystem->put(getcwd().'/'.self::TESTDIR.'/BlackboxTestCase.php', $testCase);
    }

    protected function createTestCase(array $scenarios, $filename)
    {
        $testCases = [];

        foreach ($scenarios as $key => $scenario) {
            $stub = $this->filesystem->get(__DIR__.'/../stubs/StubTestItem.php');
            $stub = str_replace('@TESTMETHOD', $key, $stub);
            $assertions = implode($this->createTestCaseAssertions($scenario), "\n");
            $stub = str_replace('@ASSERTIONS', $assertions, $stub);

            $testCases[] = $stub;
        }

        $stubCase = $this->filesystem->get(__DIR__.'/../stubs/StubTest.php');
        $stubCase = str_replace(['@TESTCLASS', '@TESTCASES'], [$filename.'Test', implode($testCases, "\n")], $stubCase);

        $this->filesystem->put(getcwd().'/'.self::TESTDIR.'/'.$filename.'Test.php', $stubCase);
    }

    protected function createTestCaseAssertions($scenario)
    {
        $assertions = [];

        foreach ($scenario as $assertion) {
            if ($assertion['action'] == 'CheckForElement') {
                $assertions[] = '$this->verifyAttribute("xpath='.$assertion['xpath'].'", "'.$assertion['name'].'");';
            }
        }

        return $assertions;
    }

    protected function extractScenarios($feature)
    {
        $rows = $this->reader->load($feature)->getCsv()->fetchAssoc();
        $scenarios = [];

        foreach ($rows as $row) {
            if ($row['TCID'] != '') {
                if ( ! A::has($scenarios, $row['TCID'])) {
                    $scenarios[$row['TCID']] = [];
                }

                $object = [
                    'name' => $row['ObjectName'],
                    'field' => $row['TestDataFiled'],
                    'xpath' => $row['Object'],
                    'action' => $row['Action']
                ];

                array_push($scenarios[$row['TCID']], $object);
                unset($object);
            }
        }

        $file = $this->filesystem->name($feature);
        $extension = $this->filesystem->extension($feature);
        $filename = S::camel(str_replace('.'.$extension, '', $file));

        $this->createTestCase($scenarios, $filename);

        $this->filesystem->delete(__DIR__.'/../stubs/stubs.csv');
    }
}