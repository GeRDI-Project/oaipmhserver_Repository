<?php
/**
 * This file is part of the GeRDI software suite
 *
 * @author  Tobias Weber <weber@lrz.de>
 * @license https://www.apache.org/licenses/LICENSE-2.0
 */
namespace AppBundle\Controller;

use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use AppBundle\Exception\OAIPMHException;
use AppBundle\Service\OAIPMHVerbFactory;
use AppBundle\Utils\OAIPMHUtils;

/**
 *
 * DefaultController for routes that should react like a OAI-PMH server
 *
 */
class DefaultController extends Controller
{
    /**
     * @var $oaipmhCommandFactory AppBundle\Service\OAIPMHVerbFactory
     */
    private $oaipmhVerbFactory;


    public function __construct(OAIPMHVerbFactory $oaipmhVerbFactory)
    {
        $this->oaipmhVerbFactory = $oaipmhVerbFactory;
    }

    /**
     * { @inheritDoc }
     * This is the default symfony action for a route
     * @Route("/", name="oaipmh-server")
     */
    public function indexAction(Request $request)
    {
        $response = new Response();
        $params = OAIPMHUtils::cleanOAIPMHkeys($request->query->all());
        try {
            $oaipmhVerb = $this->oaipmhVerbFactory
                ->createVerb($request->query->all());
            $response->setContent(
                $this->renderView(
                    "verbs/" . $oaipmhVerb->getName() . ".xml.twig",
                    array_merge(
                        $oaipmhVerb->getResponseParams(),
                        ["params" => $params ]
                    )
                )
            );
        } catch (OAIPMHException $e) {
            $response->setContent(
                $this->renderView('errors/oaipmhError.xml.twig', array(
                    "params" => $params,
                    "reason"   => $e->getReason(),
                    "code"     => $e->getErrorCode()
                ))
            );
        }
        $response->headers->set("Content-Type", "text/xml");
        return $response;
    }
}
