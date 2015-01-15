<?php

$references = [

    "test1" => [
        "relation" => "hasOne",
        "relationOptions" => [
            "referenceModel" => "\User",
        ]
    ],
    "test2" => [
        "relation" => "hasMany",
        "relationOptions" => [
            "referenceModel" => "\User",
        ]
    ],
    "test3" => [
        "relation" => "hasManyToMany",
        "relationOptions" => [
            "connectorModel" => "\UserTests",
            "connectorKey" => "user_id",
            "referenceModel" => "\Tests",
            "referenceConnectorKey" => "tests_id"
        ]
    ],
    "test4" => [
        "relation" => "belongsTo",
        "relationOptions" => [
            "referenceModel" => "\Account",
        ]
    ],
];
