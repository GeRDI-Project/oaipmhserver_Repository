<?php
namespace Oaipmh\Server\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use \DOMDocument;

class DefaultControllerTest extends WebTestCase
{
    public function testBadVerbValidatesGet()
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/?verb=tralala');
        $xml = new DOMDocument();
        $xml->loadXML($client->getResponse()->getContent());
        $this->assertTrue($xml->schemaValidate('tests/Resources/oaipmhResponse.xsd'));
    }

    public function testNoVerbValidatesGet()
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/');
        $xml = new DOMDocument();
        $xml->loadXML($client->getResponse()->getContent());
        $this->assertTrue($xml->schemaValidate('tests/Resources/oaipmhResponse.xsd'));
    }

    public function testContentType()
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/');
        $this->assertTrue(
            $client->getResponse()->headers->contains(
                'content-type',
                'text/xml; charset=UTF-8'
            ),
            "Wrong or no Content-Type in response"
        );
    }

    public function testIdentifyValidatesGet()
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/?verb=Identify');
        $xml = new DOMDocument();
        $xml->loadXML($client->getResponse()->getContent());
        $this->assertTrue($xml->schemaValidate('tests/Resources/oaipmhResponse.xsd'));
    }
    public function testBadVerbValidatesPost()
    {
        $this->postData = array(
            'verb'  => "tralala",
        );
        $client = static::createClient();
        $crawler = $client->request('POST', '/', $this->postData);
        $xml = new DOMDocument();
        $xml->loadXML($client->getResponse()->getContent());
        $this->assertTrue($xml->schemaValidate('tests/Resources/oaipmhResponse.xsd'));
    }

    public function testNoVerbValidatesPost()
    {
        $client = static::createClient();
        $crawler = $client->request('POST', '/');
        $xml = new DOMDocument();
        $xml->loadXML($client->getResponse()->getContent());
        $this->assertTrue($xml->schemaValidate('tests/Resources/oaipmhResponse.xsd'));
    }

    public function testIdentifyValidatesPost()
    {
        $this->postData = array(
            'verb'  => "Identify",
        );
        $client = static::createClient();
        $crawler = $client->request('GET', '/', $this->postData);
        $xml = new DOMDocument();
        $xml->loadXML($client->getResponse()->getContent());
        $this->assertTrue($xml->schemaValidate('tests/Resources/oaipmhResponse.xsd'));
    }

    public function testListSets()
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/?verb=ListSets');
        $xml = new DOMDocument();
        $xml->loadXML($client->getResponse()->getContent());
        $this->assertTrue($xml->schemaValidate('tests/Resources/oaipmhResponse.xsd'));
    }
}
