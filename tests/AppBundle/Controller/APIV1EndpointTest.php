<?php

namespace Tests\AppBundle\Controller;

use Tests\AppBundle\DatabaseTestCase;

class APIV1EndpointTest extends DatabaseTestCase
{
    public function testEndpointGETEmptyEmails()
    {
        $client = static::createClient();
        $crawler = $client->request('GET', static::$kernel->getContainer()->get('router')->generate('api_v1_emails'));

        $response = json_decode($client->getResponse()->getContent());
        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $this->assertEquals('success', $response->status);
        $this->assertEquals(0, $response->count);
        $this->assertEmpty($response->results);
    }

    public function testEndpointGETEmptyEmail()
    {
        $client = static::createClient();
        $crawler = $client->request('GET', static::$kernel->getContainer()->get('router')->generate('api_v1_email_record', ['id' => 1]));

        $this->assertEquals(404, $client->getResponse()->getStatusCode());
    }

    public function testEndpointPOSTEmails()
    {
        $client = static::createClient();
        $crawler = $client->request('POST', static::$kernel->getContainer()->get('router')->generate('api_v1_emails'));
        $crawler = $client->request('POST', static::$kernel->getContainer()->get('router')->generate('api_v1_emails'));

        $response = $client->getResponse();
        $this->assertEquals(201, $client->getResponse()->getStatusCode());
        $this->assertEquals(static::$kernel->getContainer()->get('router')->generate('api_v1_email_record', ['id' => 2]), $response->headers->get('location'));
    }

    public function testEndpointGETEmailsAfterCreating()
    {
        self::testEndpointPOSTEmails();

        $client = static::createClient();
        $crawler = $client->request('GET', static::$kernel->getContainer()->get('router')->generate('api_v1_emails'));

        $response = json_decode($client->getResponse()->getContent());
        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $this->assertEquals('success', $response->status);
        $this->assertEquals(2, $response->count);
        $this->assertEquals(2, $response->results[1]->id);
    }

    public function testEndpointPATCHEmailAfterCreating()
    {
        $subject_test_phrase = 'test subject';
        self::testEndpointPOSTEmails();

        // make changes
        $client = static::createClient();
        $crawler = $client->request('PATCH', static::$kernel->getContainer()->get('router')->generate('api_v1_email_record', ['id' => 1]), [
            'subject' => $subject_test_phrase,
        ]);

        $response = json_decode($client->getResponse()->getContent());
        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $this->assertEquals('success', $response->status);

        // check changes
        $client = static::createClient();
        $crawler = $client->request('GET', static::$kernel->getContainer()->get('router')->generate('api_v1_email_record', ['id' => 1]));

        $response = json_decode($client->getResponse()->getContent());
        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $this->assertEquals('success', $response->status);
        $this->assertEquals($subject_test_phrase, $response->results->subject);
    }

    public function testEndpointDELETEEmailAfterCreating()
    {
        self::testEndpointPOSTEmails();

        // delete
        $client = static::createClient();
        $crawler = $client->request('DELETE', static::$kernel->getContainer()->get('router')->generate('api_v1_email_record', ['id' => 1]));

        $response = json_decode($client->getResponse()->getContent());
        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $this->assertEquals('success', $response->status);

        // check if exist
        $client = static::createClient();
        $crawler = $client->request('GET', static::$kernel->getContainer()->get('router')->generate('api_v1_email_record', ['id' => 1]));

        $this->assertEquals(404, $client->getResponse()->getStatusCode());
    }

        public function testEndpointPOSTEmailsSendAfterCreating()
        {
            self::testEndpointPOSTEmails();

            // make changes
            $client = static::createClient();
            $crawler = $client->request('PATCH', static::$kernel->getContainer()->get('router')->generate('api_v1_email_record', ['id' => 1]), [
                'sender' => 'sender@test.lc',
                'recipients' => [
                    'recipient@test.lc',
                    'recipient@test.lc',
                ],
                'subject' => 'test subject',
                'body' => 'test body',
                'provider' => 'smtp',
            ]);

            // send emails
            $client = static::createClient();
            $crawler = $client->request('POST', static::$kernel->getContainer()->get('router')->generate('api_v1_emails_send'));

            $response = json_decode($client->getResponse()->getContent());
            $this->assertEquals(200, $client->getResponse()->getStatusCode());
            $this->assertEquals('success', $response->status);
            $this->assertEquals(2, $response->count);
            $this->assertEquals(1, $response->results->count_sent);
            $this->assertCount(1, $response->message);
        }
}
