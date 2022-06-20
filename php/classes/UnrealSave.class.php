<?php

class UnrealReader {
    public $stream;
    function __construct($filename) {
        if (!is_file($filename)) {
            throw new Exception("File does not exist");
        }
        if (pathinfo($filename)['extension'] != 'sav') {
            throw new Exception("Invalid extension type. Must be .sav");
        }
        if (!$this->stream = fopen($filename, 'rb')) {
            throw new Exception("Unable to open specified save file");
        }
    }

    function __destruct() {
        fclose($this->stream);
    }

    function get_int8() {
        $data = unpack("c",fread($this->stream,4));
        
        return $data[1];
    }
    function get_uint8() {
        $data = unpack("C",fread($this->stream,4));
        
        return $data[1];
    }

    function get_string() {
        $byteLength = $this->get_uint8();
        $data = fread($this->stream,$byteLength-1);
        $term = bin2hex(fread($this->stream,1));
        if ($term != '00') {
            throw new Exception("Invalid string terminator. expecting \\0");
        }
        
        return $data;
    }

    function get_zlibHeader() {
        
        $skipped = 0;
        $data = "";
        while ($data = bin2hex(fread($this->stream,1)) != '78') {
            $skipped++;
        }
        $compressionLevel = bin2hex(fread($this->stream,1));
        
        return "78".$compressionLevel;
    }

    function get_zlibDictID() {

    }

    function get_remaining() {
        $data ="";
        while(!feof($this->stream)) {
            $data .= fread($this->stream,4096);
        }
        return $data;
    }
    function get_chunk(int $numBytes) {
        $data = fread($this->stream,$numBytes);
        
        return $data;
    }

    function get_UEProperties() {
        $properties = $this->get_string();
        $regex = '/(?:\?)(?<keys>[\w\s\d]+)(?:\=?)(?<values>[\w\s\d]*)/';
        preg_match_all($regex, $properties, $matches);
        if ($matches === false) {
            return [];
        }
        return array_combine($matches['keys'],$matches['values']);
    }
}