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

print_r($save);

exit;
// Below here is test code
// Playing around with zlib stuff


$zlibHeader = $saveReader->get_zlibHeader();

//$data = $saveReader->get_remaining();
$zlib = inflate_init(ZLIB_ENCODING_DEFLATE,["level"=>"0"]);
//while(1) {
    $data = $saveReader->get_chunk(256);
    $inflatedData = inflate_add($zlib,$data);
//}
echo "";