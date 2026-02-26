<?php

declare(strict_types=1);

namespace App\BaLib\Validators;

class EmailValidator
{
    public function __construct()
    {

    }

    public function checkEmail($fromEmail, $emailForCheck)
    {
        $result = [
            'status' => null,
            'message' => "",
        ];
        $syntaxCheck = $this->syntaxCheckEmailAddress($emailForCheck);
        if ($syntaxCheck) {
            $resultArr = $this->verifyServerAvailability($fromEmail, $emailForCheck);
            if (in_array(true, $resultArr, true)) {
                $result['status'] = true;
                $result['message'] = '';
            }else{
                $result['status'] = false;
                $result['message'] .= ' SMTP server pro doménu emailu není dostupný.';
            }

        }else{
            $result['status'] = false;
            $result['message'] = 'Emailová adresa nemá správný tvar';
        }

        return $result;
    }

    private function syntaxCheckEmailAddress($email)
    {
        $pattern = '/^[_a-zA-Z0-9\.\-]+@[_a-zA-Z0-9\.\-]+\.[a-zA-Z]{2,4}$/m';

        return preg_match($pattern, $email);
    }

    private function verifyServerAvailability($from, $email) :array
    {
        $result = [];
        $timeout = 2;

        $domain = preg_replace('~.*@~', '', $email);
        getmxrr($domain, $mxs);
        if (!in_array($domain, $mxs)) {
            $mxs[] = $domain;
        }


        $commands = [
            "HELO " . preg_replace('~.*@~', '', $from),
            "MAIL FROM: <$from>",
            "RCPT TO: <$email>",
        ];

        foreach ($mxs as $mx) {
            $fp = @fsockopen($mx, 25, $errorNumber, $error, $timeout);
            if ($fp && substr($s = fgets($fp), 0, 3) == '220') {
                if(!is_bool($s)){
                    while ($s[3] == '-') {
                        $s = fgets($fp);
                    }
                }
                foreach ($commands as $command) {
                    fwrite($fp, "$command\r\n");
                    if (substr($s = fgets($fp), 0, 3) != '250') {
                        $result[] = false;
                    }
                    if(!is_bool($s)){
                        while ($s[3] == '-') {
                            $s = fgets($fp);
                        }
                    }
                }
                $result[] = true;
                fclose($fp);
            }else{
                $result[] = false;
                if ($fp) {
                    fclose($fp);
                }
            }
        }
        return $result;

    }
}