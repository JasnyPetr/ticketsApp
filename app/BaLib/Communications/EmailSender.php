<?php

declare(strict_types=1);

namespace App\BaLib\Communications;

use App\BaLib\Validators\EmailValidator;
use Nette\Mail\Message;
use Nette\Mail\SendmailMailer;
use Tracy\Debugger;
use Tracy\ILogger;

class EmailSender
{
    private $mailer;
    private $mail;
    private $emailValidator;
    private $emailFrom;
    private $emailFor = [];
    private $emailForCopy = [];
    private $emailForHideCopy = [];
    private $subject;
    private $attachment = [];
    private $body = null;
    private $formalControl;

    public function __construct
    (
        $emailFrom = null,
        $emailFor = null,
        $formalControl = false
    )
    {
        $this->emailValidator = new EmailValidator();
        $this->mailer = new SendmailMailer();
        $this->mail = new Message();
        $this->emailFrom = $emailFrom;
        $this->emailFor[] = $emailFor;
        $this->formalControl = $formalControl;
    }

    /**
     * @return mixed
     */
    public function getEmailFrom()
    {
        return $this->emailFrom;
    }

    /**
     * @param string $emailFrom
     */
    public function setEmailFrom(string $emailFrom): void
    {
        $this->emailFrom = $emailFrom;
    }

    /**
     * @return array
     */
    public function getEmailFor(): array
    {
        return $this->emailFor;
    }

    /**
     * @param string $emailFor
     */
    public function setEmailFor(string $emailFor): void
    {
        $this->emailFor[] = $emailFor;
    }

    /**
     * @return array
     */
    public function getEmailForCopy(): array
    {
        return $this->emailForCopy;
    }

    /**
     * @param string $emailForCopy
     */
    public function setEmailForCopy(string $emailForCopy): void
    {
        $this->emailForCopy[] = $emailForCopy;
    }

    /**
     * @return array
     */
    public function getEmailForHideCopy():array
    {
        return $this->emailForHideCopy;
    }

    /**
     * @param string $emailForHideCopy
     */
    public function setEmailForHideCopy(string $emailForHideCopy): void
    {
        $this->emailForHideCopy[] = $emailForHideCopy;
    }

    /**
     * @return string
     */
    public function getSubject():string
    {
        return $this->subject;
    }

    /**
     * @param string $subject
     */
    public function setSubject(string $subject): void
    {
        $this->subject = $subject;
    }

    /**
     * @return array
     */
    public function getAttachment():array
    {
        return $this->attachment;
    }

    /**
     * @param string $attachmentFilePath
     * @param string|null $attachmentFileName
     */
    public function setAttachment(string $attachmentFilePath, string $attachmentFileName = null): void
    {
        if ($attachmentFileName != null) {
            $this->attachment[] = [
                'filePath' => $attachmentFilePath,
                'fileName' => $attachmentFileName
            ];
        }else{
            $this->attachment[] = [
                'filePath' => $attachmentFilePath,
                'fileName' => ''
            ];
        }
    }

    /**
     * @return string
     */
    public function getBody():string
    {
        return $this->body;
    }

    /**
     * @param string $body
     */
    public function setBody(string $body): void
    {
        $this->body = $body;
    }



    private function checkingOfMandatoryRequirementsOfEmail():bool
    {
        if ($this->emailFrom == "") {
            return false;
        }
        if (count($this->emailFor) == 0) {
            return false;
        }

        return true;
    }

    private function addCopyRecipients():void
    {
        if ($this->formalControl == true) {
            if (count($this->emailForCopy) > 1) {
                foreach ($this->emailForCopy as $copyRecipient) {
                    $resultFormalControl = $this->emailValidator->checkEmail($this->emailFrom, $copyRecipient);
                    if ($resultFormalControl['status'] == true) {
                        $this->mail->addCc($copyRecipient);
                        continue;
                    }else{
                        Debugger::log($resultFormalControl['message'] . $copyRecipient, ILogger::WARNING);
                    }
                }
            } elseif (count($this->emailForCopy) == 1) {
                $resultFormalControl = $this->emailValidator->checkEmail($this->emailFrom, $this->emailForCopy[0]);
                if ($resultFormalControl['status'] == true) {
                    $this->mail->addCc($this->emailForCopy[0]);
                }else{
                    Debugger::log($resultFormalControl['message'] . $this->emailForCopy[0], ILogger::WARNING);
                }
            }
        }else{
            if (count($this->emailForCopy) > 1) {
                foreach ($this->emailForCopy as $copyRecipient) {
                    $this->mail->addCc($copyRecipient);
                }
            } elseif (count($this->emailForCopy) == 1) {
                $this->mail->addCc($this->emailForCopy[0]);
            }
        }
    }

    private function addCopyRecipientHidden():void
    {
        if ($this->formalControl == true) {
            if (count($this->emailForHideCopy) > 1) {
                foreach ($this->emailForHideCopy as $copyRecipientHidden) {
                    $resultFormalControl = $this->emailValidator->checkEmail($this->emailFrom, $copyRecipientHidden);
                    if ($resultFormalControl['status'] == true) {
                        $this->mail->addBcc($copyRecipientHidden);
                        continue;
                    }else{
                        Debugger::log($resultFormalControl['message'] . $copyRecipientHidden, ILogger::WARNING);
                    }
                }
            } elseif (count($this->emailForHideCopy) == 1) {
                $resultFormalControl = $this->emailValidator->checkEmail($this->emailFrom, $this->emailForHideCopy[0]);
                if ($resultFormalControl['status'] == true) {
                    $this->mail->addBcc($this->emailForHideCopy[0]);
                }else{
                    Debugger::log($resultFormalControl['message'] . $this->emailForHideCopy[0], ILogger::WARNING);
                }
            }
        }else{
            if (count($this->emailForHideCopy) > 1) {
                foreach ($this->emailForHideCopy as $copyRecipientHidden) {
                    $this->mail->addBcc($copyRecipientHidden);
                }
            } elseif (count($this->emailForHideCopy) == 1) {
                $this->mail->addBcc($this->emailForHideCopy[0]);
            }
        }

    }

    private function addBody():void
    {
        if ($this->body != null) {
            $this->mail->setHtmlBody($this->body);
        }
    }

    private function addAttachments():void
    {
        if (count($this->attachment) > 1) {
            foreach ($this->attachment as $attach) {
                if ($attach['fileName'] != '') {
                    $this->mail->addAttachment($attach['fileName'], file_get_contents($attach['filePath']));
                }else{
                    $this->mail->addAttachment($attach['filePath']);
                }
            }
        }elseif (count($this->attachment) == 1){
            if ($this->attachment[0]['fileName'] != '') {
                $this->mail->addAttachment($this->attachment[0]['fileName'], file_get_contents($this->attachment[0]['filePath']));
            }else{
                $this->mail->addAttachment($this->attachment[0]['filePath']);
            }
        }
    }
    public function sendMail(): bool
    {
        if ($this->checkingOfMandatoryRequirementsOfEmail()) {
            if ($this->formalControl == true) {
                if (count($this->emailFor) > 1) {
                    foreach ($this->emailFor as $item) {
                        $resultFormalControl = $this->emailValidator->checkEmail($this->emailFrom, $item);
                        if ($resultFormalControl['status'] == true) {
                            $this->mail->addTo($item);
                            continue;
                        }else{
                            Debugger::log($resultFormalControl['message'] . $item, ILogger::WARNING);
                        }
                    }
                    $this->mail->setFrom($this->emailFrom)
                        ->setSubject($this->subject);

                    $this->addCopyRecipients();
                    $this->addCopyRecipientHidden();
                    $this->addBody();
                    $this->addAttachments();

                    $this->mailer->send($this->mail);
                }else{
                    $resultFormalControl = $this->emailValidator->checkEmail($this->emailFrom, $this->emailFor[0]);
                    if ($resultFormalControl['status'] == true) {
                        $this->mail->setFrom($this->emailFrom)
                                    ->addTo($this->emailFor[0])
                                    ->setSubject($this->subject);

                        $this->addCopyRecipients();
                        $this->addCopyRecipientHidden();
                        $this->addBody();
                        $this->addAttachments();

                        $this->mailer->send($this->mail);
                    }else{
                        Debugger::log($resultFormalControl['message'] . $this->emailFor[0], ILogger::WARNING);
                    }
                }
            }else{
                if (count($this->emailFor) > 1) {
                    foreach ($this->emailFor as $item) {
                        $this->mail->addTo($item);
                    }
                    $this->mail->setFrom($this->emailFrom)
                        ->setSubject($this->subject);

                    $this->addCopyRecipients();
                    $this->addCopyRecipientHidden();
                    $this->addBody();
                    $this->addAttachments();

                    $this->mailer->send($this->mail);
                }else{
                    $this->mail->setFrom($this->emailFrom)
                        ->addTo($this->emailFor[0])
                        ->setSubject($this->subject);

                    $this->addCopyRecipients();
                    $this->addCopyRecipientHidden();
                    $this->addBody();
                    $this->addAttachments();

                    $this->mailer->send($this->mail);
                }

            }
        } else {
            return false;
        }
        return true;
    }
}