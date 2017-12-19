<?php

namespace AppBundle\Utils;

use Symfony\Component\Debug\Exception\HandledErrorException;
use AppBundle\Entity\Item;
use \DateTime;
use \Exception;

class OAIUtils
{
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

    public static function cleanOAIkeys(array $oaikeys)
    {
        foreach ($oaikeys as $key => $value) {
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
                    if (OAIUtils::validateOaiDate($oaikeys[$key])) {
                        continue 2;
                    }
            }
            unset($oaikeys[$key]);
        }
        return $oaikeys;
    }

    public static function validateOaiDate(String $dateString)
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

    protected static function paramIsAllowedForVerb(String $param, String $verb)
    {
        switch ($param) {
            case "identifier":
                if ($verb == "ListMetadataFormats"
                    or  $verb == "GetRecords") {
                    return true;
                }
                // no break
            case "metadataPrefix":
                if ($verb == "GetRecords"
                    or  $verb == "ListIdentifiers"
                    or  $verb == "ListRecords") {
                    return true;
                }
                // no break
            case "from":
            case "until":
            case "set":
                if ($verb == "ListIdentifiers"
                    or  $verb == "ListRecords") {
                    return true;
                }
                // no break
            case "resumptionToken":
                if ($verb == "ListIdentifiers"
                    or  $verb == "ListRecords"
                    or  $verb == "ListSets") {
                    return true;
                }
                //no break
            case "verb":
                return true;
                //no break
            default:
                return false;
        }
    }

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

    public static function badArgumentsForVerb(array $params, String $verb, String &$reason)
    {
        foreach ($params as $key => $value) {
            if (!OAIUtils::paramIsAllowedForVerb($key, $verb)) {
                $reason = "$key is not allowed for verb $verb!";
                return true;
            }
        }
        foreach (OAIUtils::getRequiredParamsForVerb($verb) as $req) {
            if (!array_key_exists($req, $params)) {
                if (!$req == "metadataPrefix" or !array_key_exists("resumptionToken", $params)) {
                    $reason = "$req has to be set when verb is $verb";
                    return true;
                }
            }
        }
        foreach (OAIUtils::getExclusiveParamsForVerb($verb) as $excl) {
            if (array_key_exists($excl, $params) and count($params) > 2) {
                $reason = "$excl is an exclusive parameter when called with $verb";
                return true;
            }
        }
        //check dates:
        $dateFields = array("from", "until");
        foreach ($dateFields as $dateField) {
            if (isset($params[$dateField])
                and !OAIUtils::validateOaiDate($params[$dateField])) {
                $reason =   "$dateField is not a valid date!";
                $reason .=  "Allowed formats: YYYY-MM-DD or  YYYY-MM-DDThh:mm:ssZ";
                return true;
            }
        }
        return false;
    }
}
