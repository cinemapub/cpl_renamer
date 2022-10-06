<?php

namespace Brightfish\CplRenamer\Parsers;

class PklParser extends BaseParser
{

    public function AssetList()
    {
        return $this->xml->AssetList;
    }

    public function Asset(int $id)
    {
        return $this->xml->AssetList->Asset[$id];
    }

}