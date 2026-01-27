<?php

class ViewSingleObjectCommand {
    public int $id;

    public function __construct(int $id) {
        $this->id = $id;
    }
}