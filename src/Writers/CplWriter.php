<?php

namespace Brightfish\CplRenamer\Writers;

use Brightfish\CplRenamer\Helpers\CplTime;
use Brightfish\CplRenamer\Helpers\CplUuid;

class CplWriter extends BaseWriter
{

    public function rename(string $name)
    {
        $this->contents->AnnotationText = $name;
        $this->contents->ContentTitleText = $name;
        $this->contents->Id = CplUuid::prefix4();
        $this->contents->IssueDate = CplTime::now();
        $this->contents->Issuer = $this->Issuer;
        $this->contents->Creator = $this->Creator;
    }

}