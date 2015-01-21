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
        "name" => "name_changed_in_db"
    ],
    //
    "primaryKey" => "id",
    //
    "relations" => [
        "personal" => [
            "type" => "hasOne",
            "options" => [
                "referenceModel" => "\Personal",
            ]
        ],
        "companies" => [
            "type" => "hasMany",
            "options" => [
                "referenceModel" => "\Posts",
            ]
        ],
    ],
    //
    "casts" => [],
];
