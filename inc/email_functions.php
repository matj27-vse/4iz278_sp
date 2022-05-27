<?php
    function sendMail($from, $fromGivenName = '', $fromFamilyName = '',
                      $replyTo, $replyToGivenName = '', $replyToFamilyName = '',
                      $to,
                      $subject, $body) {
        $chyby = [];

        if (!filter_var($to, FILTER_VALIDATE_EMAIL)) {
            $chyby['to'] = 'E-mail příjemce nemá platný formát.';
        }

        if (!filter_var($from, FILTER_VALIDATE_EMAIL)) {
            $chyby['from'] = 'E-mail odesílatele nemá platný formát.';
        }

        if (!filter_var($replyTo, FILTER_VALIDATE_EMAIL)) {
            $chyby['reply_to'] = 'E-mail pro odpověď nemá platný formát.';
        }

        $subject .= ' | Objednací kalendář';

        $emailHtml = '
        <html lang="cs">
        <head>
            <title>' . $subject . '</title>
        </head>
        <body>' . $body . '</body>
        </html>';

        $headers = [
            'MIME-Version: 1.0',
            'Content-type: text/html; charset=utf-8', //pokud chceme správně kódování a neřešit ruční kódování do mailu
            'From: ' . $fromGivenName . ' ' . $fromFamilyName . '<' . $from . '>', //pokud byste v mailu chtěli nejen adresu, ale i jméno odesílatele, může tu být tvar: From: Jméno Příjmení<email@domain.tld> (obdobně u dalších hlaviček)
            'Reply-To: ' . $replyToGivenName . ' ' . $replyToFamilyName . '<' . $replyTo . '>',
            'X-Mailer: PHP/' . phpversion()
        ];

        $headers = implode("\r\n", $headers);

        if (empty($chyby)) {
            //mail($to, $subject, $emailHtml, $headers);
            mail('matj27@vse.cz', $subject, $emailHtml, $headers);
        }

        return $chyby;
    }
