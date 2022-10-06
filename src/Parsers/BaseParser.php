<?php

namespace Brightfish\CplRenamer\Parsers;

use SimpleXMLElement;

class BaseParser
{
    protected SimpleXMLElement $xml;

    public function __construct(string $file)
    {
        $this->xml = simplexml_load_file($file);
    }

    public function Id(): string
    {
        return (string)$this->xml->Id ?? "";
    }

    public function AnnotationText(): string
    {
        return (string)$this->xml->AnnotationText ?? "";
    }

    public function ContentTitleText(): string
    {
        return (string)$this->xml->ContentTitleText ?? "";
    }

    public function IssueDate(): string
    {
        return (string)$this->xml->IssueDate ?? "";
    }

    public function Issuer(): string
    {
        return (string)$this->xml->Issuer ?? "";
    }

    public function Creator(): string
    {
        return (string)$this->xml->Creator ?? "";
    }

}