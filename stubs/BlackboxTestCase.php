<?php

abstract class BlackboxTestCase extends \PHPUnit_Extensions_SeleniumTestCase
{
    protected function setUp()
    {
        $this->setBrowser('*firefox');
        $this->setBrowserUrl('@APPURL');
    }
}