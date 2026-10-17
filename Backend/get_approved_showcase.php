<?php

error_reporting(E_ALL);
ini_set("display_errors", "1");

header("Content-Type: application/json");

require_once __DIR__ . "/db.php";

try {

    $showcases =
        $database->selectCollection("showcases");


    $cursor =
        $showcases->find(
            [
                "status" => "approved"
            ],
            [
                "sort" => [
                    "created_at" => -1
                ]
            ]
        );


    $results = [];


    foreach ($cursor as $showcase) {

        $results[] = [

            "id" =>
                (string) $showcase["_id"],

            "name" =>
                $showcase["name"] ?? "Worker",

            "instagram" =>
                $showcase["instagram"] ?? "",

            "description" =>
                $showcase["description"] ?? "",

            "image" =>
                $showcase["image"] ?? null,

            "status" =>
                $showcase["status"] ?? "approved"

        ];

    }


    echo json_encode(
        [
            "success" => true,
            "count" => count($results),
            "showcases" => $results
        ]
    );


} catch (Throwable $e) {

    http_response_code(500);

    echo json_encode(
        [
            "success" => false,
            "message" => $e->getMessage()
        ]
    );

}