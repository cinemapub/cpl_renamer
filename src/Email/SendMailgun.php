<?php

namespace Brightfish\CplRenamer\Email;

use Dotenv\Dotenv;
use Mailgun\Mailgun;
use Mailgun\Message\Exceptions\TooManyRecipients;
use Mailgun\Message\MessageBuilder;
use Psr\Http\Client\ClientExceptionInterface;

class SendMailgun
{

    private Mailgun $client;
    private string $from;

    public function __construct()
    {
        $dotenv = Dotenv::createImmutable(__DIR__ . "/../..");
        $dotenv->safeLoad();

        $this->client = Mailgun::create($_ENV["MAILGUN_API_KEY"],"https://api.eu.mailgun.net");
        $this->from = $_ENV["MAILGUN_SENDER"];
    }

    /**
     * @throws TooManyRecipients
     * @throws ClientExceptionInterface
     */
    public function send(string $destination, string $subject, string $body, array $attachments = []){
        $builder = new MessageBuilder();
        $builder->setFromAddress($this->from);
        $builder->addToRecipient($destination);
        $builder->addCcRecipient($_ENV["MAILGUN_CC"]);
        $builder->setReplyToAddress($_ENV["MAILGUN_CC"]);
        $builder->setSubject($subject);
        $builder->setTextBody($body);
        foreach($attachments as $attachment){
            if(file_exists($attachment)){
                $builder->addAttachment($attachment);
            }
        }
        $builder->setClickTracking(true);
        return $this->client->messages()->send($_ENV["MAILGUN_DOMAIN"],$builder->getMessage());
    }
}