<?php namespace Blackbox;

use PHPExcel_IOFactory as Factory;
use League\Csv\Reader as CsvReader;

class Reader
{
    protected $csv;

    public function load($excelFile)
    {
        $this->convertToCsv($excelFile);

        return $this;
    }

    public function getCsv()
    {
        return $this->csv;
    }

    protected function convertToCsv($input)
    {
        $type = Factory::identify($input);
        $reader = Factory::createReader($type);
        $reader->setReadDataOnly(true);
        $excel = $reader->load($input);

        $writer = Factory::createWriter($excel, 'CSV');
        $writer->save(__DIR__.'/../stubs/stubs.csv');

        $this->csv = CsvReader::createFromPath(__DIR__.'/../stubs/stubs.csv');
    }
}