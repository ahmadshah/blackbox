<?php namespace Blackbox\Contracts;

interface Command
{
	/**
	 * Execute the console command
	 * 
	 * @return void
	 */
	public function run();
}