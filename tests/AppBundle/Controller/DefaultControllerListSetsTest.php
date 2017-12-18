<?php
namespace Oaipmh\Server\Tests\Controller;

class DefaultControllerListSetsTest extends DefaultControllerAbstractTest
{
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
}
