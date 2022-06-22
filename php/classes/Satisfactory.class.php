<?php

class SatisfactorySave {
    // Header based on UE docs and visible data in the .sav files
    private int $saveGameVersion = 0;
    private int $packageVersion = 0;
    private int $customFormatVersion = 0;
    private string $saveGameType = "";
    private array $sessionProperties = [];
    private string $sessionName = "";
    private int $playDurationSeconds = 0;
    private int $saveDateTime = 0;
    private bool $sessionVisibility = false; // stored as Byte
    private int $fEditorObjectVersion = 0; // if saveVersion >= 7
    private string $modMetaData = ''; // if saveVersion >= 8
    private bool $isModdedSave = false; // if saveVersion >= 8 / stored as int


    function set_version(int $saveGameVersion, int $packageVersion, int $customFormatVersion) {
        $this->saveGameVersion = $saveGameVersion;
        $this->packageVersion = $packageVersion;
        $this->customFormatVersion = $customFormatVersion;
    }

    function version() {
        return Array(
            "saveGameVersion" => $this->saveGameVersion,
            "packageVersion" => $this->packageVersion,
            "customFormatVersion" => $this->customFormatVersion
        );
    }
    function versionString() {
        return "V".$this->saveGameVersion.".".$this->packageVersion.".".$this->customFormatVersion;
    }

    function set_saveGameType(string $string) {
        $this->saveGameType = $string;
    }
    function set_sessionProperties(array $props) {
        if(!is_array($props)) {
            throw new Exception("Expected value is not an array");
        }
        if (count($props) == 0) {
            throw new Exception("Expected values, none received in array");
        }
        $this->sessionProperties = $props;
    }
    function set_sessionName(string $string) {
        $this->sessionName = $string;
    }
    function set_playDurationSeconds(int $seconds) {
        $this->playDurationSeconds = $seconds;
    }
    function set_saveDateTime(int $timecode) {
        $this->saveDateTime = $timecode;
    }
    function set_sessionVisibility(bool $visibility) {
        $this->sessionVisibility = $visibility;
    }
    function set_fEditorObjectVersion(int $version) {
        $this->fEditorObjectVersion = $version;
    }
    function set_modMetadata(string $object) {
        $this->modMetaData = $object;
    }
    function set_isModdedSave(bool $isModded) {
        $this->isModdedSave = $isModded;
    }

    function saveGameType() {
        return $this->saveGameType;
    }
    function sessionProperties() {
        return $this->sessionProperties;
    }
    function sessionProperty(string $key) {
        if (!isset($this->sessionProperties[$key])) {
            return false;
        }
        return $this->sessionProperties[$key];
    }
    function sessionName() {
        return $this->sessionName;
    }

    function get_JSON($prettyprint) {
        $arr = Array(
            "saveGameVersion" => $this->saveGameVersion,
            "packageVersion" => $this->packageVersion,
            "customFormatVersion" => $this->customFormatVersion,
            "saveGameType" => $this->saveGameType,
            "sessionProperties" => $this->sessionProperties,
            "sessionName" => $this->sessionName,
            "playDurationSeconds" => $this->playDurationSeconds,
            "saveDateTime" => $this->saveDateTime,
            "sessionVisibility" => $this->sessionVisibility,
        );
        if ($this->saveGameVersion >= 7) {
            $arr["fEditorObjectVersion"] = $this->fEditorObjectVersion;
        }
        if ($this->saveGameVersion >= 8) {
            $arr["modMetaData"] = $this->modMetaData;
            $arr["isModdedSave"] = $this->isModdedSave;
        }
        if ($prettyprint) {
            return json_encode($arr, JSON_PRETTY_PRINT);
        } else {
            return json_encode($arr);
        }
    }
}