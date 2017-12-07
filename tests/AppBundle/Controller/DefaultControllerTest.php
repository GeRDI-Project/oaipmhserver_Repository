<?php
namespace Oaipmh\Server\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use \DOMDocument;

class DefaultControllerTest extends WebTestCase
{
    public function testBadVerbValidates()
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/?verb=tralala');
        $xml = new DOMDocument();
        $xml->loadXML($client->getResponse()->getContent());
        $this->assertTrue($xml->schemaValidate('tests/Resources/oaipmhResponse.xsd'));
    }

    public function testNoVerbValidates()
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/');
        $xml = new DOMDocument();
        $xml->loadXML($client->getResponse()->getContent());
        $this->assertTrue($xml->schemaValidate('tests/Resources/oaipmhResponse.xsd'));
    }

    public function testIdentifyValidates()
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/?verb=identify');
        $xml = new DOMDocument();
        $xml->loadXML($client->getResponse()->getContent());
        $this->assertTrue($xml->schemaValidate('tests/Resources/oaipmhResponse.xsd'));
    }
}
