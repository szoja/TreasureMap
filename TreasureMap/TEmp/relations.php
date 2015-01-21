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
            "sourceForeignFields" => "user_id", // user FK in connector table
            "referenceModel" => "\User",
            "referenceFields" => "id", // test ID                                       -> reference model local key
        ]
    ],
    "tests" => [
        "relation" => "hasManyToMany",
        "relationOptions" => [
            "sourceFields" => "id", // user ID                                          -> model local KEY   
            "connectorModel" => "\UserTests",
            "sourceConnectorFields" => "user_id", // user FK in connector table         -> Connector belognsTo SOURCE model options
            "referenceConnectorFields" => "tests_id", // test FK in connector table     -> Connector belognsTo REFERENCE model options
            "referenceModel" => "\Tests",
            "referenceFields" => "id", // test ID                                       -> reference model local key
        ]
    ],
    "testfield4" => [
        "relation" => "belongsTo",
        "relationOptions" => [
            "referenceModel" => "\Account",
        ]
    ],
];
