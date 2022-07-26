<?php
$inflatedSaveFile = 'satisfactory_update5_inflated.dat';

if (!isset($saveFile)) {
    ini_set('memory_limit','4096M');
    require_once("classes/Satisfactory.class.php");
    require_once("classes/unrealSave.class.php");
    require_once("classes/int_helper.class.php");
    $inflatedReader = New UnrealReader($inflatedSaveFile);
    $_save = New SatisfactorySave;
} else {
    $inflatedReader = New UnrealReader("php://temp",$inflatedData);
}

$_save->saveFileBody->size = int_helper::Int32($inflatedReader->get_chunk(4));
$_save->saveFileBody->objectHeaderCount = int_helper::Int32($inflatedReader->get_chunk(4));

for ($i = 0;$i < $_save->saveFileBody->objectHeaderCount;$i++) {
    $objectHeader = new ObjectHeader();
    $objectHeader->headerType = int_helper::Int32($inflatedReader->get_chunk(4));
    if ($objectHeader->headerType == 1) {
        // Actor Object
        $actorHeader = new ActorHeader();
        $actorHeader->typePath = $inflatedReader->get_string();
        $actorHeader->rootObject = $inflatedReader->get_string();
        $actorHeader->instanceName = $inflatedReader->get_string();
        $actorHeader->needsTransform = int_helper::Int32($inflatedReader->get_chunk(4));
        $actorHeader->rotationX = int_helper::Float32($inflatedReader->get_chunk(4));
        $actorHeader->rotationY = int_helper::Float32($inflatedReader->get_chunk(4));
        $actorHeader->rotationZ = int_helper::Float32($inflatedReader->get_chunk(4));
        $actorHeader->rotationW = int_helper::Float32($inflatedReader->get_chunk(4));
        $actorHeader->positionX = int_helper::Float32($inflatedReader->get_chunk(4));
        $actorHeader->positionY = int_helper::Float32($inflatedReader->get_chunk(4));
        $actorHeader->positionZ = int_helper::Float32($inflatedReader->get_chunk(4));
        $actorHeader->scaleX = int_helper::Float32($inflatedReader->get_chunk(4));
        $actorHeader->scaleY = int_helper::Float32($inflatedReader->get_chunk(4));
        $actorHeader->scaleZ = int_helper::Float32($inflatedReader->get_chunk(4));
        $actorHeader->placedInLevel = int_helper::Int32($inflatedReader->get_chunk(4));

        $objectHeader->header = $actorHeader;
    } else if ($objectHeader->headerType == 0) {
        // Component Object
        $componentHeader = new ComponentHeader();
        $componentHeader->typePath = $inflatedReader->get_string();
        $componentHeader->rootObject = $inflatedReader->get_string();
        $componentHeader->instanceName = $inflatedReader->get_string();
        $componentHeader->parentActorName = $inflatedReader->get_string();

        $objectHeader->header = $componentHeader;
    }
    array_push($_save->saveFileBody->objectHeaders,$objectHeader);
    echo "";
}

$_save->saveFileBody->objectCount = int_helper::Int32($inflatedReader->get_chunk(4));

for ($i = 0;$i < $_save->saveFileBody->objectCount;$i++) {
    $sPos = $inflatedReader->get_currentPosition();
    
    if ($_save->saveFileBody->objectHeaders[$i]->headerType == 1) {
        // Actor Object
        
        $object = new ActorObject();
        $object->size = int_helper::Int32($inflatedReader->get_chunk(4));
        $object->parentObjectRoot = $inflatedReader->get_string();
        $object->parentObjectName = $inflatedReader->get_string();
        $object->componentCount = int_helper::Int32($inflatedReader->get_chunk(4));
        if ($object->componentCount != 0) {
            $object->components;
        }
        //$object->properties;
        $remainingBytes = $object->size - ($inflatedReader->get_currentPosition() - $sPos);
        $object->trailingBytes = $inflatedReader->get_chunk($remainingBytes);
    } else if ($_save->saveFileBody->objectHeaders[$i]->headerType == 0) {
        // Component Object
        $componentHeader = new ComponentObject();
        $object->size = int_helper::Int32($inflatedReader->get_chunk(4));

        //$object->properties;
        $remainingBytes = $object->size - ($inflatedReader->get_currentPosition() - $sPos);
        $object->trailingBytes = $inflatedReader->get_chunk($remainingBytes);
    }
    array_push($_save->saveFileBody->objects,$object);
    echo "";
}

$pos = $inflatedReader->get_currentPosition();
exit;