<?php

namespace Brightfish\CplRenamer\Writers;

use SimpleXMLElement;

class BaseWriter
{
    protected SimpleXMLElement $contents;
    protected string $Issuer = 'Brightfish';
    protected string $Creator = 'Spottix Renamer';

    public function loadFromFile(string $filename){
        $this->contents = simplexml_load_file($filename);
    }

    public function loadFromText(string $text){
        $this->contents = simplexml_load_string($text);
    }

    public function saveToFile(string $filename): bool
    {
        if(isset($this->contents)){
            $dom = new \DOMDocument('1.0');
            $dom->preserveWhiteSpace = true;
            $dom->formatOutput = true;
            $dom->loadXML($this->contents->asXML());
            file_put_contents($filename,$dom->saveXML());
            return true;
        } return false;
    }

}