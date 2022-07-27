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

$_save->saveFileBody->size = $inflatedReader->get_num();
$_save->saveFileBody->objectHeaderCount = $inflatedReader->get_num();

for ($i = 0;$i < $_save->saveFileBody->objectHeaderCount;$i++) {
    $objectHeader = new ObjectHeader();
    $objectHeader->headerType = $inflatedReader->get_num();
    if ($objectHeader->headerType == 1) {
        // Actor Object
        $actorHeader = new ActorHeader();
        $actorHeader->typePath = $inflatedReader->get_string();
        $actorHeader->rootObject = $inflatedReader->get_string();
        $actorHeader->instanceName = $inflatedReader->get_string();
        $actorHeader->needsTransform = $inflatedReader->get_num();
        $actorHeader->rotationX = $inflatedReader->get_num("Float32");
        $actorHeader->rotationY = $inflatedReader->get_num("Float32");
        $actorHeader->rotationZ = $inflatedReader->get_num("Float32");
        $actorHeader->rotationW = $inflatedReader->get_num("Float32");
        $actorHeader->positionX = $inflatedReader->get_num("Float32");
        $actorHeader->positionY = $inflatedReader->get_num("Float32");
        $actorHeader->positionZ = $inflatedReader->get_num("Float32");
        $actorHeader->scaleX = $inflatedReader->get_num("Float32");
        $actorHeader->scaleY = $inflatedReader->get_num("Float32");
        $actorHeader->scaleZ = $inflatedReader->get_num("Float32");
        $actorHeader->placedInLevel = $inflatedReader->get_num();

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

$_save->saveFileBody->objectCount = $inflatedReader->get_num();

for ($i = 0;$i < $_save->saveFileBody->objectCount;$i++) {
    $sPos = $inflatedReader->get_currentPosition();
    
    if ($_save->saveFileBody->objectHeaders[$i]->headerType == 1) {
        // Actor Object
        
        $object = new ActorObject();
        $object->size = $inflatedReader->get_num();
        $object->parentObjectRoot = $inflatedReader->get_string();
        $object->parentObjectName = $inflatedReader->get_string();
        $object->componentCount = $inflatedReader->get_num();
        if ($object->componentCount != 0) {
            $object->components;
        }
        $object->properties = Array();
        
        $list = new PropertyList();
        $list->name = $inflatedReader->get_string();
        $list->type = $inflatedReader->get_string();
        switch ($list->type) {
            case "StructProperty":
                $property = new StructProperty();
                $property->size = $inflatedReader->get_num();
                $property->index = $inflatedReader->get_num();
                $property->type = $inflatedReader->get_string();
                $property->padding = $inflatedReader->get_num();
                $property->padding2 = $inflatedReader->get_num();
                $property->padding3 = $inflatedReader->get_num();
                
                switch ($property->type) {
                    default:
                        $data = $inflatedReader->get_chunk($property->size);
                        $none = $inflatedReader->get_chunk(4);
                        if ($none !== "None") {
                            throw new Error("Missing none!");
                        }
                        $property->properties = (string)$data;
                        $list->typedData = $property; 
                }
                break;
            
            default:
                break;
        }
        array_push($object->properties, $list);
        
        //TODO: Loop through properties while bytes are remaining.
        //TODO: Refactor code to be better thought out. This is turning into spaghetti and is not easily readable.

        $remainingBytes = $object->size - ($inflatedReader->get_currentPosition() - $sPos);
        $object->trailingBytes = $inflatedReader->get_chunk($remainingBytes);
    } else if ($_save->saveFileBody->objectHeaders[$i]->headerType == 0) {
        // Component Object
        $componentHeader = new ComponentObject();
        $object->size = $inflatedReader->get_num();

        //$object->properties;
        
        $remainingBytes = $object->size - ($inflatedReader->get_currentPosition() - $sPos);
        $object->trailingBytes = $inflatedReader->get_chunk($remainingBytes);
    }
    array_push($_save->saveFileBody->objects,$object);
    echo "";
}

$pos = $inflatedReader->get_currentPosition();
exit;