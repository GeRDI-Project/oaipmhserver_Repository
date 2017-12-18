<?php
namespace Oaipmh\Server\Tests\Controller;

class DefaultControllerTest extends DefaultControllerAbstractTest
{
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
    */
}
