<?php

namespace AppBundle\Utils;

use Symfony\Component\Debug\Exception\HandledErrorException;
use AppBundle\Entity\Item;
use \DateTime;
use \Exception;

/**
 * OAIPMHUtils
 *
 * Helper classes for OAIPMH specific tasks. All methods should be static
 *
 * @author  Tobias Weber <weber@lrz.de>
 * @license https://www.apache.org/licenses/LICENSE-2.0
 */
class OAIPMHUtils
{
    /**
     * Checks whether items timestamp matches the until/from selection
     *
     * @param AppBundle\Entitiy\Item $item
     * @param array $params
     *
     * @return bool
     */
    public static function isItemTimestampInsideDateSelection(Item $item, array $params)
    {
        if (!isset($params["from"]) and !isset($params["until"])) {
            return true;
        }

        $checkDates = array("from" => false, "until" => false);
        foreach ($checkDates as $key => $value) {
            if (isset($params[$key])) {
                $checkDates[$key] = new DateTime($params[$key]);
            }
        }
        if ($checkDates["from"]
            and $item->getTimestamp() < $checkDates["from"]) {
            return false;
        } elseif ($checkDates["until"]
            and $item->getTimestamp() > $checkDates["until"]) {
            return false;
        }
        return true;
    }

    /**
     * Returns a cleansed version of the given associative array:
     * only valid OAIPMH-params are returned.
     * All keys are checked. Values are only checked, if a specific value
     * would hinder the validation of the rendered xml (unvalid OAIPMH dates
     * or verbs)
     *
     * @param array $oaipmhkeys Associative array holding the requested params
     *
     * @return array The subset of $oaipmhkeys that are valid keys
     */
    public static function cleanOAIPMHkeys(array $oaipmhkeys)
    {
        foreach ($oaipmhkeys as $key => $value) {
            if ($key == "verb") {
                switch ($value) {
                    case "Identify":
                    case "GetRecord":
                    case "ListIdentifiers":
                    case "ListMetadataFormats":
                    case "ListRecords":
                    case "ListSets":
                        continue 2;
                }
            }
            switch ($key) {
                case "identifier":
                case "metadataPrefix":
                case "resumptionToken":
                case "set":
                    continue 2;
                case "from":
                case "until":
                    if (OAIPMHUtils::validateOAIPMHDate($oaipmhkeys[$key])) {
                        continue 2;
                    }
            }
            unset($oaipmhkeys[$key]);
        }
        return $oaipmhkeys;
    }

    /**
     * Checks whether the given string is a parsable and valid OAIPMH date
     * ('YYYY-MM-DD' or 'YYYY-MM-DDThh:mm:ssZ')
     *
     * @param String $dateString The date as a string
     *
     * @return bool
     */
    public static function validateOAIPMHDate(String $dateString)
    {
        try {
            $date = new DateTime($dateString);
        } catch (Exception $e) {
            return false;
        }

        if ($date && $date->format('Y-m-d\TH:i:s\Z') == $dateString) {
            return true;
        }

        if ($date && $date->format('Y-m-d') == $dateString) {
            return true;
        }

        return false;
    }

    /**
     * Checks whether the given parameter is allowed for the verb of the OAIPMH request
     *
     * @param string $param
     * @param string $verb
     *
     * @return bool
     */
    protected static function paramIsAllowedForVerb(String $param, String $verb)
    {
        $isAllowed = false;
        switch ($param) {
            case "identifier":
                if ($verb == "ListMetadataFormats"
                    or  $verb == "GetRecord") {
                    $isAllowed = true;
                }
                break;
            case "metadataPrefix":
                if ($verb == "GetRecord"
                    or  $verb == "ListIdentifiers"
                    or  $verb == "ListRecords") {
                    $isAllowed = true;
                }
                break;
            case "from":
            case "until":
            case "set":
                if ($verb == "ListIdentifiers"
                    or  $verb == "ListRecords") {
                    $isAllowed = true;
                }
                break;
            case "resumptionToken":
                if ($verb == "ListIdentifiers"
                    or  $verb == "ListRecords"
                    or  $verb == "ListSets") {
                    $isAllowed = true;
                }
                break;
            case "verb":
                $isAllowed = true;
                break;
        }
        return $isAllowed;
    }
    
    /**
     * Returns the params required for the given verb
     *
     * @param String $verb
     *
     * @return array All required params in an array
     */
    protected static function getRequiredParamsForVerb(String $verb)
    {
        switch ($verb) {
            case "GetRecord":
                return array("identifier", "metadataPrefix");
            case "ListRecords":
            case "ListIdentifiers":
                return array("metadataPrefix");
            default:
                return array();
        }
    }
     
    /**
     * Returns the params that are exclusive for the given verb
     *
     * @param String $verb
     *
     * @return array All exclusive params in an array
     */
    protected static function getExclusiveParamsForVerb(String $verb)
    {
        switch ($verb) {
            case "ListIdentifiers":
            case "ListRecords":
            case "ListSets":
                return array("resumptionToken");
            default:
                return array();
        }
    }

    /**
     * Checks whether the given params are valid for the given verb.
     * If the validation fails a reason is stored in $reason
     *
     * @param array $params Associative array of params requested with a verb
     * @param String $verb
     * @param String $reason Will be overwritten (call-by-address)
     *
     * @return bool
     */
    public static function badArgumentsForVerb(array $params, String $verb, String &$reason)
    {
        foreach ($params as $key => $value) {
            if (!OAIPMHUtils::paramIsAllowedForVerb($key, $verb)) {
                $reason = "$key is not allowed for verb $verb!";
                return true;
            }
        }
        foreach (OAIPMHUtils::getRequiredParamsForVerb($verb) as $req) {
            if (!array_key_exists($req, $params)) {
                if (!$req == "metadataPrefix" or !array_key_exists("resumptionToken", $params)) {
                    $reason = "Parameter '$req' has to be set when verb is '$verb'";
                    return true;
                }
            }
        }
        foreach (OAIPMHUtils::getExclusiveParamsForVerb($verb) as $excl) {
            if (array_key_exists($excl, $params) and count($params) > 2) {
                $reason = "'$excl' is an exclusive parameter when called with '$verb'";
                return true;
            }
        }
        //check dates:
        $dateFields = array("from", "until");
        foreach ($dateFields as $dateField) {
            if (isset($params[$dateField])
                and !OAIPMHUtils::validateOAIPMHDate($params[$dateField])) {
                $reason =  "'" . $params[$dateField]."' is not a valid date!";
                $reason .=  " Allowed formats: 'YYYY-MM-DD' or 'YYYY-MM-DDThh:mm:ssZ' (ISO 8601)";
                return true;
            }
        }
        return false;
    }
}
