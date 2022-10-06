<?php

namespace Brightfish\CplRenamer\Writers;

use Brightfish\CplRenamer\Helpers\CplTime;
use Brightfish\CplRenamer\Helpers\CplUuid;
use Brightfish\CplRenamer\Parsers\BaseParser;
use SimpleXMLElement;

class MapWriter extends BaseWriter
{

    public function __construct()
    {
        $this->contents = new SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><AssetMap xmlns="http://www.digicine.com/PROTO-ASDCP-AM-20040311#"></AssetMap>');
        $this->contents->addChild("Id", CplUuid::prefix4());
        $this->contents->addChild("VolumeCount", 1);
        $this->contents->addChild("IssueDate", CplTime::now());
        $this->contents->addChild('Issuer', $this->Issuer);
        $this->contents->addChild('Creator', $this->Creator);
        $this->contents->addChild('AssetList');
    }

    public function addAsset(string $filename)
    {
        /*
        <Asset>
          <Id>urn:uuid:fe0f6cf1-04eb-4482-8c51-7f004ae71b7a</Id>
          <ChunkList>
            <Chunk>
              <Path>ADV-PUB2022-10-05AfterEverHappyOV2DS1-MAIN-P2.xml</Path>
              <VolumeIndex>1</VolumeIndex>
            </Chunk>
          </ChunkList>
        </Asset>
         */
        $asset = $this->contents->AssetList->addChild("Asset");
        $asset->addChild("Id", (new BaseParser($filename))->Id());
        $chunk = $asset->addChild("ChunkList")->addChild("Chunk");
        $chunk->addChild("Path", basename($filename));
        $chunk->addChild("VolumeIndex", 1);
    }

}