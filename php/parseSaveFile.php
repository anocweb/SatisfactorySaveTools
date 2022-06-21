<?php
require_once("classes/Satisfactory.class.php");
require_once("classes/unrealSave.class.php");

$saveFile = 'satisfactory.sav';

$save = New SatisfactorySave;

$saveReader = New UnrealReader($saveFile);

// Get game save version
$saveGameVersion = $saveReader->get_uint8();
$packageVersion = $saveReader->get_uint8();
$customFormatVersion = $saveReader->get_uint8();
$save->set_version($saveGameVersion,$packageVersion,$customFormatVersion);

// Read remaining header data
$save->set_saveGameType($saveReader->get_string());
$save->set_sessionProperties($saveReader->get_UEProperties());
$save->set_sessionName($saveReader->get_string());

// TODO: Account for the 73 Bytes between the above and the zlib header

// $Read the ZLIB header
$zlibHeader = $saveReader->get_zlibHeader();

//$data = $saveReader->get_remaining();
$zlib = inflate_init(ZLIB_ENCODING_RAW);
$status = inflate_get_status($zlib);

$inflatedData = '';
while($status = inflate_get_status($zlib) == 0) {
    $data = $saveReader->get_chunk(256);
    $inflatedData .= inflate_add($zlib,$data);
}

// Temporarily output to files for testing
file_put_contents(pathinfo($saveFile)['filename'],$save->get_JSON(true));
file_put_contents(pathinfo($saveFile)['filename']."_inflated.dat",$inflatedData);