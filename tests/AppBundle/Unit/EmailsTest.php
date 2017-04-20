<?php

namespace Tests\AppBundle\Unit;

use Tests\AppBundle\DatabaseTestCase;
use AppBundle\Entity\Email;

class EmailsTest extends DatabaseTestCase
{
    public function testGetEmails()
    {
        $email_repository = $this->em->getRepository('AppBundle:Email');

        $email = new Email();
        $email->setSubject('test subject');
        $this->em->persist($email);
        $this->em->flush();

        $results = $email_repository->findBySubject('test subject');
        $this->assertEquals(1, count($results));
    }
}
