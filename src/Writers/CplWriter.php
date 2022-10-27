<?php

namespace Brightfish\CplRenamer\Writers;

use Brightfish\CplRenamer\Exceptions\InputInvalidException;
use Brightfish\CplRenamer\Helpers\CplTime;
use Brightfish\CplRenamer\Helpers\CplUuid;
use Brightfish\CplRenamer\Helpers\GetSpotDetails;

class CplWriter extends BaseWriter
{

    private array $needed_spots;

    public function rename(string $name)
    {
        $this->contents->AnnotationText = $name;
        $this->contents->ContentTitleText = $name;
        $this->contents->Id = CplUuid::prefix4();
        $this->contents->IssueDate = CplTime::now();
        $this->contents->Issuer = $this->Issuer;
        $this->contents->Creator = $this->Creator;
    }

    /**
     * @throws InputInvalidException
     */
    public function calculateCplDigest(): string
    {
        $digest_length=3;
        if(!isset($this->contents)){
            throw new InputInvalidException('No contents to analyze');
        }
        $idList=[];
        $details = new GetSpotDetails();
        foreach($this->contents->ReelList->Reel as $reel){
            $id=(string)$reel->Id;
            $idList[$id]=$id;
        }
        sort($idList);
        $idListTxt = implode(",",$idList);

        return sprintf("%03d%s",count($idList),strtoupper(substr(sha1($idListTxt),0,$digest_length)));
    }

    /**
     * @throws InputInvalidException
     */
    public function getSpotList(): array
    {
        if(!isset($this->contents)){
            throw new InputInvalidException('No contents to analyze');
        }
        $needed_spots=[];
        $details = new GetSpotDetails();
        foreach($this->contents->ReelList->Reel as $reel){
            $id=(string)$reel->Id;
            $spot_info = $details->get($id);
            if($spot_info){
                $folder_name = $spot_info["folder_name"] ?? "";
                $content_title = $spot_info["content_title"] ?? "";
                $needed_spots[$folder_name]=$content_title;
            }
        }
        ksort($needed_spots);
        return $needed_spots;
    }

}