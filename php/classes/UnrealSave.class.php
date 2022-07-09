<?php

class UnrealReader {
    public $stream;
    function __construct(string $filename,$content = '') {
        if ($filename != "php://temp") {
            if (!is_file($filename)) {
                throw new Exception("File does not exist");
            }
            if (pathinfo($filename)['extension'] != 'sav') {
                throw new Exception("Invalid extension type. Must be .sav");
            }
            if (!$this->stream = fopen($filename, 'rb')) {
                throw new Exception("Unable to open specified save file");
            }
        } else {
            if (!$this->stream = fopen($filename, 'rb+')) {
                throw new Exception("Unable to open specified save file");
            } else {
                fwrite($this->stream,$content);
                rewind($this->stream);
            }
        }
        
    }

    function __destruct() {
        fclose($this->stream);
    }

    function get_string(string $byteLength) {
        if ($byteLength == 0) {
            $data = "";
        } else {
        $pos = $this->get_currentPosition();
        $data = fread($this->stream,$byteLength-1);
        $term = bin2hex(fread($this->stream,1));
        if ($term != '00') {
            throw new Exception("Invalid string terminator. expecting \\0 (Str: 00)");
        }
        }
        return $data;
    }

    function get_zlibHeader() {
        
        $skipped = 0;
        $data = "";
        while (true) {
            $data = fread($this->stream,1);
            if (bin2hex($data) == '78') {
                break;
            } else {
                $skipped++;
            }
        }
        $cmf = $this->parse_zlibCMF($data);
        $flg = $this->parse_zlibCINFO(fread($this->stream,1));
        return array_merge($cmf,$flg);
    }

    function parse_zlibCMF($byte) {
        //$bin = hex2bin(ord($byte));
        //$bin = str_pad($bin, 8, 0, STR_PAD_LEFT);
        $data = Array(
            "CINFO" => '',
            "CM" => ''
        );

        for ($i=0; $i<4; $i++) {
            $data['CINFO'] .= (ord($byte) & (1<<$i))>>$i;
        }
        for ($i=4; $i<8; $i++) {
            $data['CM'] .= (ord($byte) & (1<<$i))>>$i;
        }
        
        return $data;
    }

    function parse_zlibCINFO($byte) {
        $flags = Array(
            "FCHECK" => '',
            "FDICT" => '',
            "FLEVEL" => '',
        );

        for ($i=0; $i<5; $i++) {
            $flags['FCHECK'] .= (ord($byte) & (1<<$i))>>$i;
        }
        for ($i=5; $i<6; $i++) {
            $flags['FDICT'] .= (ord($byte) & (1<<$i))>>$i;
        }
        $flags['FDICT'] = boolval($flags['FDICT']);
        for ($i=6; $i<8; $i++) {
            $flags['FLEVEL'] .= (ord($byte) & (1<<$i))>>$i;
        }
        switch ($flags['FLEVEL']) {
            case '00':
                $flags['FLEVEL'] = "fastest";
                break;
            case '01':
                $flags['FLEVEL'] = "fast";
                break;
            case '11':
                $flags['FLEVEL'] = "maximum";
                break;
            default:
                $flags['FLEVEL'] = "default";
                break;
        }
        return $flags;
    }

    function get_zlibDictID() {
        // TODO: Add dictionary parser for zlib
    }

    function get_remainingBytes() {
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

    function get_currentPosition() {
        $data = ftell($this->stream);
        
        return $data;
    }
    function get_totalByteCount() {
        $data = fstat($this->stream);
        return $data['size'];
    }

    function get_UEProperties(string $properties) {
        $regex = '/(?:\?)(?<keys>[\w\s\d]+)(?:\=?)(?<values>[\w\s\d]*)/';
        preg_match_all($regex, $properties, $matches);
        if ($matches === false) {
            return [];
        }
        return array_combine($matches['keys'],$matches['values']);
    }

    function get_object($terminator) {
        // check that there is enough bytes left to read an object
        $byteLeft = $this->get_totalByteCount() - $this->get_currentPosition();
        if ($byteLeft == 73130) {
            echo "";
            return null;
        }
        echo "";
        $object = new SatisfactoryGameObject;
        $object->set_object4bytes(int_helper::uInt32($this->get_chunk(4)));
        $object->set_object4bytes2(int_helper::uInt32($this->get_chunk(4)));

        $object->set_objectPath($this->get_string(int_helper::uInt32($this->get_chunk(4))));
        if ($object->get_object4bytes2() != 1) {
            $object->set_object4bytes3(int_helper::uInt32($this->get_chunk(4)));
            $object->set_object4bytes4(int_helper::uInt32($this->get_chunk(4)));
            $object->set_object4bytes5(int_helper::uInt32($this->get_chunk(4)));
        }  
        $strLength = int_helper::uInt32($this->get_chunk(4));
        
        $object->set_objectScript($this->get_string($strLength));
        $object->set_objectName($this->get_string(int_helper::uInt32($this->get_chunk(4))));
        $object->set_objectSettings($this->get_string(int_helper::uInt32($this->get_chunk(4))));
        $str = '';
        while (!$this->is_terminator($terminator)) {
            $str .= $this->get_chunk(4);
        }
        $object->object_RemBytes($str);
        $t = bin2hex($this->get_chunk(12));
        return $object;
    }

    private function is_terminator($hexTerminator) {
        $terminatorFirstByte = substr($hexTerminator,0,8);
        $remBytes = (strlen($hexTerminator)/2)-4;
        $byte = $this->get_chunk(4);
        $byte = bin2hex($byte);
        if ($byte == $terminatorFirstByte) {
            $lastbytes = $this->get_chunk($remBytes);
            $lastbytes = bin2hex($lastbytes);
            fseek($this->stream, -($remBytes+4), SEEK_CUR);
            if ($byte.$lastbytes == $hexTerminator) {
                return true;
            }
        } else {
            fseek($this->stream, -4, SEEK_CUR);
        }
        return false;
    }
}