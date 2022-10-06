<?php

namespace Brightfish\CplRenamer\Writers;

use Brightfish\CplRenamer\Exceptions\InputInvalidException;
use Brightfish\CplRenamer\Exceptions\InputMissingException;
use Brightfish\CplRenamer\Exceptions\OutputFailedException;
use SimpleXMLElement;

class BaseWriter
{
    protected SimpleXMLElement $contents;
    protected string $Issuer = 'Brightfish';
    protected string $Creator = 'Spottix Renamer';

    /**
     * @throws InputMissingException
     */
    public function loadFromFile(string $filename)
    {
        if (!file_exists($filename)) {
            throw new InputMissingException("File not found: [$filename]");
        }
        $this->contents = simplexml_load_file($filename);
    }

    public function loadFromText(string $text)
    {
        $this->contents = simplexml_load_string($text);
    }

    /**
     * @throws OutputFailedException
     * @throws InputInvalidException
     */
    public function saveToFile(string $filename): bool
    {
        if(!isset($this->contents)){
            throw new InputInvalidException('No data to export');
        }
        $dom = new \DOMDocument('1.0');
        $dom->preserveWhiteSpace = true;
        $dom->formatOutput = true;
        $dom->loadXML($this->contents->asXML());
        file_put_contents($filename, $dom->saveXML());
        if(!file_exists($filename) || filesize($filename) == 0){
            throw new OutputFailedException("Could not create output file [$filename]");
        }
        return true;
    }

}