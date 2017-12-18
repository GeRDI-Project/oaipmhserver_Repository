<?php
namespace Oaipmh\Server\Tests\Controller;

class DefaultControllerIdentifyTest extends DefaultControllerAbstractTest
{
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
}
