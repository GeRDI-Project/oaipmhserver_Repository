<?php
namespace Oaipmh\Server\Tests\Controller;

class DefaultControllerListIdentifiersMinTest extends DefaultControllerAbstractTest
{
    public function testListIdentifiersMin()
    {
        $queryData = array(
            'verb'  => "ListIdentifiers",
            'metadataPrefix' => 'oai_dc',
        );
        $contents = $this->getGetAndPost("/", $queryData);
        $this->genericResponseCheck($contents);
        $this->assertTrue(
            $this->checkXpathReturnsExactly(
                '/o:OAI-PMH/o:ListIdentifiers/o:header',
                $contents,
                3
            ),
            "Answer does not include exactly three header tags"
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
                '/o:OAI-PMH/o:ListIdentifiers/o:header',
                $contents
            ),
            "Answer does not include exactly one header tag"
        );
    }

    public function testListIdentifiersFromLong()
    {
        $queryData = array(
            'verb'  => "ListIdentifiers",
            'metadataPrefix' => 'oai_dc',
            'from'  => '2017-09-09T12:00:00Z',
        );
        $contents = $this->getGetAndPost("/", $queryData);
        $this->genericResponseCheck($contents);
        $this->assertTrue(
            $this->checkXpathReturnsExactly(
                '/o:OAI-PMH/o:ListIdentifiers/o:header',
                $contents,
                2
            ),
            "Answer does not include exactly two header tags"
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
     * date must be valid
     */
    public function testListIdentifiersbadArgument4()
    {
        $queryData = array(
            'verb'  => "ListIdentifiers",
            'metadataPrefix' => 'oai_dc',
            'until' => '2017-33-12asbdasd',
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
            'verb' => 'ListIdentifiers',
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
            'metadataPrefix' => 'oai_dc',
            'until' => '1970-12-10',
        );
        $contents = $this->getGetAndPost("/", $queryData);
        $this->genericResponseCheck($contents);
        $this->assertTrue(
            $this->checkXpathReturnsExactly(
                '/o:OAI-PMH/o:error[@code="noRecordsMatch"]',
                $contents,
                0
            ),
            "Answer does not include exactly one error tag with code 'noRecordsMatch'",
            0
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
}
