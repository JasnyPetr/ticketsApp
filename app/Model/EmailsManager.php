<?php

namespace App\Model;

use Nette;
use App\FsLib\Communications\EmailSender;
use Nette\Bridges\ApplicationLatte\ILatteFactory;
class EmailsManager
{
    private $latteFactory;

    public function __construct
    (
        ILatteFactory $latteFactory
    )
    {
        $this->latteFactory = $latteFactory;
    }

    public function sendEmail($forEmail, $subject, $emailText)
    {
        try {
            $latte = $this->latteFactory->create();

            $emailSender = new EmailSender('kontakt@fyzioklinika.cz', $forEmail, false);
            $emailSender->setSubject($subject);
            $emailSender->setBody($latte->renderToString(__DIR__ . '/email.latte', array('content' => $emailText)));
            $emailSender->sendMail();
            return true;
        }catch (\Exception $e){
            Debugger::log($e->getMessage(), ILogger::EXCEPTION);
            return false;
        }
    }

    public function sendEmailWithQrcodeAjax($actionName, $clientEmail, $ticketPath)
    {
        try {
            $latte = $this->latteFactory->create();
            $fileName = basename($ticketPath);

            $emailSender = new EmailSender('kontakt@fyzioklinika.cz', $clientEmail, false);
            $emailSender->setSubject('Vstupenka na "' . $actionName . '"');
            $emailSender->setBody($latte->renderToString(__DIR__ . '/ticket_email.latte', array('imagePath' => $ticketPath, 'actionName' => $actionName)));
            $emailSender->setAttachment($ticketPath, $fileName);
            $emailSender->sendMail();
            return true;
        }catch (\Exception $e){
            Debugger::log($e->getMessage(), ILogger::EXCEPTION);
            return false;
        }
    }
}