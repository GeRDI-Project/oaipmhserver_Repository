<?php
namespace Oaipmh\Server\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use \DOMDocument;
use \DOMXpath;

abstract class DefaultControllerAbstractTest extends WebTestCase
{
    public function getGetAndPost(string $path, array $queryData)
    {
        $client = static::createClient();
        $crawler = $client->request('GET', "$path?" . http_build_query($queryData));
        $retval["getResponse"] = $client->getResponse();
        $crawler = $client->request('POST', "$path", $queryData);
        $retval["postResponse"] = $client->getResponse();
        return $retval;
    }

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

    public function checkXpathReturnsExactlyOne(string $xpathQuery, array $contents)
    {
        return $this->checkXpathReturnsExactly($xpathQuery, $contents, 1);
    }
}
