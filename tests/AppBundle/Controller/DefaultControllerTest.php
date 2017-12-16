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
            $xpath->registerNamespace("oai_dc", "http://www.openarchives.org/OAI/2.0/oai_dc/");
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

    public function testIdentify()
    {
        $queryData = array(
            'verb'  => "Identify",
        );
        $contents = $this->getGetAndPost("/", $queryData);
        $this->genericResponseCheck($contents);
        $this->assertTrue(
            $this->checkXpathReturnsExactlyOne(
                '/o:OAI-PMH/o:Identify',
                $contents
            ),
            "Answer does not include exactly one Identify tag"
        );

    }

    public function testIdentifyBadArgument()
    {
        $queryData = array(
            'verb'  => "Identify",
            'some'  => "OtherValue",
        );
        $contents = $this->getGetAndPost("/", $queryData);
        $this->genericResponseCheck($contents);
        $this->assertTrue(
            $this->checkXpathReturnsExactlyOne(
                '/o:OAI-PMH/o:error[@code="badArgument"]',
                $contents
            ),
            "Answer does not include exactly one error tag with code='badArgument'"
        );
    }

    /**
     * @todo: add an alternative "or" in assert-Statement, when Sets are supported
     */
    public function testListSets()
    {
        $queryData = array(
            'verb'  => "ListSets",
        );
        $contents = $this->getGetAndPost("/", $queryData);
        $this->genericResponseCheck($contents);
        $this->assertTrue(
            $this->checkXpathReturnsExactlyOne(
                '/o:OAI-PMH/o:error[@code="noSetHierarchy"]',
                $contents
            ),
            "Answer does not include exactly one error tag with code='noSetHierarchy'"
        );
    }
    
    public function testListSetsBadArgument()
    {
        $queryData = array(
            'verb'  => "ListSets",
            'some'  => "otherValue",
        );
        $contents = $this->getGetAndPost("/", $queryData);
        $this->genericResponseCheck($contents);
        $this->assertTrue(
            $this->checkXpathReturnsExactlyOne(
                '/o:OAI-PMH/o:error[@code="badArgument"]',
                $contents
            ),
            "Answer does not include exactly one error tag with code='badArgument'"
        );
    }
    

    /**
     * @todo: Add public function testListSetsBadResumptionToken here when resumptionTokens are supported
     */

    public function testListMetadataFormats()
    {
        $queryData = array(
            'verb'  => "ListMetadataFormats",
        );
        $contents = $this->getGetAndPost("/", $queryData);
        $this->genericResponseCheck($contents);
        $this->assertTrue(
            $this->checkXpathReturnsExactlyOne(
                '/o:OAI-PMH/o:ListMetadataFormats',
                $contents
            ),
            "Answer does not include exactly one ListMetadataFormats tag"
        );
        
    }

    public function testListMetadataFormatsWithIdExists()
    {
        $queryData = array(
            'verb'  => "ListMetadataFormats",
            'identifier' => 'oai:www.alpendac.eu:1',
        );
        $contents = $this->getGetAndPost("/", $queryData);
        $this->genericResponseCheck($contents);
        $this->assertTrue(
            $this->checkXpathReturnsExactlyOne(
                '/o:OAI-PMH/o:ListMetadataFormats',
                $contents
            ),
            "Answer does not include exactly one ListMetadataFormats tag"
        );
    }

    public function testListMetadataFormatsIdDoesNotExist1()
    {
        $queryData = array(
            'verb'  => "ListMetadataFormats",
            'identifier' => 'oai:www.alpendac.eu:xyz',
        );
        $contents = $this->getGetAndPost("/", $queryData);
        $this->genericResponseCheck($contents);
        $this->assertTrue(
            $this->checkXpathReturnsExactlyOne(
                '/o:OAI-PMH/o:error[@code="idDoesNotExist"]',
                $contents
            ),
            "Answer does not include exactly one error tag with code 'idDoesNotExist'"
        );
    }

    public function testListMetadataFormatsIdDoesNotExist2()
    {
        $queryData = array(
            'verb'  => "ListMetadataFormats",
            'identifier' => 'oai:www.alpendac.eu:10000',
        );
        $contents = $this->getGetAndPost("/", $queryData);
        $this->genericResponseCheck($contents);
        $this->assertTrue(
            $this->checkXpathReturnsExactlyOne(
                '/o:OAI-PMH/o:error[@code="idDoesNotExist"]',
                $contents
            ),
            "Answer does not include exactly one error tag with code 'idDoesNotExist'"
        );
    }

    public function testListMetadataFormatsNoMetadataFormats()
    {
        $queryData = array(
            'verb'  => "ListMetadataFormats",
            'identifier' => 'oai:www.alpendac.eu:2',
        );
        $contents = $this->getGetAndPost("/", $queryData);
        $this->genericResponseCheck($contents);
        $this->assertTrue(
            $this->checkXpathReturnsExactlyOne(
                '/o:OAI-PMH/o:error[@code="noMetadataFormats"]',
                $contents
            ),
            "Answer does not include exactly one error tag with code 'noMetadataFormats'"
        );
    }

    public function testGetRecord()
    {
        $queryData = array(
            'verb'  => "GetRecord",
            'identifier' => 'oai:www.alpendac.eu:1',
            'metadataPrefix' => 'oai_dc',
        );
        $contents = $this->getGetAndPost("/", $queryData);
        $this->genericResponseCheck($contents);
        $this->assertTrue(
            $this->checkXpathReturnsExactlyOne(
                '/o:OAI-PMH/o:GetRecord/o:record/o:metadata/oai_dc:dc',
                $contents
            ),
            "Answer does not include exactly one oai_dc:dc tag"
        );
    }

    public function testGetRecordBadArgument1()
    {
        $queryData = array(
            'verb'  => "GetRecord",
            'identifier' => 'oai:www.alpendac.eu:1',
        );
        $contents = $this->getGetAndPost("/", $queryData);
        $this->genericResponseCheck($contents);
        $this->assertTrue(
            $this->checkXpathReturnsExactlyOne(
                '/o:OAI-PMH/o:error[@code="badArgument"]',
                $contents
            ),
            "Answer does not include exactly one error tag with code 'badArgument'"
        );
    }

    public function testGetRecordBadArgument2()
    {
        $queryData = array(
            'verb'  => "GetRecord",
            'identifier' => 'oai:www.alpendac.eu:1',
            'metadataPrefix' => 'oai_dc',
            'some'  => 'otherValue',
        );
        $contents = $this->getGetAndPost("/", $queryData);
        $this->genericResponseCheck($contents);
        $this->assertTrue(
            $this->checkXpathReturnsExactlyOne(
                '/o:OAI-PMH/o:error[@code="badArgument"]',
                $contents
            ),
            "Answer does not include exactly one error tag with code 'badArgument'"
        );
    }

    public function testGetRecordCannotDisseminateFormat()
    {
        $queryData = array(
            'verb'  => "GetRecord",
            'identifier' => 'oai:www.alpendac.eu:2',
            'metadataPrefix' => 'oai_dc',
        );
        $contents = $this->getGetAndPost("/", $queryData);
        $this->genericResponseCheck($contents);
        $this->assertTrue(
            $this->checkXpathReturnsExactlyOne(
                '/o:OAI-PMH/o:error[@code="cannotDisseminateFormat"]',
                $contents
            ),
            "Answer does not include exactly one error tag with code 'cannotDisseminateFormat'"
        );
    }

    public function testGetRecordIdDoesNotExist1()
    {
        $queryData = array(
            'verb'  => "GetRecord",
            'identifier' => 'oai:www.alpendac.eu:10000',
            'metadataPrefix' => 'oai_dc',
        );
        $contents = $this->getGetAndPost("/", $queryData);
        $this->genericResponseCheck($contents);
        $this->assertTrue(
            $this->checkXpathReturnsExactlyOne(
                '/o:OAI-PMH/o:error[@code="idDoesNotExist"]',
                $contents
            ),
            "Answer does not include exactly one error tag with code 'idDoesNotExist'"
        );
    }

    public function testGetRecordIdDoesNotExist2()
    {
        $queryData = array(
            'verb'  => "GetRecord",
            'identifier' => 'asjkdbaksdjask',
            'metadataPrefix' => 'oai_dc',
        );
        $contents = $this->getGetAndPost("/", $queryData);
        $this->genericResponseCheck($contents);
        $this->assertTrue(
            $this->checkXpathReturnsExactlyOne(
                '/o:OAI-PMH/o:error[@code="idDoesNotExist"]',
                $contents
            ),
            "Answer does not include exactly one error tag with code 'idDoesNotExist'"
        );
    }
/*
    public function testListIdentifiersMin()
    {
        $queryData = array(
            'verb'  => "ListIdentifiers",
            'metadataPrefix' => 'oai_dc',
        );
        $contents = $this->getGetAndPost("/", $queryData);
        $this->genericResponseCheck($contents);
        $this->assertTrue(
            $this->checkXpathReturnsExactlyOne(
                '/o:OAI-PMH/o:ListIdentifiers',
                $contents
            ),
            "Answer does not include exactly one ListIdentifiers tag"
        );
    }

    public function testListIdentifiersFromUntilShort()
    {
        $queryData = array(
            'verb'  => "ListIdentifiers",
            'metadataPrefix' => 'oai_dc',
            'from'  => '2017-09-09',
            'until' => '2017-12-31',
        );
        $contents = $this->getGetAndPost("/", $queryData);
        $this->genericResponseCheck($contents);
        $this->assertTrue(
            $this->checkXpathReturnsExactlyOne(
                '/o:OAI-PMH/o:ListIdentifiers',
                $contents
            ),
            "Answer does not include exactly one ListIdentifiers tag"
        );
    }

    /**
     * metadataPrefix must be set
     */
    public function testListIdentifiersbadArgument1()
    {
        $queryData = array(
            'verb'  => "ListIdentifiers",
        );
        $contents = $this->getGetAndPost("/", $queryData);
        $this->genericResponseCheck($contents);
        $this->assertTrue(
            $this->checkXpathReturnsExactlyOne(
                '/o:OAI-PMH/o:error[@code="badArgument"]',
                $contents
            ),
            "Answer does not include exactly one error tag with code 'badArgument'"
        );
    }
    
    /**
     * some=value is not allowed
     */
    public function testListIdentifiersbadArgument2()
    {
        $queryData = array(
            'verb'  => "ListIdentifiers",
            'metadataPrefix' => 'oai_dc',
            'from'  => '2017-09-09T12:00:00Z',
            'until' => '2017-12-31T23:59:59Z',
            'some'  => 'value',
        );
        $contents = $this->getGetAndPost("/", $queryData);
        $this->genericResponseCheck($contents);
        $this->assertTrue(
            $this->checkXpathReturnsExactlyOne(
                '/o:OAI-PMH/o:error[@code="badArgument"]',
                $contents
            ),
            "Answer does not include exactly one error tag with code 'badArgument'"
        );
    }

    /**
     * resumptionToken is an exclusive parameter
     */
    public function testListIdentifiersbadArgument3()
    {
        $queryData = array(
            'verb'  => "ListIdentifiers",
            'metadataPrefix' => 'oai_dc',
            'resumptionToken' => 'token123',
            'until' => '2017-12-31T23:59:59Z',
            'some'  => 'value',
        );
        $contents = $this->getGetAndPost("/", $queryData);
        $this->genericResponseCheck($contents);
        $this->assertTrue(
            $this->checkXpathReturnsExactlyOne(
                '/o:OAI-PMH/o:error[@code="badArgument"]',
                $contents
            ),
            "Answer does not include exactly one error tag with code 'badArgument'"
        );
    }

    /**
     * We do not support resumptionTokens
     * @todo If we do that, this test case must be rewritten
     * and a testcase for a successful resumption token added.
     */
    public function testListIdentifiersBadResumptionToken()
    {
        $queryData = array(
            'resumptionToken' => 'token123',
        );
        $contents = $this->getGetAndPost("/", $queryData);
        $this->genericResponseCheck($contents);
        $this->assertTrue(
            $this->checkXpathReturnsExactlyOne(
                '/o:OAI-PMH/o:error[@code="badResumptionToken"]',
                $contents
            ),
            "Answer does not include exactly one error tag with code 'badResumptionToken'"
        );
    }

    /**
     * metadataPrefis is not supported
     */
    public function testListIdentifiersCannotDisseminateFormat()
    {
        $queryData = array(
            'verb'  => "ListIdentifiers",
            'metadataPrefix' => 'oai_nonexistingschema',
        );
        $contents = $this->getGetAndPost("/", $queryData);
        $this->genericResponseCheck($contents);
        $this->assertTrue(
            $this->checkXpathReturnsExactlyOne(
                '/o:OAI-PMH/o:error[@code="cannotDisseminateFormat"]',
                $contents
            ),
            "Answer does not include exactly one error tag with code 'cannotDisseminateFormat'"
        );
    }

    /**
     *  from - until values result in an empty list
     *  @todo: if we start to support sets, we need a second version
     *  of this test to test a set request that results in an empty list.
     */
    public function testListIdentifiersNoRecordMatch1()
    {
        $queryData = array(
            'verb'  => "ListIdentifiers",
            'metadataPrefix' => 'oai_nonexistingschema',
        );
        $contents = $this->getGetAndPost("/", $queryData);
        $this->genericResponseCheck($contents);
        $this->assertTrue(
            $this->checkXpathReturnsExactlyOne(
                '/o:OAI-PMH/o:error[@code="noRecordsMatch"]',
                $contents
            ),
            "Answer does not include exactly one error tag with code 'noRecordsMatch'"
        );
    }

    /**
     *  We do not support sets (this test can be deleted
     *  when we start doing so)
     */
    public function testListIdentifiersNoSetHierarchy()
    {
        $queryData = array(
            'verb'  => "ListIdentifiers",
            'metadataPrefix' => 'oai_dc',
            'set' => 'setTag',
        );
        $contents = $this->getGetAndPost("/", $queryData);
        $this->genericResponseCheck($contents);
        $this->assertTrue(
            $this->checkXpathReturnsExactlyOne(
                '/o:OAI-PMH/o:error[@code="noSetHierarchy"]',
                $contents
            ),
            "Answer does not include exactly one error tag with code 'noSetHierarchy'"
        );
    }

    public function testListRecordsMin()
    {
        $queryData = array(
            'verb'  => "ListRecords",
            'metadataPrefix' => 'oai_dc',
        );
        $contents = $this->getGetAndPost("/", $queryData);
        $this->genericResponseCheck($contents);
        $this->assertTrue(
            $this->checkXpathReturnsExactlyOne(
                '/o:OAI-PMH/o:ListRecords',
                $contents
            ),
            "Answer does not include exactly one ListRecords tag"
        );
    }

    public function testListRecordsFromUntilShort()
    {
        $queryData = array(
            'verb'  => "ListRecords",
            'metadataPrefix' => 'oai_dc',
            'from'  => '2017-09-09',
            'until' => '2017-12-31',
        );
        $contents = $this->getGetAndPost("/", $queryData);
        $this->genericResponseCheck($contents);
        $this->assertTrue(
            $this->checkXpathReturnsExactlyOne(
                '/o:OAI-PMH/o:ListRecords',
                $contents
            ),
            "Answer does not include exactly one ListRecords tag"
        );
    }

    public function testListRecordsFromUntilLong()
    {
        $queryData = array(
            'verb'  => "ListRecords",
            'metadataPrefix' => 'oai_dc',
            'from'  => '2017-09-09T12:00:00Z',
            'until' => '2017-12-31T23:59:59Z',
        );
        $contents = $this->getGetAndPost("/", $queryData);
        $this->genericResponseCheck($contents);
        $this->assertTrue(
            $this->checkXpathReturnsExactlyOne(
                '/o:OAI-PMH/o:ListRecords',
                $contents
            ),
            "Answer does not include exactly one ListRecords tag"
        );
    }

    /**
     * metadataPrefix must be set
     */
    public function testListRecordsbadArgument1()
    {
        $queryData = array(
            'verb'  => "ListRecords",
        );
        $contents = $this->getGetAndPost("/", $queryData);
        $this->genericResponseCheck($contents);
        $this->assertTrue(
            $this->checkXpathReturnsExactlyOne(
                '/o:OAI-PMH/o:error[@code="badArgument"]',
                $contents
            ),
            "Answer does not include exactly one error tag with code 'badArgument'"
        );
    }
    
    /**
     * some=value is not allowed
     */
    public function testListRecordsbadArgument2()
    {
        $queryData = array(
            'verb'  => "ListRecords",
            'metadataPrefix' => 'oai_dc',
            'from'  => '2017-09-09T12:00:00Z',
            'until' => '2017-12-31T23:59:59Z',
            'some'  => 'value',
        );
        $contents = $this->getGetAndPost("/", $queryData);
        $this->genericResponseCheck($contents);
        $this->assertTrue(
            $this->checkXpathReturnsExactlyOne(
                '/o:OAI-PMH/o:error[@code="badArgument"]',
                $contents
            ),
            "Answer does not include exactly one error tag with code 'badArgument'"
        );
    }

    /**
     * resumptionToken is an exclusive parameter
     */
    public function testListRecordsbadArgument3()
    {
        $queryData = array(
            'verb'  => "ListRecords",
            'metadataPrefix' => 'oai_dc',
            'resumptionToken' => 'token123',
            'until' => '2017-12-31T23:59:59Z',
            'some'  => 'value',
        );
        $contents = $this->getGetAndPost("/", $queryData);
        $this->genericResponseCheck($contents);
        $this->assertTrue(
            $this->checkXpathReturnsExactlyOne(
                '/o:OAI-PMH/o:error[@code="badArgument"]',
                $contents
            ),
            "Answer does not include exactly one error tag with code 'badArgument'"
        );
    }

    /**
     * We do not support resumptionTokens
     * @todo If we do that, this test case must be rewritten
     * and a testcase for a successful resumption token added.
     */
    public function testListRecordsBadResumptionToken()
    {
        $queryData = array(
            'resumptionToken' => 'token123',
        );
        $contents = $this->getGetAndPost("/", $queryData);
        $this->genericResponseCheck($contents);
        $this->assertTrue(
            $this->checkXpathReturnsExactlyOne(
                '/o:OAI-PMH/o:error[@code="badResumptionToken"]',
                $contents
            ),
            "Answer does not include exactly one error tag with code 'badResumptionToken'"
        );
    }

    /**
     * metadataPrefis is not supported
     */
    public function testListRecordsCannotDisseminateFormat()
    {
        $queryData = array(
            'verb'  => "ListRecords",
            'metadataPrefix' => 'oai_nonexistingschema',
        );
        $contents = $this->getGetAndPost("/", $queryData);
        $this->genericResponseCheck($contents);
        $this->assertTrue(
            $this->checkXpathReturnsExactlyOne(
                '/o:OAI-PMH/o:error[@code="cannotDisseminateFormat"]',
                $contents
            ),
            "Answer does not include exactly one error tag with code 'cannotDisseminateFormat'"
        );
    }

    /**
     *  from - until values result in an empty list
     *  @todo: if we start to support sets, we need a second version
     *  of this test to test a set request that results in an empty list.
     */
    public function testListRecordsNoRecordMatch1()
    {
        $queryData = array(
            'verb'  => "ListRecords",
            'metadataPrefix' => 'oai_nonexistingschema',
        );
        $contents = $this->getGetAndPost("/", $queryData);
        $this->genericResponseCheck($contents);
        $this->assertTrue(
            $this->checkXpathReturnsExactlyOne(
                '/o:OAI-PMH/o:error[@code="noRecordsMatch"]',
                $contents
            ),
            "Answer does not include exactly one error tag with code 'noRecordsMatch'"
        );
    }

    /**
     *  We do not support sets (this test can be deleted
     *  when we start doing so)
     */
    public function testListRecordsNoSetHierarchy()
    {
        $queryData = array(
            'verb'  => "ListRecords",
            'metadataPrefix' => 'oai_dc',
            'set' => 'setTag',
        );
        $contents = $this->getGetAndPost("/", $queryData);
        $this->genericResponseCheck($contents);
        $this->assertTrue(
            $this->checkXpathReturnsExactlyOne(
                '/o:OAI-PMH/o:error[@code="noSetHierarchy"]',
                $contents
            ),
            "Answer does not include exactly one error tag with code 'noSetHierarchy'"
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
