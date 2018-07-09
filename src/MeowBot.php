<?php

namespace stickmeo\MeowBot;

class MeowBot extends Chat {
    public function __construct($options) {
        if (!$options['accessToken'] || !$options['vertifyToken']) {
            throw new Exception('Missing accessToken or vertifyToken');
            exit;
        }
        $request = json_decode(file_get_contents('php://input'));
        $this->accessToken = $options['accessToken'];
        $this->vertifyToken = $options['vertifyToken'];
        $this->webhook();
        if (isset($request->entry[0]->messaging[0]) && !isset($request->entry[0]->messaging[0]->delivery) && !isset($request->entry[0]->messaging[0]->read)) {
            $this->payload = $request->entry[0]->messaging[0];
            parent::__construct($this->accessToken, $this->payload);
            $this->checkAsking();
        } else {
            exit(200);
        }
    }

    private function webhook() {
        if (isset($_REQUEST['hub_verify_token']) && $_REQUEST['hub_verify_token'] === $this->vertifyToken) {
            echo $_REQUEST['hub_challenge'];
            exit;
        }
    }
    
    public function hear($msg, $callback) {
        $msg = is_array($msg) ? $msg : [$msg];
        if (isset($this->payload->message->text)) {
            foreach ($msg as $key) {
                if ($key == $this->payload->message->text) {
                    $callback($this->payload, new Chat($this->accessToken, $this->payload));
                }
            }
        } else if (isset($this->payload->postback)) {
            foreach ($msg as $key) {
                if (substr($key, 0, 9) == 'postback:' && substr($key, 9) == $this->payload->postback->payload) {
                    $callback($this->payload, new Chat($this->accessToken, $this->payload));
                }
            }
        }
    }

    private function checkAsking() {
        $fname = 'src/temp/ask_'.$this->payload->sender->id;
        if (file_exists($fname)) {

            $serializer = new \SuperClosure\Serializer();

            $ask = unserialize(file_get_contents($fname));
            $serializer->unserialize($ask->callbacks[0])($this->payload, new Chat($this->accessToken, $this->payload));
            array_splice($ask->callbacks, 0, 1);
            if (!empty($ask->questions)) {
                $this->meow($ask->questions[0]);
                array_splice($ask->questions, 0, 1);
                file_put_contents($fname, serialize($ask));
                exit;
            } else {
                unlink($fname);
            }
        }
    }
}

?>