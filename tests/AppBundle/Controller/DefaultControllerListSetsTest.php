<?php
/**
 * This file is part of the GeRDI software suite
 *
 * @author  Tobias Weber <weber@lrz.de>
 * @license https://www.apache.org/licenses/LICENSE-2.0
 */
namespace AppBundle\Tests\Controller;

class DefaultControllerListSetsTest extends DefaultControllerAbstractTest
{
    /**
     * Test whether noSetHierarchy reply is triggered if ListSets is requested
     *
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
    
    /**
     * Test whether badArgument reply is triggered if a random param is given
     */
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
}
