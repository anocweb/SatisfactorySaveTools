<?php
require_once("classes/Satisfactory.class.php");
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

//$data = $saveReader->get_remaining();
$zlib = inflate_init(ZLIB_ENCODING_RAW);
$status = inflate_get_status($zlib);

$inflatedData = '';
while($status = inflate_get_status($zlib) == 0) {
    $data = $saveReader->get_chunk(256);
    $inflatedData .= inflate_add($zlib,$data);
}

// Temporarily output to files for testing
file_put_contents(pathinfo($saveFile)['filename'].".json",$save->get_JSON(true));
file_put_contents(pathinfo($saveFile)['filename']."_inflated.dat",$inflatedData);



// Objects in inflated data
// size type description            : Example from save
// ---------------------------------------------------------------
// 8 bytes ??                       :
// 4 Bytes uint32 string length     : 78
// *-1 Bytes string                 : Level /Game/FactoryGame/Map/GameLevel01/Tile_X3_Y0.Tile_X3_Y0:PersistentLevel
// 1 Byte terminator \00            : \00
// 4 bytes ?? Pos X?                : 57856
// 4 bytes ?? Pos Y?                : 306
// 4 bytes ?? Pos Z?                : 1
// 4 Bytes uint32 string length     : 36
// *-1 Bytes string                 : /Script/FactoryGame.FGWorldSettings
// 1 Byte terminator \00            : \00
// 4 Bytes uint32 string length     : 11
// *-1 Bytes string                 : Tile_X3_Y0
// 1 Byte terminator \00            : \00
// 4 Bytes uint32 string length     : 43
// *-1 Bytes string                 : Tile_X3_Y0:PersistentLevel.FGWorldSettings
// 1 Byte terminator \00            : \00
// 32 Bytes ??                      :
// 12 Bytes terminator Object End   : \00\00\80\3f\00\00\80\3f\00\00\80\3f
