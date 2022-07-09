<?php
require_once("classes/Satisfactory.class.php");
require_once("classes/SatisfactoryObject.class.php");
require_once("classes/unrealSave.class.php");
require_once("classes/int_helper.class.php");

$saveFile = 'satisfactory.sav';

$save = New SatisfactorySave;

$saveReader = New UnrealReader($saveFile);

// Get game save version
$saveGameVersion = int_helper::uInt32($saveReader->get_chunk(4));
$packageVersion = int_helper::uInt32($saveReader->get_chunk(4));
$customFormatVersion = int_helper::uInt32($saveReader->get_chunk(4));
$save->set_version($saveGameVersion,$packageVersion,$customFormatVersion);

// Read remaining header data
$save->set_saveGameType($saveReader->get_string(int_helper::uInt32($saveReader->get_chunk(4))));
$save->set_sessionProperties($saveReader->get_UEProperties($saveReader->get_string(int_helper::uInt32($saveReader->get_chunk(4)))));
$save->set_sessionName($saveReader->get_string(int_helper::uInt32($saveReader->get_chunk(4))));
$save->set_playDurationSeconds(int_helper::uInt32($saveReader->get_chunk(4)));
$save->set_saveDateTime(int_helper::uInt32($saveReader->get_chunk(8)));
$save->set_sessionVisibility($saveReader->get_chunk(1));
if ($saveGameVersion >= 7) {
    $save->set_fEditorObjectVersion(int_helper::uInt32($saveReader->get_chunk(4)));
}
if ($saveGameVersion >= 8) {
    $save->set_isModdedSave(int_helper::uInt32($saveReader->get_chunk(4)));
    $save->set_modMetaData($saveReader->get_string(int_helper::uInt32($saveReader->get_chunk(4))));
    
}
// TODO: Account for the 73 Bytes between the above and the zlib header

// $Read the ZLIB header
$zlibHeader = $saveReader->get_zlibHeader();

$zlib = inflate_init(ZLIB_ENCODING_RAW);
$status = inflate_get_status($zlib);

// inflate the rest of the data
$inflatedData = '';
while($status = inflate_get_status($zlib) == 0) {
    $data = $saveReader->get_chunk(4096);
    $inflatedData .= inflate_add($zlib,$data);
}

// Store the inflated data in a stream reader
$inflatedReader = New UnrealReader("php://temp",$inflatedData);
// Temporarily output to dat files for testing
file_put_contents(pathinfo($saveFile)['filename']."_inflated.dat",$inflatedData);

unset($saveReader);
unset($inflatedData);

$object = "";
while (1) {
    $object = $inflatedReader->get_object("0000803f0000803f0000803f");
    if (is_null($object)) {
        break;
    }
    $save->add_object($object);
}

// Final JSON output
file_put_contents(pathinfo($saveFile)['filename'].".json",$save->get_JSON(true));
