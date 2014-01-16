<?php

/**
 * This scripts generates random posts
 */
require 'cli-bootstrap.php';

class SendSpoolTask extends Phalcon\DI\Injectable
{

    protected $amazonSes;

    protected $config;

    /**
     * @param $config
     */
    public function __construct($config)
    {
        $this->config = $config;
    }

    /**
     * @param $raw
     *
     * @return bool|string
     */
    private function amazonSESSend($raw)
    {

        if ($this->amazonSes == null) {
            $this->amazonSes
                = new AmazonSES($this->config->amazon->AWSAccessKeyId, $this->config->amazon->AWSSecretKey);
            $this->amazonSes->disable_ssl_verification();
        }

        $opt        = array(
            'curlopts' => array(
                CURLOPT_SSL_VERIFYHOST => 0,
                CURLOPT_SSL_VERIFYPEER => 0
            )
        );
        $rawMessage = array(
            'Data' => base64_encode($raw)
        );
        $response   = $this->amazonSes->send_raw_email($rawMessage, $opt);

        if (!$response->isOK()) {
            echo 'Error sending email from AWS SES: ' . $response->body->asXML(), PHP_EOL;
            return false;
        }

        return $response->body;
    }

    /**
     * @param $text
     *
     * @return mixed
     */
    private function prerify($text)
    {
        if (preg_match_all('#```([a-z]+)(.+)```([\n\r]+)?#m', $text, $matches, PREG_SET_ORDER)) {
            foreach ($matches as $match) {
                $text = str_replace($match[0], '<pre>' . $match[2] . '</pre>', $text);
            }
        }
        return $text;
    }

    public function send()
    {
        $notifications = Phosphorum\Models\Notifications::find('sent = "N"');

        foreach ($notifications as $notification) {

            $post = $notification->post;
            $user = $notification->user;

            if ($user->email && $user->notifications != 'N') {

                $message = new Swift_Message('[Phalcon Forum] ' . $post->title);
                $message->setTo(new Swift_Address($user->email, $user->name));

                if ($notification->type == 'P') {
                    $originalContent = $post->content;
                    $escapedContent  = $this->escaper->escapeHtml($post->content);
                    $message->setFrom(new Swift_Address('phosphorum@phalconphp.com', $post->user->name));
                } else {
                    $reply           = $notification->reply;
                    $originalContent = $reply->content;
                    $escapedContent  = $this->escaper->escapeHtml($reply->content);
                    $message->setFrom(new Swift_Address('phosphorum@phalconphp.com', $reply->user->name));
                }

                $prerifiedContent = $this->prerify($escapedContent);
                $htmlContent      = nl2br($prerifiedContent);

                $textContent = $originalContent;

                $htmlContent .= '<p style="font-size:small;-webkit-text-size-adjust:none;color:#717171;">';
                if ($notification->type == 'P') {
                    $htmlContent
                        .=
                        '&mdash;<br>This email works only as notification. Don\'t reply. To join conversation you must view the complete thread on '
                        . PHP_EOL . '<a href="http://forum.phalconphp.com/discussion/' . $post->id . '/'
                        . $post->slug . '">Phosphorum</a>. ';
                } else {
                    $htmlContent
                        .=
                        '&mdash;<br>This email works only as notification. Don\'t reply. To join conversation you must view the complete thread on '
                        . PHP_EOL . '<a href="http://forum.phalconphp.com/discussion/' . $post->id . '/' . $post->slug
                        . '#C' . $reply->id . '">Phosphorum</a>. ';
                }
                $htmlContent .= PHP_EOL . 'Change your preferences <a href="http://forum.phalconphp.com/settings">here</a>';

                $bodyMessage = new Swift_Message_Part($htmlContent, 'text/html');
                $bodyMessage->setCharset('UTF-8');
                $message->attach($bodyMessage);

                $bodyMessage = new Swift_Message_Part($textContent, 'text/plain');
                $bodyMessage->setCharset('UTF-8');
                $message->attach($bodyMessage);

                $raw  = '';
                $data = $message->build();
                while (false !== $bytes = $data->read()) {
                    $raw .= $bytes;
                }

                echo $raw;
                die;

                if (($sendResponse = $this->amazonSESSend($raw)) !== false) {
                    $notification->message_id = (string)$sendResponse->SendRawEmailResult->MessageId;
                }
            }

            $notification->sent = 'Y';
            if ($notification->save() == false) {
                foreach ($notification->getMessages() as $message) {
                    echo $message->getMessage(), PHP_EOL;
                }
            }
        }
    }

    /**
     *
     */
    public function run()
    {
        $notifications = Phosphorum\Models\Notifications::find('sent = "N"');

        foreach ($notifications as $notification) {

            $post = $notification->post;
            $user = $notification->user;

            if ($user->email && $user->notifications != 'N') {

                $message = new Swift_Message('[Phalcon Forum] ' . $post->title);
                $message->setTo(new Swift_Address($user->email, $user->name));

                if ($notification->type == 'P') {
                    $originalContent = $post->content;
                    $escapedContent  = $this->escaper->escapeHtml($post->content);
                    $message->setFrom(new Swift_Address('phosphorum@phalconphp.com', $post->user->name));
                } else {
                    $reply           = $notification->reply;
                    $originalContent = $reply->content;
                    $escapedContent  = $this->escaper->escapeHtml($reply->content);
                    $message->setFrom(new Swift_Address('phosphorum@phalconphp.com', $reply->user->name));
                }

                $prerifiedContent = $this->prerify($escapedContent);
                $htmlContent      = nl2br($prerifiedContent);

                $textContent = $originalContent;

                $htmlContent .= '<p style="font-size:small;-webkit-text-size-adjust:none;color:#717171;">';
                if ($notification->type == 'P') {
                    $htmlContent
                        .=
                        '&mdash;<br>This email works only as notification. Don\'t reply. To join conversation you must view the complete thread on '
                        . PHP_EOL . '<a href="http://forum.phalconphp.com/discussion/' . $post->id . '/'
                        . $post->slug . '">Phosphorum</a>. ';
                } else {
                    $htmlContent
                        .=
                        '&mdash;<br>This email works only as notification. Don\'t reply. To join conversation you must view the complete thread on '
                        . PHP_EOL . '<a href="http://forum.phalconphp.com/discussion/' . $post->id . '/' . $post->slug
                        . '#C' . $reply->id . '">Phosphorum</a>. ';
                }
                $htmlContent .= PHP_EOL . 'Change your preferences <a href="http://forum.phalconphp.com/settings">here</a>';

                $bodyMessage = new Swift_Message_Part($htmlContent, 'text/html');
                $bodyMessage->setCharset('UTF-8');
                $message->attach($bodyMessage);

                $bodyMessage = new Swift_Message_Part($textContent, 'text/plain');
                $bodyMessage->setCharset('UTF-8');
                $message->attach($bodyMessage);

                $raw  = '';
                $data = $message->build();
                while (false !== $bytes = $data->read()) {
                    $raw .= $bytes;
                }

                echo $raw;
                die;

                if (($sendResponse = $this->amazonSESSend($raw)) !== false) {
                    $notification->message_id = (string)$sendResponse->SendRawEmailResult->MessageId;
                }
            }

            $notification->sent = 'Y';
            if ($notification->save() == false) {
                foreach ($notification->getMessages() as $message) {
                    echo $message->getMessage(), PHP_EOL;
                }
            }
        }
    }

}

try {
    $task = new SendSpoolTask($config);
    $task->send();
} catch (Exception $e) {
    echo $e->getTraceAsString();
}
