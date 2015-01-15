<?php

$references = [

    "testfield1" => [
        "relation" => "hasOne",
        "relationOptions" => [
            "referenceModel" => "\User",
        ]
    ],
    "testfield2" => [
        "relation" => "hasMany",
        "relationOptions" => [
            "referenceModel" => "\User",
        ]
    ],
    "testfield3" => [
        "relation" => "hasManyToMany",
        "relationOptions" => [
            "connectorModel" => "\UserTests",
            "connectorKey" => "user_id",
            "referenceModel" => "\Tests",
            "referenceConnectorKey" => "tests_id"
        ]
    ],
    "testfield4" => [
        "relation" => "belongsTo",
        "relationOptions" => [
            "referenceModel" => "\Account",
        ]
    ],
];
