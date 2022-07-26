<?php
ini_set('memory_limit','4048M');
require_once("classes/Satisfactory.class.php");
require_once("classes/unrealSave.class.php");
require_once("classes/int_helper.class.php");

//$saveFile = 'satisfactory.sav';
//$saveFile = 'satisfactory_update4.sav';
$saveFile = 'satisfactory_update5.sav';
//$saveFile = 'satisfactory_update6.sav';


$_save = New SatisfactorySave;
$saveReader = New UnrealReader($saveFile);

/*
 * Save File Header
 */

// Get game save version
$saveHeaderVersion = int_helper::Int32($saveReader->get_chunk(4));
$saveVersion = int_helper::Int32($saveReader->get_chunk(4));
$buildVersion = int_helper::Int32($saveReader->get_chunk(4));

$_save->saveFileHeader->set_version($saveHeaderVersion,$saveVersion,$buildVersion);
$_save->saveFileHeader->set_mapName($saveReader->get_string());
$_save->saveFileHeader->set_mapOptions($saveReader->get_string());
$_save->saveFileHeader->set_sessionName($saveReader->get_string());
$_save->saveFileHeader->set_playDuration(int_helper::Int32($saveReader->get_chunk(4)));
$_save->saveFileHeader->set_saveTimestamp(int_helper::Int64($saveReader->get_chunk(8)));
$_save->saveFileHeader->set_sessionVisibility(int_helper::Int8($saveReader->get_chunk(1)));
if ($saveHeaderVersion >= 7) {
    $_save->saveFileHeader->set_editorObjectVersion(int_helper::Int32($saveReader->get_chunk(4)));
}
if ($saveHeaderVersion >= 8) {
    $_save->saveFileHeader->set_modMetaData($saveReader->get_string());
    $_save->saveFileHeader->set_modFlags(int_helper::Int32($saveReader->get_chunk(4)));
}

/*
 * Save File Body
 */

$UEPackageSignature = bin2hex($saveReader->get_chunk(4));
if($UEPackageSignature != "c1832a9e") {
    throw new Exception("Unexpected UE package signature found! Expecting c1832a9e, Found $UEPackageSignature");
}
$_save->saveFileBody->set_compressedSignature($UEPackageSignature);

$chunksMeta = Array();
while (count($chunksMeta) <= 2) {
    if (count($chunksMeta) < 2) {
        $chunkMeta = Array(
            "chunkPadding" => int_helper::Int32($saveReader->get_chunk(4)),
            "chunkMaxSize" => int_helper::Int32($saveReader->get_chunk(4)),
            "chunkCompressedPadding" => int_helper::Int32($saveReader->get_chunk(4)),
            "chunkCompressedSize" => int_helper::Int32($saveReader->get_chunk(4))
        );
    } else {
        $chunkMeta = Array(
            "chunkPadding" => int_helper::Int32($saveReader->get_chunk(4)),
            "chunkMaxSize" => int_helper::Int32($saveReader->get_chunk(4)),
            "chunkCompressedPadding" => int_helper::Int32($saveReader->get_chunk(4)),
            "chunkCompressedSize" => null
        );
    }
    array_push($chunksMeta,$chunkMeta);
}
$_save->saveFileBody->set_compressedChunkMeta($chunksMeta);

file_put_contents($saveFile.".json",$_save->get_JSON(true));
$pos = $saveReader->get_currentPosition();
$headers = findZlibHeaders($saveReader,'0000789c');

$inflatedData = '';
for ($i = 0; $i <= count($headers); $i++) {
    $zlibHeader = $saveReader->get_zlibHeader();
    $zlib = inflate_init(ZLIB_ENCODING_RAW);
    $status = inflate_get_status($zlib);
    if ($i == 0) {
        $length = $headers[$i] - $pos;
    } else if ($i == count($headers)) {
        $length = $saveReader->get_totalByteCount(); - $headers[$i-1] - 2;
    } else {
        $length = $headers[$i] - $headers[$i-1] - 2;
    }
    $data = $saveReader->get_chunk($length);
    $inflatedData .= inflate_add($zlib,$data);
    $pos = $saveReader->get_currentPosition();
}
unset($saveReader);

// Temporarily output to dat files for testing
file_put_contents(pathinfo($saveFile)['filename']."_inflated.dat",$inflatedData);

require('parseInflatedData.php');

// Final JSON output
file_put_contents(pathinfo($saveFile)['filename'].".json",$_save->get_JSON(true));

exit;

function findZlibHeaders(UnrealReader $stream,string $header = "789c") {
    $arr = Array();
    // Remember our current position in the stream
    $startPos = $stream->get_currentPosition();
    
    // Also set to the current position for use in the loop
    $curPos = $startPos;
    
    // Define the buffer size. Going for 2x the data
    $bufferSize = strlen($header)*2;

    $bufferReadSize = strlen($header);
    
    $buffer = '';

    while($stream->get_totalByteCount() > $stream->get_currentPosition()) {
        if ($bufferSize <= strlen($buffer)) {
            // Trim the buffer by the readsize
            $buffer = substr($buffer,$bufferReadSize,$bufferReadSize);
        }
        $curPos = $stream->get_currentPosition();
        $buffer .= bin2hex($stream->get_chunk($bufferReadSize/2));
        $fPos = strpos($buffer,$header);
        if ($fPos !== false) {
            $realPos = $curPos+($fPos/2)-($bufferReadSize/2);
            array_push($arr,$realPos);
            $buffer = '';
        }
        echo "";       
    } 
    $stream->set_currentPosition($startPos);
    return $arr;
}