<?php

namespace Horde\Dns\AwsRoute53Test;

use Aws\Route53\Route53Client;
use Aws\Result as AwsResult;

use Horde\Dns\AwsRoute53;

class FakeAwsRoute53Api extends Route53Client
{
    private $recordSets;
    private $zones;
    public $stats;

    public const MAX_RECORD_SETS_PER_PAGE = 100;

    public function __construct()
    {
        $this->recordSets = [];
        $this->zones = [];
        $this->stats = [
            "apiCalls" => []
        ];
    }

    private function logApiCall($methodName, $params)
    {
        $this->stats["apiCalls"][] = ["name" => $methodName, "params" => $params];
    }

    public function addZone($zoneId, $zoneParams = [])
    {
        $this->zones[$zoneId] = [
            'DelegationSet' => [
                'CallerReference' => $zoneParams["DelegationSetCallerReference"] ?? 'DelegationSetCallerReference',
                'Id' => $zoneParams["DelegationSetId"] ?? 'DelegationSetId',
                'NameServers' => $zoneParams["NameServers"] ?? [
                    "ns1", "ns2"
                ],
            ],
            'HostedZone' => [
                'CallerReference' => $zoneParams["CallerReference"] ?? 'CallerReference',
                'Config' => [
                    'Comment' => $zoneParams["Comment"] ?? 'Comment',
                    'PrivateZone' => $zoneParams["PrivateZone"] ?? false,
                ],
                'Id' => $zoneId,
                'LinkedService' => [
                    'Description' => $zoneParams["LinkedServiceDescription"] ?? 'LinkedServiceDescription',
                    'ServicePrincipal' => $zoneParams["LinkedServiceServicePrincipal"] ?? 'LinkedServiceServicePrincipal',
                ],
                'Name' => $zoneParams["Name"] ?? 'Name',
                'ResourceRecordSetCount' => $zoneParams["ResourceRecordSetCount"] ?? 1,
            ],
            'VPCs' => [
                [
                    'VPCId' => $zoneParams["VPCId"] ?? 'VPCId',
                    'VPCRegion' => 'us-east-1',
                ],
            ],
        ];
    }

    public function checkHostedZoneId($zoneId)
    {
        if (is_null($zoneId)) {
            throw new AwsException("HostedZoneId parameter is required.");
        } else {
            if (!isset($this->zones[$zoneId])) {
                throw new AwsException("HostedZoneId '" . $zoneId . "' could not be found");
            }
        }
    }

    public function &getRecordSets($zoneId)
    {
        if (!isset($this->recordSets[$zoneId])) {
            $this->recordSets[$zoneId] = [];
        }
        return $this->recordSets[$zoneId];
    }

    public function generateRecordSets($amount, $zoneId)
    {
        if ($amount <= 0) {
            return;
        }
        if (!isset($this->zones[$zoneId])) {
            $this->addZone($zoneId);
        }
        $recordSets = &$this->getRecordSets($zoneId);
        $currentRecordSetsInZone = count($recordSets);
        for ($i = $currentRecordSetsInZone; $i < $amount+$currentRecordSetsInZone; $i++) {
            $recordSets[] = $this->getRecordSet([], $i);
        }
        usort($recordSets, function ($a, $b) {
            return strcmp($a["Name"], $b["Name"]);
        });
    }

    public function insertRecordSet($zoneId, $recordSet)
    {
        $recordSets = &$this->getRecordSets($zoneId);
        $idx = $this->getRecordSetIndex($zoneId, $recordSet);
        if ($idx > -1) {
            throw new AwsException("Record already exists. No action was performed");
        }
        foreach ($recordSets as $idx => $val) {
            if (strcmp($val["Name"], $recordSet["Name"]) >= 0) {
                array_splice($recordSets, $idx, 0, [$recordSet]);
                break;
            }
        }
        if (count($recordSets) === $idx+1) {
            $recordSets[] = $recordSet;
        }
    }

    public function deleteRecordSet($zoneId, $recordSet)
    {
        $recordSets = &$this->getRecordSets($zoneId);
        $idx = $this->getRecordSetIndex($zoneId, $recordSet);
        if ($idx <= -1) {
            throw new AwsException("Record doesn't exists. No action was performed");
        }
        array_splice($recordSets, $idx, 1);
    }

    public function updateOrCreateRecordSet($zoneId, $recordSet)
    {
        $recordSets = &$this->getRecordSets($zoneId);
        $idx = $this->getRecordSetIndex($zoneId, $recordSet);
        if ($idx <= -1) {
            $this->insertRecordSet($zoneId, $recordSet);
        } else {
            $recordSets[$idx] = $recordSet;
        }
    }

    public function getRecordSetIndex($zoneId, $recordSet)
    {
        $recordSets = $this->getRecordSets($zoneId);
        foreach ($recordSets as $idx => $val) {
            if (
                $val["Name"] === $recordSet["Name"]
                && $val["Type"] ===  $recordSet["Type"]
            ) {
                return $idx;
            }
        }
        return -1;
    }


    public function getRecordSet($params = [], $idx = 0)
    {
        return [
            "Name" => $params["name"] ?? "recordName_".$idx,
            "Type" => $params["type"] ?? "recordType_".$idx,
            "SetIdentifier" => $params["setId"] ?? "recordSetId_".$idx,
            "Weight" => $params["weight"] ?? "recordWeight_".$idx,
            "Region" => $params["region"] ?? "recordRegion_".$idx,
            "GeoLocation" => [
                "ContinentCode" => $params["continentCode"] ?? "recordContinentCode_".$idx,
                "CountryCode" => $params["countryCode"] ?? "recordCountryCode_".$idx,
            ],
            "ResourceRecords" => [
                [
                    "Value" => $params["value"] ?? "recordValue_".$idx,
                ]
            ],
            "TTL" => $params["ttl"] ?? $idx,
        ];
    }

    public function getResourceRecordSetsResult($recordSets = null, $params = [])
    {
        if (is_null($recordSets)) {
            $recordSets = [$this->getRecordSet()];
        }
        $idx = 0;
        $recordsSetsTotal = count($recordSets);

        $startRecordName = $params["StartRecordName"] ?? null;
        $startRecordType = $params["StartRecordType"] ?? null;
        
        if (!is_null($startRecordName)) {
            for (; $idx < $recordsSetsTotal; $idx++) {
                $recordSet = $recordSets[$idx];
                if (strcmp($recordSet["Name"], $startRecordName) >= 0) {
                    if (is_null($startRecordType)) {
                        break;
                    } elseif ($recordSet["Type"] === $startRecordType) {
                        break;
                    }
                }
            }
        }

        $maxItems = $params["MaxItems"] ?? $this::MAX_RECORD_SETS_PER_PAGE;

        $truncated = false;
        $returnData = [];
        if ($idx === $recordsSetsTotal) {
            $returnData["ResourceRecordSets"] = [];
        } else {
            $recordSetsLeft = $recordsSetsTotal - $idx;
            if ($recordSetsLeft > $maxItems) {
                $returnData["IsTruncated"] = true;
                $nextPageFirstRecord = $recordSets[$idx + $maxItems];
                $returnData["NextRecordName"] = $nextPageFirstRecord["Name"];
                $returnData["NextRecordType"] = $nextPageFirstRecord["Type"];
            }
            $returnData["ResourceRecordSets"] = array_slice($recordSets, $idx, $maxItems);
        }

        $res = new AwsResult($returnData);
        return $res;
    }

    private function getChangeInfo($id, $comment = "", $status = "PENDING", $submittedAt = "2021-05-25T17:48:16.751Z")
    {
        return [
            "Id" => "/change/" . $id,
            "Comment" => $comment,
            "Status" => $status,
            "SubmittedAt" => $submittedAt,
        ];
    }

    public function handleChangeRequest($zoneId, $change)
    {
        $action = $change["Action"] ?? null;
        if (is_null($action)) {
            throw new AwsException("Change Action parameter is required.");
        }
        $resourceRecordSet = $change["ResourceRecordSet"] ?? null;
        if (is_null($resourceRecordSet)) {
            throw new AwsException("Change ResourceRecordSet parameter is required.");
        }
        $name = $resourceRecordSet["Name"];
        $type = $resourceRecordSet["Type"];
        $ttl = $resourceRecordSet["TTL"];
        $resourceRecords = $resourceRecordSet["ResourceRecords"];
        $value = $resourceRecords[0]["Value"];  // assuming there is exactly one value

        $params = [
            "name" => $name,
            "type" => $type,
            "ttl" => $ttl,
            "value" => $value,
        ];
        
        $recordSet = $this->getRecordSet($params);

        if ($action === "CREATE") {
            $this->insertRecordSet($zoneId, $recordSet);
        } elseif ($action === "DELETE") {
            $this->deleteRecordSet($zoneId, $recordSet);
        } elseif ($action === "UPSERT") {
            $this->updateOrCreateRecordSet($zoneId, $recordSet);
        }
    }

    // api methods start here:
    public function listResourceRecordSets($params)
    {
        $this->logApiCall(__FUNCTION__, $params);

        $zoneId = $params["HostedZoneId"] ?? null;
        $this->checkHostedZoneId($zoneId);
        if (isset($params["StartRecordType"]) && !isset($params["StartRecordName"])) {
            throw new AwsException("Not allowed to specify StartRecordType without StartRecordName.");
        }

        $recordSets = $this->recordSets[$zoneId] ?? [];
        $res = $this->getResourceRecordSetsResult($recordSets, $params);
        return $res;
    }

    public function getHostedZone($params)
    {
        $this->logApiCall(__FUNCTION__, $params);

        $zoneId = $params["Id"] ?? null;
        if (is_null($zoneId)) {
            throw new AwsException("Id parameter is required");
        }
        $zone = $this->zones[$zoneId] ?? null;
        if (is_null($zone)) {
            throw new AwsException("Zone with id '" . $zoneId . "' does not exist.");
        }
        return new AwsResult($zone);
    }

    public function changeResourceRecordSets($params)
    {
        $this->logApiCall(__FUNCTION__, $params);

        $zoneId = $params["HostedZoneId"] ?? null;
        $this->checkHostedZoneId($zoneId);

        $changeBatch = $params["ChangeBatch"] ?? null;
        if (is_null($changeBatch)) {
            throw new AwsException("'ChangeBatch' parameter is required.");
        }
        $changes = $changeBatch["Changes"] ?? null;
        if (is_null($changes)) {
            throw new AwsException("'Changes' subparameter is required.");
        }
        $comment = $params["Comment"] ?? "";


        foreach ($changes as $change) {
            $this->handleChangeRequest($zoneId, $change);
        }

        $data = [
            "ChangeInfo" => $this->getChangeInfo("C53AX2H352", $comment)
        ];
        return new AwsResult($data);
    }
}
