<?php
/**
 * This file is part of the GeRDI software suite
 *
 * @author  Tobias Weber <weber@lrz.de>
 * @license https://www.apache.org/licenses/LICENSE-2.0
 */
namespace AppBundle\Tests\Controller;

class DefaultControllerTest extends DefaultControllerAbstractTest
{
    /**
     * Test whether badVerb reply is triggered if a non-valid verb is given
     */
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

    /**
     * Test whether badVerb is triggered if no verb is given
     */
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
