<?php
namespace Oaipmh\Server\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use \DOMDocument;
use \DOMXpath;

class DefaultControllerTest extends WebTestCase
{
    public function getGetAndPost(string $path, array $queryData)
    {
        $client = static::createClient();
        $crawler = $client->request('GET', "$path?" . http_build_query($queryData));
        $retval["getResponse"] = $client->getResponse();
        $crawler = $client->request('POST', "$path", $queryData);
        $retval["postResponse"] = $client->getResponse();
        return $retval;
    }

    public function validateResponseContent(array $contents)
    {
        $xml = new DOMDocument();
        foreach ($contents as $content) {
            $xml->loadXML($content->getContent());
            if (!$xml->schemaValidate('tests/Resources/oaipmhResponse.xsd')) {
                return false;
            }
        }
        return true;
    }

    public function validateContentType(array $contents)
    {
        foreach ($contents as $content) {
            if (!$content->headers->contains(
                'content-type',
                'text/xml; charset=UTF-8'
            )) {
                return false;
            }
        }
        return true;
    }

    public function genericResponseCheck($contents)
    {
        $this->assertTrue(
            $this->validateResponseContent($contents),
            "Response is not valid according to xsd"
        );
        $this->assertTrue(
            $this->validateContentType($contents),
            "Wrong or no Content-Type in response"
        );
    }

    public function checkXpathReturnsExactlyOne(string $xpathQuery, array $contents)
    {
        $xml = new DOMDocument();
        foreach ($contents as $content) {
            if (! $xml->loadXML($content->getContent())) {
                return false;
            }
            $xpath = new DOMXpath($xml);
            $xpath->registerNamespace("o", "http://www.openarchives.org/OAI/2.0/");
            $result = $xpath->query($xpathQuery, $xml->documentElement);
            if ($result->length != 1) {
                return false;
            }
            return true;
        }
    }

    public function testBadVerb()
    {
        $queryData = array(
            'verb'  => "tralala",
        );
        $contents = $this->getGetAndPost("/", $queryData);
        $this->genericResponseCheck($contents);
        $this->assertTrue(
            $this->checkXpathReturnsExactlyOne(
                '/o:OAI-PMH/o:error[@code="badVerb"]',
                $contents
            ),
            "Answer does not include exactly one error tag with code='badVerb'"
        );
    }

    public function testNoVerb()
    {
        $queryData = array();
        $contents = $this->getGetAndPost("/", $queryData);
        $this->genericResponseCheck($contents);
        $this->assertTrue(
            $this->checkXpathReturnsExactlyOne(
                '/o:OAI-PMH/o:error[@code="badVerb"]',
                $contents
            ),
            "Answer does not include exactly one error tag with code='badVerb'"
        );
    }

    public function testIdentifyValidatesGet()
    {
        $queryData = array(
            'verb'  => "Identify",
        );
        $contents = $this->getGetAndPost("/", $queryData);
        $this->genericResponseCheck($contents);
    }

/* @todo clarify whether this really violates the standard
    public function testMultipleVerbsValidatesGet()
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/?verb=ListIdentifiers&verb=ListMetadataFormats');
        $xml = new DOMDocument();
        $xml->loadXML($client->getResponse()->getContent());
        $this->assertTrue($xml->schemaValidate('tests/Resources/oaipmhResponse.xsd'));
        $this->assertGreaterThan(
            0,
            $crawler->filter('xml:contains("badVerb")')->count()
        );
    }





    public function testIdentifyBadArgumentGet()
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/?verb=Identify&someother=value');
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

    public function testListMetadataFormatsGet()
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/?verb=ListMetadataFormats');
        $xml = new DOMDocument();
        $xml->loadXML($client->getResponse()->getContent());
        $this->assertTrue($xml->schemaValidate('tests/Resources/oaipmhResponse.xsd'));
    }

    public function testListMetadataFormats()
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/?verb=ListMetadataFormats');
        $xml = new DOMDocument();
        $xml->loadXML($client->getResponse()->getContent());
        $this->assertTrue($xml->schemaValidate('tests/Resources/oaipmhResponse.xsd'));
    }

    public function testListMetadataFormatsWithIdExists()
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/?verb=ListMetadataFormats&identifier=oai:example.de:1');
        $xml = new DOMDocument();
        $xml->loadXML($client->getResponse()->getContent());
        $this->assertTrue($xml->schemaValidate('tests/Resources/oaipmhResponse.xsd'));
    }

    public function testListMetadataFormatsBadArgument()
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/?verb=ListMetadataFormats&identifier=oai:example.de:1&tritra=trullala');
        $xml = new DOMDocument();
        $xml->loadXML($client->getResponse()->getContent());
        $this->assertTrue($xml->schemaValidate('tests/Resources/oaipmhResponse.xsd'));
        var_dump($crawler->filter('xml:contains("badArgument")'));
        $this->assertGreaterThan(
            0,
            $crawler->filter('xml:contains("badArgument")')->count()
        );
    
    }

    public function testListMetadataFormatsWithIdNotExists()
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/?verb=ListMetadataFormats&identifier=oai:example.de:bliblablu');
        $xml = new DOMDocument();
        $xml->loadXML($client->getResponse()->getContent());
        $this->assertTrue($xml->schemaValidate('tests/Resources/oaipmhResponse.xsd'));
    }

    public function testGetRecordExists()
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/?verb=GetRecord&identifier=oai:example.de:1&metadaPrefix=oai_dc');
        $xml = new DOMDocument();
        $xml->loadXML($client->getResponse()->getContent());
        $this->assertTrue($xml->schemaValidate('tests/Resources/oaipmhResponse.xsd'));
    }

    public function testGetRecordNotExists()
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/?verb=GetRecord&identifier=oai:example.de:bliblablu&metadaPrefix=oai_dc');
        $xml = new DOMDocument();
        $xml->loadXML($client->getResponse()->getContent());
        $this->assertTrue($xml->schemaValidate('tests/Resources/oaipmhResponse.xsd'));
    }

    public function testGetRecordMetadataFormatNotExists()
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/?verb=GetRecord&identifier=oai:example.de:1&metadaPrefix=oai_notexists');
        $xml = new DOMDocument();
        $xml->loadXML($client->getResponse()->getContent());
        $this->assertTrue($xml->schemaValidate('tests/Resources/oaipmhResponse.xsd'));
    }

    public function testListIdentifieres()
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/?verb=ListIdentifiers');
        $xml = new DOMDocument();
        $xml->loadXML($client->getResponse()->getContent());
        $this->assertTrue($xml->schemaValidate('tests/Resources/oaipmhResponse.xsd'));
    }

    public function testListRecords()
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/?verb=ListRecords');
        $xml = new DOMDocument();
        $xml->loadXML($client->getResponse()->getContent());
        $this->assertTrue($xml->schemaValidate('tests/Resources/oaipmhResponse.xsd'));
    }
 */
}
