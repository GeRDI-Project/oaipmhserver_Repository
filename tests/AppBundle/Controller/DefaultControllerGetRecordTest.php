<?php
namespace Oaipmh\Server\Tests\Controller;

class DefaultControllerGetRecordTest extends DefaultControllerAbstractTest
{
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
            'identifier' => 'oai:www.alpendac.eu:4',
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
}
