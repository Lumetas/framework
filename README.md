## Описание
MEract - Небольшой фреймворк для языка php.

Его основной особенностью является построение сервера, в отличии от других фреймворков или же чистого применения где под каждый запрос весь код по новой интерпритировался, здесь используется свой web server.

Что позволяет сэкономить время на интепритации кода при запуске сервера, ведь код интерпритируется только один раз, а так же позволяет хранить некоторую временную информацию о пользователе напрямую в оперативной памяти. Не прибегая к сторонним средствам.


### Напоминаю
Все классы фреймворка нужно подключить используя `use Meract\Core\<Class>`

## Документация
Конкретная техническая документация по методам [доступна тут](https://lumetas.github.io/meract/)


## Стрктура
```
.
├───app                 - Код вашего приложения
│   ├───controllers     - Контроллеры
│   ├───core            - Скрипты выполняемые перед запуском сервера. Общие утилиты, дополнительные настройки и т.д.
│   ├───middleware      - middleware Роутов
│   ├───migrations      - Миграции базы данных
│   ├───models          - Ваши модели
│   ├───routes          - Ваши роуты
│   ├───static          - Сатичные файлы(html, css, js)
│   ├───views           - Ваши шаблоны view
│   └───workers         - Ваши воркеры
├───meract              - Код фреймворка
│   ├───commands        - Команды для mrst
│   └───core            - Основные классы и код фреймворка
│
│
│
├───config.php          - Конфиг фреймворка, а так же вашего приложения.
├───index.php           - index файл запускаешь сервер и всё ваше приложение
├───worker.php          - Файл запускающий воркер, который будет выполнять задачи из очереди
├───mrst                - Утилита командной строки фреймворка
│
│
├───vendor              - composer vendor
├───composer.json       - composer.json
├───composer.lock       - composer.lock
└───tests               - Директория тестов phpUNIT
```


## Конфигурация
Конфигурация хранится в файле `config.php`, по умолчанию он выглядит так:
```
<?php
return [
	"server" => [
		"host" => "0.0.0.0",
		"port" => 80
	],
	
	"database" => [
		"driver" => "sqlite",
		"sqlite_path" => __DIR__ . "/db.sqlite"
	]
];
```
Здесь задаётся host и port сервера. Вы можете указать свою функцию при поднятии сервера, а так же свой логгер запросов:
```
<?php
return [
	"server" => [
		"host" => "0.0.0.0",
		"port" => 80,
		"requestLogger" => new class extends RequestLogger {
			public function handle($rq) {
				echo "test\n";
			}
		},

		"initFunction" => function () { echo "test start\n"; }
	]
];
```


## Установка
```
composer create-project lumetas/meract project-name
cd project-name;
php mrst init;
php mrst migrate;
```

## Запуск
Для запуска вам необходимо выполнить файл `index.php`, например вот так:
```
php index.php
```
После чего произойдёт разовая интерпритация и подключение всех классов, дальше сервер будет запущен на том хосту и порту что указаны в конфиге. Далее вы получите сообщение "server started!"

Оно вызывается функцией инициализации которую вы можете задать в конфигурационном файле.

Сервер начнёт слушать и принимать запросы выводя информацию о запросе в консоль, формат логов вы так же можете поменять как и было указано выше



## Роутеры и контроллеры
С таким устройством сервера без роутеров было бы невозможно жить, а без контроллеров была бы невозможна жизнь в MVC. Коей следует данный фреймворк. Очень во многом я вдохновляюсь laravel. Так что многое покажется для вас знакомым.

И так, вот все примеры синтаксиса роутеров:
```
Route::get('/', function(Request $rq) {
	$content = View::render("main", [
		"title" => "example lumframework project",
		"value" => IterateController::get()
	]);
	$r = new Response($content, 200);
	$r->header("Content-Type", "text/html");
	return $r;
});

Route::get('/add/{num}', [IterateController::class, "add"]);

Route::get('/rem/{num}', [IterateController::class, "rem"]);

Route::staticFolder("static");

Route::notFound(function(Request $rq) {
	return new Response('is a 404 error', 404);
});

Route::group('/admin', function () {
    Route::get('/', function ($rq){return new Response('hello admin!', 200);});
    Route::get('/test1', function ($rq){return new Response('hello admin test1!', 200);});
    Route::get('/test2', function ($rq){return new Response('hello admin test2!', 200);});
});

Route::get('/', function ($rq) {
	return new Response('hello world!', 200);
}, [new FiftyFiftyMiddleware()]);

Route::group('/admin', function () {
    Route::get('/', function ($rq){return new Response('hello admin!', 200);});
    Route::get('/test1', function ($rq){return new Response('hello admin test1!', 200);});
    Route::get('/test2', function ($rq){return new Response('hello admin test2!', 200);});
}, [new FiftyFiftyMiddleware()]);

route::middleware(new FiftyFiftyMiddleware); //Глобальный middleware
```
И контроллер используемый тут:
```
use Meract\Core\Controller;
class IterateController extends Controller{
	private static $i = 0;
	public static function add($rq, $arr) {
		self::$i += $arr["num"];
		return self::html("value added");
	}
	public static function get(): int{
		return self::$i;
	}
	public static function rem($rq, $arr) {
		self::$i -= $arr["num"];
		return self::html("value removed");
	}
}

```
А так же middleware:
```
use Meract\Core\Request;
use Meract\Core\Response;

class FiftyFiftyMiddleware
{
    public function handle(Request $request, callable $next): Response
    {
        if (mt_rand(0, 1) === 1) {
            // Пропускаем запрос (50% шанс)
            return $next($request);
        }

        // Блокируем запрос (50% шанс)
        return new Response("Sorry, you lost the 50/50 chance", 403);
    }
}
```
Мы можем передать в роутер путь, и коллбэк функцию, так же как и метода контроллера. Так же мы можем установить маршрут для ошибки 404 и директорию для статичных файлов.

Статический метода html который предоставляет класс Controller принимает html и возвращает, объект класса Response с установленным заголовком `Content-Type : text/html`, просто сокращает ненужный код в контроллерах.

Работает это следующим образом, когда приходит запрос, сервер сначала ищет по прописанным напрямую маршрутам, если не находит и имеется указанная статичная директория, ищет в ней. Если она не указана и/или такого файла нет, выполняется маршрут 404. Если он не установлен тогда пользователь просто увидит "not found"

## view
Шаблоны позволяют упрощать вывод. Синтаксис такой.
```
$header = new View("header", ["title" => $data['name']]);
```
Вы можете объеденять несколько шаблонов:
```
$header = new View("header", ["title" => $data['name']]);
$html = new View('index', ["header" => $header, "a" => "sdf"]);
return self::html($html);
```
views/header.php:
```
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title; ?></title>
```
views/index.php:
```
<!DOCTYPE html>
<html lang="en">
<head>
<?= $header; ?>
</head>
<body>
<?= $a; ?>

</body>
</html>
```
В данном примере вы можете увидеть как вставлять какие-то параметры, а так же использование нескольких шаблонов друг в друге.
## Модели
Для работы прийдётся настроить базу данных. в вашем файле config.php
Примеры:
```
"database" => [
    "driver" => "mysql",
    "host" => "localhost",
    "port" => 3306,
    "dbname" => "test",
    "username" => "root",
    "password" => "",
    "charset" => "utf8mb4"
]
```
```
"database" => [
    "driver" => "pgsql",
    "host" => "localhost",
    "port" => 5432,
    "dbname" => "test",
    "username" => "postgres",
    "password" => "password"
]
```
```
"database" => [
    "driver" => "sqlite",
    "sqlite_path" => __DIR__ . "/database.sqlite"
]
```
У вас должны быть установлены и включены модули pdo и другие.
```
use Meract\Core\Model;
class TestModel extends Model{
	protected static $table = 'your_table'; // Имя таблицы
	protected $fillable = ['id', 'name'];

}
```
Вот так вы можете создать модель привязанную к таблице. Далее примеры использования данной модели. В рамках данного примера выполнение происходит внутри роута. Вы же должны делать это внутри контроллера.
```
Route::get('/', function (Request $rq) {
	$m = new TestModel(["name" => (string) random_int(0, 10000)]); // Создаём модель с случайным именем.
	$m->save(); //Сохраняем.
	$r = new Response("Запись создана", 200); //Создаём ответ. С текстом и статусом 200.
	$r->header("Content-Type", "text/html");// Устанавливаем тип html
	return $r;// возвращаем ответ.
});

Route::get('/show', function (Request $rq) {
	$m = new TestModel();//Создаём модель 
	$pices = OUTVAR::dump($m->all()); //$m->all() - Возвращает все записи. OUTVAR::dump делает var_dump в переменную

	$r = new Response("<pre>$pices</pre>", 200);// Выводим всё пользователю обрамляе в pre
	$r->header("Content-Type", "text/html");
	return $r;
});

Route::get('/up/{id}/{data}', function (Request $rq, array $data) {
	$test = TestModel::find((int) $data["id"]); //Создаём модель из записи с id полученным из запроса.
	$test->name = $data['data']; // Устанавливаем значение data из запроса в name.
	$test->save(); // сохраняем

	$pices = "Запись $data[id] обновлена";
	//Сообщаем пользователю.
	$r = new Response("<pre>$pices</pre>", 200);
	$r->header("Content-Type", "text/html");
	return $r;
});

Route::get('/del/{id}', function (Request $rq, array $data) {
	$test = TestModel::find((int) $data["id"]);// создаём модель из записи по id 
	$test->delete();// Удаляем запись.
	
	$pices = "Запись $data[id] Удалена";//Информируем пользователя.

	$r = new Response("<pre>$pices</pre>", 200);
	$r->header("Content-Type", "text/html");
	return $r;
});
```
Данные примеры кода охватывают стандартные CRUD операции выполненные через модели.


## Storage
Синтаксис:
```
Storage::setTime(int seconds); // Устанавливает время жизни записей.
Storage::set("property", "value" ?prefix); // Создаёт запись, при указании префикса запись на определённом префиксе
Storage::get("property", ?prefix); // Получает значение записи
Storage::update("property", ?prefix); // Обновляет время жизни записи.
Storage::remove("property", ?prefix); // Удаляет запись
Storage::handleDeletion(); // Удаляет все истёкшие записи.
```
Из-за устройства сервера у вас есть возможность прямо в PHP хранить какие-то данные в оперативной памяти. Но при большом их объёме может случиться переполнение. Поэтому регулярно выполняйте `Storage::handleDeletion();` чтобы избежать переполнения.

Данный функционал существует ТОЛЬКО для временного краткосрочного хранения мелких, но важных данных между запросами.


## Workers
Воркеры представляют собой систему очередей.

Начнём с конфигурации:
```
"worker" => [
	"enabled" => true,
	"endpoint" => "endpoint",
	"server-callback" => function (string $data): string {
		echo $data."\n";
		return "Понял";
	}
]
```
Так же вам будет необходимо создать таблицу `lum_workers`, со столбцами `primary id`, `string name`, `string message`

Далее создадим небольшой воркер `sleep`.

В файле `app/workers/sleep.php`:
```
<?php
use Meract\Core\Worker;

return new class extends Worker {
    public function run(string $message) {
        sleep((int) $message);
        $result = self::sendToServer("Я подождал $message секунд");
        if ($result == "Понял") {
            echo "Меня услышали!\n";
        }
    }
};
```
И в любом месте кода нашего мастер процесса можем использовать:
```
Worker::register("sleep", "3");
```
Это создаст запись в таблице. После worker process когда дойдёт до выполнения этой записи возьмёт имя "sleep" и запустит метод run передав туда message.

Метод sendToServer отправит данные на endpoint. И в мастер процессе отработает колбэк функция воркера. Возвращаемое ей значение выйдет из метода sendToServer.

По факту это система очередей. Но благодоря сохранению состояния. Вы можете создать воркер для обработки большого количества информации. Результат отправить в мастер и сохранить в storage для быстрого ответа пользователю.

Для запуска воркера нужно запустить `worker.php`.

## QRYLI
qryli это QueryBuilder. Для начала вам необходимо установить в класс объект pdo. Он хранится в глобальной переменной. Например вы можете установить его, а так же storage в initFunction в вашем `config.php`:
```
"initFunction" => function () {
	global $pdo;
	Storage::setTime(600);
	QRYLI::setPdo($pdo);
	echo "server started!\n";
}
```
Небольшие примеры использования:
```
QRYLI::insert("users", ["name" => "aaaaa"])->run();
$users = QRYLI::select('*')->from('users')->where('age > ?', [18])->orderBy('name')->limit(10)->run();
QRYLI::update('users', ['age' => 26])->where('id = ?', [1])->run();
QRYLI::delete('users')->where('id = ?', [1])->run();
```
## Session
В общем случае использование сессий выглядит примерно так:
```
Route::get('/', function ($rq) {
	$session = Session::start($rq);
	if (isset($session->a)) { $session->a += 1; } else {$session->a = 0;}
	
	return $session->end(new Response($session->a, 200));
});
```
Так вы можете устанавливать любые параметры любого типа. Сохранятся они будут с помощью `Storage` Так что не забывайте чистить истёкшие записи перед участками работы с сессиями.

## Миграции
Фреймворк обладает базовым функционалом миграций.

Для создания миграции вам нужно создать файл, например `app/migrations/first_migration.php`:
```
<?php

use Meract\Core\Migration;

return new class extends Migration {
    public function up()
    {
        $this->schema->create('fist_migration', function ($table) {
            $table->id();               // Автоинкрементный первичный ключ
            $table->string('name');     // Строковое поле name
            $table->string('message');  // Строковое поле message
        });
    }

    public function down()
	{
        $this->schema->drop('first_migration');
    }
};
```
Дальше вы можете воспользоваться `mrst` для применения миграции:
```
php mrst migrate # Все миграции
php mrst migrate fist_migration # Миграция "first_migration"
```
Так же чтобы откатить миграции вы можете сделать:
```
php mrst migrate.rollback # Все миграции
php mrst migrate.rollback fist_migration # Миграция "first_migration"
```

## mrst
`mrst` или `meract support tool` средство помощи.
Для создания команды вам нужно создать файл в 'meract/commands/file.php'
С примерно таким синтаксисом:
```
<?php
return new class {
	public function run($argv, $argc) {
		var_dump($argv);
	}
};
```
После вы сможете вызвать команду так:
```
php mrst file arg0 arg1 arg2 arg3
```
Вы увидите примерно следующее:
```
array(4) {
  [0]=>
  string(4) "arg0"
  [1]=>
  string(4) "arg1"
  [2]=>
  string(4) "arg2"
  [3]=>
  string(4) "arg3"
}
```

### make
```
php mrst make <type> <name>
```
Так это создасть файл указанного типа с указанным названием, например:
```
php mrst make model Test
```
Чтобы увидеть больше справки выполните `php mrst make`

### tests
```
php mrst tests
```
Проводит unit тесты из папки `tests` с помощью `phpUnit`
