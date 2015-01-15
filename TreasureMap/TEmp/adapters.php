<?php

/*
 * protected $source;
  protected $fieldMap;
  protected $localKey;
  protected $relations;
  protected $casts;
 */

$adapter = [
    "source" => "account",
    //
    "fieldMap" => [
        "id" => "id",
        "email" => "email",
        "username" => "username",
        "password" => "password",
    ],
    //
    "localKey" => "id",
    //
    "relations" => [
        "personal" => [
            "relation" => "hasOne",
            "relationOptions" => [
                "referenceModel" => "\Personal",
            ]
        ],
        "companies" => [
            "relation" => "hasMany",
            "relationOptions" => [
                "referenceModel" => "\Company",
            ]
        ],
    ],
    //
    "casts" => [],
];
