<?php

// Reference: https://satisfactory.fandom.com/wiki/Save_files#Compressed_Save_File_Body_Format

class SatisfactorySave {
    public object $saveFileHeader;
    public object $saveFileBody;
    
    function __construct() {
        $this->saveFileHeader = new SaveFileHeader();
        $this->saveFileBody = new SaveFileBody();
    }

    function __destruct() {

    }

    function get_JSON(bool $prettyprint = true) {
        $arr = Array();
        $arr["saveFileHeader"] = $this->saveFileHeader->get_Array();
        $arr['saveFileBody'] =  $this->saveFileBody->get_Array();
        // TODO:
        
        if ($prettyprint) {
            return json_encode($arr, JSON_PRETTY_PRINT);
        } else {
            return json_encode($arr);
        }
    }
}

class SaveFileHeader extends SatisfactorySave {
    public int $saveHeaderVersion;
    public int $saveVersion;
    public int $buildVersion;
    public string $mapName;
    public string $mapOptions;
    public string $sessionName;
    public int $playedSeconds;
    public int $saveTimestamp;
    public int $sessionVisibility;
    public int $editorObjectVersion;
    public string $modMetaData;
    public int $modFlags;

    function __construct() {
    
    }

    function __destruct() {

    }

    function set_version(int $headerVersion, int $saveVersion, int $buildVersion) {
        $this->saveHeaderVersion = $headerVersion;
        $this->saveVersion = $saveVersion;
        $this->buildVersion = $buildVersion;
    }

    function get_version(bool $returnString = false) {
        if (!$returnString) {
        return Array(
            "saveHeaderVersion" => $this->saveHeaderVersion,
            "saveVersion" => $this->saveVersion,
            "buildVersion" => $this->buildVersion
        );
        } else {
            return "V: ".$this->saveHeaderVersion.".".$this->saveVersion.".".$this->buildVersion;
        }
    }

    function set_mapName(string $string) {
        $this->mapName = $string;
    }

    function get_mapName() {
        return $this->mapName;
    }

    function set_mapOptions(string $string) {
        $this->mapOptions = $string;
    }

    function get_mapOptions() {
        return $this->mapOptions;
    }

    function set_sessionName(string $string) {
        $this->sessionName = $string;
    }

    function get_sessionName() {
        return $this->sessionName;
    }

    function set_playDuration(int $seconds) {
        $this->playedSeconds = $seconds;
    }

    function get_playDuration(string $timeFormat = null) {
        if (is_null($timeFormat)) {
            return $this->playedSeconds;
        }

        throw new Exception("Not implemented yet");
    }

    function set_saveTimestamp(int $ticks) {
        $this->saveTimestamp = $ticks;
    }

    function get_saveTimestamp() {
        return $this->saveTimestamp;
    }

    function set_sessionVisibility(int $visibility) {
        $this->sessionVisibility = $visibility;
    }

    function get_sessionVisibility() {
        return $this->sessionVisibility;
    }

    function set_editorObjectVersion(int $version) {// if saveVersion >= 7
        if ($this->saveVersion >= 7) {
            $this->editorObjectVersion = $version;
        } else {
            return false;
        }
    }

    function get_editorObjectVersion() {
        return $this->editorObjectVersion;
    }

    function set_modMetaData(string $metaString) {
        $this->modMetaData = $metaString;
    }

    function get_modMetaData() {
        if ($this->saveVersion >= 8) {
            return $this->modMetaData;
        } else {
            return false;
        }
    }

    function set_modFlags(int $flags) {
        $this->modFlags = $flags;
    }

    function get_modFlags() {
        if ($this->saveVersion >= 8) {
            return $this->modFlags;
        } else {
            return false;
        }
    }

    function get_Array() {
        $arr = Array(
            "saveHeaderVersion" => $this->saveHeaderVersion,
            "saveVersion" => $this->saveVersion,
            "buildVersion" => $this->buildVersion,
            "mapName" => $this->mapName,
            "mapOptions" => $this->mapOptions,
            "sessionName" => $this->sessionName,
            "playedSeconds" => $this->playedSeconds,
            "saveTimestamp" => $this->saveTimestamp,
            "sessionVisibility" => $this->sessionVisibility
        );
        if ($this->saveHeaderVersion >= 7) {
            $arr["editorObjectVersion"] = $this->editorObjectVersion;
        }
        if ($this->saveHeaderVersion >= 8) {
            $arr["modMetaData"] = $this->modMetaData;
            $arr["modFlags"] = $this->modFlags;
        }
        return $arr;
    }
}

class SaveFileBody {
    public string $compressedSignature;
    public array $compressedChunkMeta;
    public int $size;
    public int $objectHeaderCount;
    public array $objectHeaders;
    public int $objectCount;
    public array $objects;
    public int $collectedObjectsCount;
    public array $collectedObjects;

    function __construct() {
        $this->objectHeaders = Array();
        $this->objects = Array();
        $this->collectedObjects = Array();
    }

    function __destruct() {

    }

    function set_compressedSignature(string $sig) {
        $this->compressedSignature = $sig;
    }

    function get_compressedSignature() {
        return $this->compressedSignature;
    }

    function set_compressedChunkMeta(array $arr) {
        $this->compressedChunkMeta = $arr;
    }

    function get_compressedChunkMeta() {
        return $this->compressedChunkMeta;
    }
    
    function get_Array() {
        $arr = Array(
            "compressedSignature" => $this->compressedSignature,
            "compressedChunkMeta" => $this->compressedChunkMeta
        );
        return $arr;
    }
}

class ObjectHeader {
    public int $headerType;
    public object $header;
}

class ActorHeader {
    public string $typePath;
    public string $rootObject;
    public string $instanceName;
    public int $needsTransform;
    public float $rotationX;
    public float $rotationY;
    public float $rotationZ;
    public float $rotationW;
    public float $positionX;
    public float $positionY;
    public float $positionZ;
    public float $scaleX;
    public float $scaleY;
    public float $scaleZ;
    public int $placedInLevel;
}

class ActorObject {
    public int $size;
    public string $parentObjectRoot;
    public string $parentObjectName;
    public int $componentCount;
    public object $components;
    public object $properties;
    public string $trailingBytes;
}

class ComponentHeader {
    public string $typePath;
    public string $rootObject;
    public string $instanceName;
    public string $parentActorName;
}

class ComponentObject {
    public int $size;
    public object $properties;
    public string $trailingBytes;
}

class PropertyList {
    public int $size;
    public int $index;
    public string $type;
    public int $padding;
    public int $length;
    public array $elements;
}

class BoolProperty {
    public int $startPadding;
    public int $index;
    public int $value;
    public int $endPadding;
}

class ByteProperty {
    public int $size;
    public int $index;
    public string $type;
    public int $padding;
    public $value;
}

class EnumProperty {
    public int $size;
    public int $index;
    public string $type;
    public int $padding;
    public string $value;
}

class FloatProperty {
    public int $size;
    public int $index;
    public int $padding;
    public float $value;
}

class IntProperty {
    public int $size;
    public int $index;
    public int $padding;
    public int $value;
}

class Int64Property {
    public int $size;
    public int $index;
    public int $padding;
    public int $value;
}

class MapProperty {
    public int $size;
    public int $index;
    public string $keyType;
    public string $valueType;
    public int $padding;
    public int $modeType;
    public int $elementCount;
    public array $mapElements;
}

class NameProperty {
    public int $size;
    public int $index;
    public int $padding;
    public string $value;
}

class ObjectProperty {
    public int $size;
    public int $index;
    public int $padding;
    public string $levelName;
    public string $pathName;
}
class StrProperty {
    public int $size;
    public int $index;
    public string $type;
    public int $padding;
    public int $padding2; // ????
    public int $padding3; // ????
    public array $typedData;
}
class StructProperty {
    public int $size;
    public int $index;
    public int $padding;
    public float $value;
}
class TextProperty {
    public int $size;
    public int $index;
    public int $padding;
    public int $flags;
    public int $historyType;
    public int $isTextCultureInvariant;
    public string $value;
}