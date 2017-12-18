<?php
namespace Oaipmh\Server\Tests\Controller;

class DefaultControllerListRecordsTest extends DefaultControllerAbstractTest
{
    public function testListRecordsMin()
    {
        $queryData = array(
            'verb'  => "ListRecords",
            'metadataPrefix' => 'oai_dc',
        );
        $contents = $this->getGetAndPost("/", $queryData);
        $this->genericResponseCheck($contents);
        $this->assertTrue(
            $this->checkXpathReturnsExactly(
                '/o:OAI-PMH/o:ListRecords/o:record',
                $contents,
                3
            ),
            "Answer does not include exactly three record tags"
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
                '/o:OAI-PMH/o:ListRecords/o:record',
                $contents
            ),
            "Answer does not include exactly one record tag"
        );
    }

    public function testListRecordsFromLong()
    {
        $queryData = array(
            'verb'  => "ListRecords",
            'metadataPrefix' => 'oai_dc',
            'from'  => '2017-09-09T12:00:00Z',
        );
        $contents = $this->getGetAndPost("/", $queryData);
        $this->genericResponseCheck($contents);
        $this->assertTrue(
            $this->checkXpathReturnsExactly(
                '/o:OAI-PMH/o:ListRecords/o:record',
                $contents,
                2
            ),
            "Answer does not include exactly two record tags"
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
     * date must be valid
     */
    public function testListRecordsbadArgument4()
    {
        $queryData = array(
            'verb'  => "ListRecords",
            'metadataPrefix' => 'oai_dc',
            'until' => '2:3:4B8-9-10Z',
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
}
