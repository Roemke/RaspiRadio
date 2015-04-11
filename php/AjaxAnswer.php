<?php

class AjaxAnswer implements JsonSerializable
{
  public $infoText;
  public $result;
  public $state; //0 ok, 1 error
  function __construct($text="", $result="", $state = 0)
  {
    $this->infoText = $text;
    $this->result = $result;
    $this->state = $state; //state to 1 means error
  }
  // function called when encoded with json_encode
  public function jsonSerialize()
  {
    return get_object_vars($this);
  }
}
?>
