<?php
/**
 * This file is part of the GeRDI software suite
 *
 * @author  Tobias Weber <weber@lrz.de>
 * @license https://www.apache.org/licenses/LICENSE-2.0
 */
namespace AppBundle\Tests\Controller;

class DefaultControllerGetRecordTest extends DefaultControllerAbstractTest
{
    /**
     * test a normal query
     */
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

    /**
     * test whether badArgument reply is triggered when no metadataPrefix is given
     */
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

    /**
     * test whether badArgument reply is triggered when random param is given
     */
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

    /**
     * test whether cannotDisseminateFormat reply is triggered if the item
     * has no record in the test database
     */
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

    /**
     * test whether idDoesNotExist reply is triggered by a well-formed identifier
     * but no corresponding item in the test database
     */
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

    /**
     * test whether idDoesNotExist reply is triggered by a not well-formed identifier
     */
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
