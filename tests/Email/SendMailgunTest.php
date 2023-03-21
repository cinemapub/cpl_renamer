<?php

namespace Brightfish\CplRenamer\Email;

use PHPUnit\Framework\TestCase;

class SendMailgunTest extends TestCase
{

    public function testSend()
    {
        $message = new SendMailgun();
        $message->send("p.forret@brightfish.be","Test email " . date("c"),"some text");

    }
}
