<?php

namespace stickmeo\MeowBot;
use stickmeo\MeowBot\Chat;

class Conversation extends Chat{
    public function __construct($accessToken, $payload) {
        parent::__construct($accessToken, $payload);
        $this->ask = new \stdClass();
        $this->ask->questions = [];
        $this->ask->callbacks = [];
        $this->conversation = null;
    }

    public function ask($quest, $callback) {
        $serializer = new \SuperClosure\Serializer();
        array_push($this->ask->questions, $quest);
        array_push($this->ask->callbacks, $serializer->serialize($callback));
    }

    public function end() {
        $this->meow($this->ask->questions[0]);
        array_splice($this->ask->questions, 0, 1);
        $file = fopen('src/temp/ask_'.$this->payload->sender->id, 'w');
        fwrite($file, serialize($this->ask));
        fclose($file);
    }
}

?>