<?php
/**
 * This file is part of the GeRDI software suite
 *
 * @author  Tobias Weber <weber@lrz.de>
 * @license https://www.apache.org/licenses/LICENSE-2.0
 */
namespace AppBundle\Tests\Controller;

class DefaultControllerListMetadataFormatsTest extends DefaultControllerAbstractTest
{
    /**
     * Check whether a valid minimal request returns successfully
     *
     * @todo If we start to support more metadata schemes we have to rewrite this test
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

    /**
     * test whether a valid request for metadataFormats for a specific identifier returns successfully.
     *
     *
     * @todo If we start to support more metadata schemes we have to rewrite this test
     */
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

    /**
     * Check whether idDoesNotExist is triggered if a not well-defined identifier is given
     */
    public function testListMetadataFormatsIdDoesNotExist1()
    {
        $queryData = array(
            'verb'  => "ListMetadataFormats",
            'identifier' => 'asdjbaskdbaskjdbkajsb',
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
     * Check whether idDoesNotExist is triggered if a well-defined but not-existing identifier is given
     */
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

    /**
     * Check whether noMetadataFormats reply is triggered if an item without record in the testdatabase is requested
     */
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
