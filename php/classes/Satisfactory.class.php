<?php

// Reference: https://satisfactory.fandom.com/wiki/Save_files#Compressed_Save_File_Body_Format

class SatisfactorySave {
    public object $saveFileHeader;
    public object $saveFileBody;
    
    function __construct() {
        $this->saveFileHeader = new SaveFileHeader;
        $this->saveFileBody = new SaveFileBody;
    }

    function __destruct() {

    }
    function get_JSON(bool $prettyprint = true) {
        $arr = Array();
        
        // TODO:
        
        if ($prettyprint) {
            return json_encode($arr, JSON_PRETTY_PRINT);
        } else {
            return json_encode($arr);
        }
    }
}

class SaveFileHeader {
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
        $this->playDurationSeconds = $seconds;
    }

    function get_playDuration(string $timeFormat = null) {
        if (is_null($timeFormat)) {
            return $this->playDurationSeconds;
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
            $this->fEditorObjectVersion = $version;
        } else {
            return false;
        }
    }

    function get_editorObjectVersion() {
        return $this->fEditorObjectVersion;
    }

    function set_modMetaData() {
        // TODO
    }

    function get_modMetaData() {
        if ($this->saveVersion >= 8) {
            // TODO
        } else {
            return false;
        }
    }

    function set_modFlags() {
        // TODO
    }

    function get_modFlags() {
        if ($this->saveVersion >= 8) {
            // TODO
        } else {
            return false;
        }
    }
}

class SaveFileBody {
    public int $size;
    public int $objectHeaderCount;
    public int $objectHeaders;
    public int $objectCount;
    public int $objects;
    public int $collectedObjectsCount;
    public int $collectedObjects;
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