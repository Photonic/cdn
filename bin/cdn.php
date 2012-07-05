#!/usr/bin/env php
<?php

require __DIR__ . '/../vendor/autoload.php';

use Symfony\Component\Console\Application as BaseApplication;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Finder\Finder;

class TwitterBootstrap
{
	protected $template = "template.html";
	
	protected $data = array(
			'title'	=> 'Photonic Content Delivery Network'
		);
	
	public function renderTemplate($data)
	{
		$loader = new Twig_Loader_String();
		$twig = new Twig_Environment($loader);
		
		return $twig->render(file_get_contents(__DIR__.'/../'.$this->template),$data);
	}
	
	public function setData($key, $value)
	{
		$this->data['$key'] = $value;
	}
	
	public function toString()
	{
		return $this->renderTemplate($this->data);
	}
}


class CreateCommand extends Command
{
	protected function configure()
	{
		$this
			->setName('create')
			->setDescription('Generate HTML from files')
		;
	}
	
	protected function execute(InputInterface $input, OutputInterface $output)
	{
		$output->writeln("Collecting files");
		$finder = new Finder();
		
		$config = json_decode(file_get_contents(__DIR__."/../cdn.json"),true);
		//var_export($config);

		if(isset($config['folders']))
		{
			$output->writeln("Searching folders:");
			foreach($config['folders'] as $dir)
			{
				$finder->in(__DIR__.'/../'.$dir['dir']);
				$finder->depth('== 0');
				$output->writeln($dir['dir']);
			}
		}

		$page = new TwitterBootstrap();
		$page->setData('folders', $config['folders']);
		//$page->setData('path',$path);
		file_put_contents('files.html', $page->toString());
		
		foreach($finder->directories() as $value)
		{
			//$output->writeln($value);
		}
		
		//var_export(pathinfo("img/social/64/"));
	}
	
	protected function createPageForDir($path)
	{
		$finder = new Finder();
		$finder->in(__DIR__.'/../'.$path);
		$pathinfo = pathinfo($path);
		$current = $pathinfo['filename'];
		$parent = $pathinfo['dirname'];
	}
}

class Application extends BaseApplication
{
	protected function getDefaultCommands()
	{
		$commands = parent::getDefaultCommands();
		array_unshift($commands, new CreateCommand());
		
		return $commands;
	}
}


$app = new Application();
$app->run();

