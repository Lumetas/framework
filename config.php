<?php
use Lum\Core\Storage;
use Lum\Core\Qryli;
return [
	"server" => [
		"host" => "0.0.0.0",
		"port" => 80,
		"initFunction" => function () {
			global $pdo;
			Storage::setTime(600);
			Qryli::setPdo($pdo);
			echo "server started!\n";
		}
	],
	"database" => [
		"driver" => "sqlite",
		"sqlite_path" => __DIR__ . "/db.sqlite"
	]
];
