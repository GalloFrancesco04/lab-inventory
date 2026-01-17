<?php
// Command class to encapsulate data for deleting an inventory object
class DeleteObjectCommand {
    public int $id;
    // Constructor to initialize the id property
    public function __construct(int $id) 
    { 
        $this->id = $id;
     }
}