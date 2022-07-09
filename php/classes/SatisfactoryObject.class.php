<?php
class SatisfactoryGameObject {

    
private int $object_4bytes;
private int $object_4bytes2;
private string $object_path = "";
private int $object_4bytes3;
private int $object_4bytes4;
private int $object_4bytes5;
private string $object_script = "";
private string $object_name = "";
private string $object_settings = "";
private $object_RemBytes = "";

function set_object4bytes(string $string) {
    $this->object_4bytes = $string;
}

function set_object4bytes2 (string $string) {
    $this->object_4bytes2 = $string;
}

function set_object4bytes3 (string $string) {
    $this->object_4bytes3 = $string;
}

function set_object4bytes4 (string $string) {
    $this->object_4bytes4 = $string;
}

function set_object4bytes5 (string $string) {
    $this->object_4bytes5 = $string;
}

function object_RemBytes(string $string) {
    $this->object_RemBytes = bin2hex($string);
}

function set_objectName(string $string) {
    $this->object_name = $string;
}

function set_objectPath(string $string) {
    $this->object_path = $string;
}

function set_objectSettings(string $string) {
    $this->object_settings = $string;
}

function set_objectScript(string $string) {
    $this->object_script = $string;
}

function get_object4bytes() {
    return $this->object_4bytes;
}

function get_object4bytes2 () {
    return $this->object_4bytes2;
}

function get_objectPath () {
    return $this->object_path;
}

function get_object () {
    $arr = [
        $this->object_4bytes,
        $this->object_4bytes2,
        $this->object_path,
        $this->object_4bytes3,
        $this->object_4bytes4,
        $this->object_4bytes5,
        $this->object_script,
        $this->object_name,
        $this->object_settings,
        $this->object_RemBytes,
    ];
    return $arr;
}

}