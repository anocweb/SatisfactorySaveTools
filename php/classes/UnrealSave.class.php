<?php

class UnrealReader {
    public $stream;
    function __construct(string $filename,$content = '') {
        if ($filename != "php://temp") {
            if (!is_file($filename)) {
                throw new Exception("File does not exist");
            }
            if (!in_array(pathinfo($filename)['extension'],['sav','dat'])) {
                throw new Exception("Invalid extension type. Must be .sav or .dat");
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

    function get_string($nullTerminator = '00', int $bytes = 4) {
        $strLength = int_helper::Int32($this->get_chunk($bytes));
        if ($strLength == 0) {
            $data = '';
        } else {
            $nullByteCount = strlen($nullTerminator)/2;
            $data = fread($this->stream,$strLength-$nullByteCount);
            $term = bin2hex(fread($this->stream,$nullByteCount));
            if ($nullByteCount != 0 && $term != $nullTerminator) {
                throw new Exception("Invalid string terminator. expecting (Hex: '$nullTerminator')");
            }
        }
        return $data;
    }

    function get_num($numType = "Int32") {
        $val = 0;
        switch ($numType) {
            case "Int8":
                $val = int_helper::Int8($this->get_chunk(1));
                break;

            case "Int32":
                $val = int_helper::Int32($this->get_chunk(4));
                break;
 
            case "Int64":
                $val = int_helper::Int64($this->get_chunk(8));
                break;

            case "Float32":
                $val = int_helper::Float32($this->get_chunk(4));
                break;

            case "Float64":
                $val = int_helper::Float64($this->get_chunk(8));
                break;

            default:
                throw new Error("Unsupported Type!");
                // TODO: Add support for other data types
                break;
        }
        return $val;
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
        if ($numBytes == 0) {
            throw new Exception("Cannot retreive empty or null chunk!");
        }
        $data = fread($this->stream,$numBytes);
        
        return $data;
    }

    function get_currentPosition() {
        $data = ftell($this->stream);
        
        return $data;
    }
    
    function set_currentPosition($pos) {
        fseek($this->stream,$pos);
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
}