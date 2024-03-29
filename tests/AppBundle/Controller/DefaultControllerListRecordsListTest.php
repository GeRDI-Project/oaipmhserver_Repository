<?php
/**
 * This file is part of the GeRDI software suite
 *
 * @author  Tobias Weber <weber@lrz.de>
 * @license https://www.apache.org/licenses/LICENSE-2.0
 */
namespace AppBundle\Tests\Controller;

class DefaultControllerListRecordsTest extends DefaultControllerAbstractTest
{
    /**
     * Test a valid minimal ListRecords request for which 3 items are in the
     * test database
     */
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
                20
            ),
            "Answer does not include exactly twenty record tags"
        );
    }

    /**
     * Test a valid request with short dates.
     * 1 item in the testdatabase should match
     */
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
            $this->checkXpathReturnsExactly(
                '/o:OAI-PMH/o:ListRecords/o:record',
                $contents,
                18
            ),
            "Answer does not include exactly eighteen record tag"
        );
    }

    /**
     * Test a valid request with only one date in long format.
     * 2 items in the test database should match
     */
    public function testListRecordsFromLong()
    {
        $queryData = array(
            'verb'  => "ListRecords",
            'metadataPrefix' => 'oai_dc',
            'from'  => '2018-07-01T12:00:00Z',
        );
        $contents = $this->getGetAndPost("/", $queryData);
        $this->genericResponseCheck($contents);
        $this->assertTrue(
            $this->checkXpathReturnsExactly(
                '/o:OAI-PMH/o:ListRecords/o:record',
                $contents,
                1
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

    /**
     * Test with an invalid resumptionToken
     */
    public function testListRecordsBadResumptionToken()
    {
        $queryData = array(
            'verb' => 'ListRecords',
            'resumptionToken' => 'token123',
        );
        $contents = $this->getGetAndPost("/", $queryData);
        $this->genericResponseCheck($contents);
        $this->assertTrue(
            $this->checkXpathReturnsExactlyOne(
                '/o:OAI-PMH/o:error[@code="badResumptionToken"]',
                $contents
            ),
            "Invalid resumptionToken"
        );
    }


    /**
     * Test with a valid resumptionToken
     */
    public function testListRecordsValidResumptionToken()
    {
        $queryData = array(
            'verb' => 'ListRecords',
            'resumptionToken' => '{"reqParams"%3A{"verb"%3A"ListRecords"%2C"metadataPrefix"%3A"oai_dc"}%2C"offset"%3A"21"%2C"cursor"%3A0}',
        );
        $contents = $this->getGetAndPost("/", $queryData);
        $this->genericResponseCheck($contents);
        $this->assertTrue(
            $this->checkXpathReturnsExactlyOne(
                '/o:OAI-PMH/o:ListRecords/o:record',
                $contents
            ),
            "Invalid resumptionToken"
        );
    }
}
