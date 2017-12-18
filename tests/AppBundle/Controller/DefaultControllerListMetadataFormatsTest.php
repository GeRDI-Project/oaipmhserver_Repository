<?php
namespace Oaipmh\Server\Tests\Controller;

class DefaultControllerListMetadataFormatsTest extends DefaultControllerAbstractTest
{
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
            'identifier' => 'oai:www.alpendac.eu:4',
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
}
