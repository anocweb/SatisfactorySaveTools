<?php

class SatisfactorySave {
    // Header based on UE docs and visible data in the .sav files
    private int $saveGameVersion = 0;
    private int $packageVersion = 0;
    private int $customFormatVersion = 0;
    private string $saveGameType = "";
    private array $sessionProperties = [];
    private string $sessionName = "";

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

}