<?php

namespace Brightfish\CplRenamer;

use SimpleXMLIterator;

class CplRenamer
{
    public function __construct()
    {
    }

    public function rename(string $playlist_folder, string $output_folder){
        if(!is_dir($output_folder)){
            mkdir($output_folder);
        }
        $cpls = glob("$playlist_folder/*.xml");
        foreach($cpls as $cpl){
            $this->rename_cpl($cpl,$output_folder);
        }
        $this->create_packlist($output_folder);
        $this->create_volindex($output_folder);
        $this->create_assetmap($output_folder);
    }

    private function rename_cpl(string $filename, string $output_folder){
    }

    private function create_packlist(string $output_folder, string $output_file="PKL.xml"){
        /*
         * <?xml version="1.0" encoding="UTF-8"?>
        <PackingList xmlns="http://www.digicine.com/PROTO-ASDCP-PKL-20040311#">
          <Id>urn:uuid:211d519a-814c-454a-8701-bdab7f4e8220</Id>
          <AnnotationText>ADV-PUB2022-10-05AfterEverHappyOV2DS1</AnnotationText>
          <IssueDate>2022-10-04T13:44:52.450+02:00</IssueDate>
          <Issuer>Unique X</Issuer>
          <Creator>Advertising Accord</Creator>
          <AssetList>
            <Asset>
              <Id>urn:uuid:7f339e2b-cbe8-4293-98ae-01aadd987ae9</Id>
              <AnnotationText>ADV-PUB2022-10-05AfterEverHappyOV2DS1-ECH-P1</AnnotationText>
              <Hash>gMuS3kKkSF+d3/PoXSuzuNsDPHQ=</Hash>
              <Size>5046</Size>
              <Type>text/xml;asdcpKind=CPL</Type>
              <OriginalFileName>ADV-PUB2022-10-05AfterEverHappyOV2DS1-ECH-P1.xml</OriginalFileName>
            </Asset>
            <Asset>
              <Id>urn:uuid:fe0f6cf1-04eb-4482-8c51-7f004ae71b7a</Id>
              <AnnotationText>ADV-PUB2022-10-05AfterEverHappyOV2DS1-MAIN-P2</AnnotationText>
              <Hash>a8YknM5nQZDPBCvysvBQfSrAyVo=</Hash>
              <Size>16678</Size>
              <Type>text/xml;asdcpKind=CPL</Type>
              <OriginalFileName>ADV-PUB2022-10-05AfterEverHappyOV2DS1-MAIN-P2.xml</OriginalFileName>
            </Asset>
          </AssetList>
        </PackingList>
         */
        $filename="$output_folder/$output_file";
        $xml='';
        file_put_contents($filename,$xml);

    }

    private function create_volindex(string $output_folder, string $output_file="VOLINDEX"){
        /*
         * <?xml version="1.0" encoding="UTF-8"?>
        <VolumeIndex xmlns="http://www.digicine.com/PROTO-ASDCP-VL-20040311#">
          <Index>1</Index>
        </VolumeIndex>
         */
        $xml='<?xml version="1.0" encoding="UTF-8"?>
<VolumeIndex xmlns="http://www.digicine.com/PROTO-ASDCP-VL-20040311#">
  <Index>1</Index>
</VolumeIndex>
';
        $filename="$output_folder/$output_file";
        file_put_contents($filename,$xml);
    }

    private function create_assetmap(string $output_folder, string $output_file="ASSETMAP"){
        $filename="$output_folder/$output_file";
        $xml='';
        file_put_contents($filename,$xml);

    }

}