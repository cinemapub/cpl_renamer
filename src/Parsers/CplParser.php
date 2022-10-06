<?php

namespace Brightfish\CplRenamer\Parsers;

class CplParser extends BaseParser
{
    public function ReelList()
    {
        return $this->xml->ReelList;
    }

    public function Reel(int $id): string
    {
        return $this->xml->ReelList->Reel[$id] ;
    }
}