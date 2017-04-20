<?php

namespace Tests\AppBundle;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\NullOutput;

abstract class DatabaseTestCase extends WebTestCase
{
    protected $em;
    protected $kernel_service;

    public function setUp()
    {
        self::bootKernel();
        // getting Doctrine manager
        $this->em = static::$kernel->getContainer()->get('doctrine')->getManager();

        // getting Kernel service
        $this->kernel_service = static::$kernel->getContainer()->get('kernel');

        // dropping test database
        $application = new Application($this->kernel_service);
        $application->setAutoExit(false);
        $input = new ArrayInput([
            'command' => 'doctrine:schema:drop',
            '--force' => true,
        ]);
        $application->run($input, new NullOutput());

        // creating test database
        $application = new Application($this->kernel_service);
        $application->setAutoExit(false);
        $input = new ArrayInput([
            'command' => 'doctrine:schema:create',
        ]);
        $application->run($input, new NullOutput());
    }

    protected function tearDown()
    {
        parent::tearDown();

        $this->em->close();
    }
}
