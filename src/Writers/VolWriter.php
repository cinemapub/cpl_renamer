<?php

namespace Brightfish\CplRenamer\Writers;

class VolWriter extends BaseWriter
{
    public function __construct()
    {
        $this->contents = simplexml_load_string('<?xml version="1.0" encoding="UTF-8"?><VolumeIndex xmlns="http://www.digicine.com/PROTO-ASDCP-VL-20040311#"><Index>1</Index></VolumeIndex>');
    }
}