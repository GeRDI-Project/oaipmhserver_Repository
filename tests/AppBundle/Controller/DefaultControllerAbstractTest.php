<?php
/**
 * This file is part of the GeRDI software suite
 *
 * @author  Tobias Weber <weber@lrz.de>
 * @license https://www.apache.org/licenses/LICENSE-2.0
 */
namespace AppBundle\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use \DOMDocument;
use \DOMXpath;

/**
 * Abstract class as intermediate between WebTestCase and the specific
 * case has no abstract methods, but is abstract to hinder initialization
 * without test classes 
 */
abstract class DefaultControllerAbstractTest extends WebTestCase
{
    /**
     * Retrieves both a GET and a POST request.
     *
     * @param string $path URL-path to be requested
     * @param array $queryData Params to be send with the request
     *
     * @return array Array items are of type Symfony\Component\HttpFoundation\Response (keys "getResponse" and "postResponse")
     */
    public function getGetAndPost(string $path, array $queryData)
    {
        $client = static::createClient();
        $crawler = $client->request('GET', "$path?" . http_build_query($queryData));
        $retval["getResponse"] = $client->getResponse();
        $crawler = $client->request('POST', "$path", $queryData);
        $retval["postResponse"] = $client->getResponse();
        return $retval;
    }

    /**
     * Checks whether the contents of the given array validate against the
     * OAI-PMH Response xsd. The given xsd might include other xsd (e.g.
     * dublin core validation)
     * 
     * @see https://www.openarchives.org/OAI/openarchivesprotocol.html#OAIPMHschema
     *
     * @param array An associative array, values are of type Symfony\Component\HttpFoundation\Response
     *
     * @return bool
     */
    public function validateResponseContent(array $contents)
    {
        $xml = new DOMDocument();
        foreach ($contents as $content) {
            $xml->loadXML($content->getContent());
            if (!$xml->schemaValidate('tests/Resources/oaipmhResponse.xsd')) {
                return false;
            }
        }
        return true;
    }
 
    /**
     * Checks whether the http response header has the correct contenttype
     *
     * @see https://www.openarchives.org/OAI/openarchivesprotocol.html#HTTPResponseFormat 
     *
     *
     * @param array An associative array, the values are of type Symfony\Component\HttpFoundation\Respons
     * return bool
     */
    public function validateContentType(array $contents)
    {
        foreach ($contents as $content) {
            if (!$content->headers->contains(
                'content-type',
                'text/xml; charset=UTF-8'
            )) {
                return false;
            }
        }
        return true;
    }
 
    /**
     * This method should be called by all tests it includes all checks
     * that should be made for all testcases
     *
     * @param array An associative array, the values are of type Symfony\Component\HttpFoundation\Respons
     */
    public function genericResponseCheck($contents)
    {
        $this->assertTrue(
            $this->validateResponseContent($contents),
            "Response is not valid according to xsd"
        );
        $this->assertTrue(
            $this->validateContentType($contents),
            "Wrong or no Content-Type in response"
        );
    }

    /**
     * Helper function to validate the number of items returned by a XPATH query
     *
     * @param string $xpathQuery Should be executed on all members of $contents
     * @param array An associative array, the values are of type Symfony\Component\HttpFoundation\Respons
     * @param int $number Number that should be matched
     *
     * @return bool
     */
    public function checkXpathReturnsExactly(string $xpathQuery, array $contents, int $number)
    {
        $xml = new DOMDocument();
        foreach ($contents as $content) {
            if (! $xml->loadXML($content->getContent())) {
                return false;
            }
            $xpath = new DOMXpath($xml);
            $xpath->registerNamespace("o", "http://www.openarchives.org/OAI/2.0/");
            $xpath->registerNamespace("oai_dc", "http://www.openarchives.org/OAI/2.0/oai_dc/");
            $result = $xpath->query($xpathQuery, $xml->documentElement);
            if ($result->length != $number) {
                return false;
            }
            return true;
        }
    }

    /**
     * Special case of checkXpathReturnsExactly: Only one item should be returned
     * by a XPATH query. 
     *
     * @param string $xpathQuery Should be executed on all members of $contents
     * @param array An associative array, the values are of type Symfony\Component\HttpFoundation\Respons
     */
    public function checkXpathReturnsExactlyOne(string $xpathQuery, array $contents)
    {
        return $this->checkXpathReturnsExactly($xpathQuery, $contents, 1);
    }
}
