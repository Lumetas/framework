<?php

/**
 * Класс для настройки структуры проекта.
 *
 * Создает необходимые директории и файлы при инициализации проекта.
 */
return new class
{
	/**
	 * Основной метод выполнения настройки проекта.
	 *
	 * @param array $argv Аргументы командной строки
	 * @param int $argc Количество аргументов
	 * @return void
	 */
	public function run(array $argv, int $argc): void
	{
		// Папки, которые должны быть созданы
		$requiredDirectories = [
			PROJECT_DIR.'/meract/commands',
			PROJECT_DIR.'/meract/core',
			PROJECT_DIR.'/app/core',
			PROJECT_DIR.'/app/controllers',
			PROJECT_DIR.'/app/models',
			PROJECT_DIR.'/app/routes',
			PROJECT_DIR.'/app/views',
			PROJECT_DIR.'/app/workers',
			PROJECT_DIR.'/app/migrations',
			PROJECT_DIR.'/static',
			PROJECT_DIR.'/tests',
			PROJECT_DIR.'/app/middleware'
		];

		// Файлы, которые должны быть созданы (если их нет)
		$requiredFiles = [
			PROJECT_DIR.'/app/routes/web.php' => "<?php\n\nuse Meract\\Core\\Route;\nuse Meract\\Core\Response;\n\nRoute::get('/', function (\$rq) {\n\treturn new Response('hello world!', 200);\n});"
			/* 
			'index.php' => "<?php\n\n// Your index.php content here\n",
			'public/index.php' => "<?php\n\n// Your public/index.php content here\n", 
			'console.php' => "<?php\n\n// Your console.php content here\n", 
			 */
		];

		// Создаем все необходимые директории
		foreach ($requiredDirectories as $dir) {
			$this->createDirectory($dir);
		}

		// Создаем все необходимые файлы
		foreach ($requiredFiles as $file => $content) {
			$this->createFile($file, $content);
		}

		echo "Setup completed!\n";
	}

	/**
	 * Создает директорию, если её не существует.
	 *
	 * @param string $path Путь к директории
	 * @return void
	 */
	private function createDirectory(string $path): void
	{
		if (!is_dir($path)) {
			mkdir($path, 0755, true);
			echo "Created directory: $path\n";
		} else {
			echo "Directory already exists: $path\n";
		}
	}

	/**
	 * Создает файл, если его не существует.
	 *
	 * @param string $path Путь к файлу
	 * @param string $content Содержимое файла
	 * @return void
	 */
	private function createFile(string $path, string $content): void
	{
		if (!file_exists($path)) {
			file_put_contents($path, $content);
			echo "Created file: $path\n";
		} else {
			echo "File already exists: $path\n";
		}
	}
};
