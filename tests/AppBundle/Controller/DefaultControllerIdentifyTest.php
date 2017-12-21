<?php
/**
 * This file is part of the GeRDI software suite
 *
 * @author  Tobias Weber <weber@lrz.de>
 * @license https://www.apache.org/licenses/LICENSE-2.0
 */
namespace AppBundle\Tests\Controller;

class DefaultControllerIdentifyTest extends DefaultControllerAbstractTest
{
    /**
     * test a normal and valid Identify request
     */
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

    /**
     * test whether badArgument reply is triggered if a random param is set
     */
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
