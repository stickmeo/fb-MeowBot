<?php

namespace stickmeo\MeowBot;
use stickmeo\MeowBot\Conversation;

class Chat {
    public function __construct($accessToken, $payload) {
        $this->accessToken = $accessToken;
        $this->payload = $payload;
    }

    public function meow($msg, $retId=false) {
        $retId = $retId ? $retId : $this->payload->sender->id;
        $res = null;
        if (is_string($msg)) {
            $res = $this->textMessage($retId, $msg);
        }
        else if (isset($msg['buttons']) && isset($msg['text'])) {
            $res = $this->buttonTemplate($retId, $msg);
        }
        else if (isset($msg['cards'])) {
            $res = $this->genericTemplate($retId, $msg);
        }
        if ($res) {
            $ch = curl_init('https://graph.facebook.com/v3.0/me/messages?access_token='.$this->accessToken);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($res));
            curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
            $result = curl_exec($ch);
            curl_close($ch);
        } else {
            throw new Exception('Wrong input type');
        }
    }

    public function textMessage($retId, $msg) {
        return [
            'recipient' => [ 'id' => $retId ],
            'message' => [ 'text' => $msg ]
        ];
    }

    public function buttonTemplate($retId, $msg) {
        return [
            'recipient' => [ 'id' => $retId ],
            'message' => [
                'attachment' => [
                    'type' => 'template',
                    'payload' => [
                        'template_type' => 'button',
                        'text' => $msg['text'],
                        'buttons' => $msg['buttons']
                    ]
                ]
            ]
        ];
    }

    public function genericTemplate($retId, $msg) {
        return [
            'recipient' => [ 'id' => $retId ],
            'message' => [
                'attachment' => [
                    'type' => 'template',
                    'payload' => [
                        'template_type' => 'generic',
                        'elements' => $msg['cards']
                    ]
                ]
            ]
        ];
    }

    public function conversation($conver) {
        $conver(new Conversation($this->accessToken, $this->payload));
    }
}