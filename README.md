## Описание
Это крайне небольшой фреймворк для языка PHP.

Его основной особенностью является построение сервера, в отличии от других фреймворков или же чистого применения где под каждый запрос весь код по новой интерпритировался, здесь используется свой web server.

Что позволяет сэкономить время на интепритации кода при запуске сервера, ведь код интерпритируется только один раз, а так же позволяет хранить некоторую временную информацию о пользователе напрямую в оперативной памяти. Не прибегая к сторонним средствам.


## Конфигурация
Конфигурация хранится в файле `config.php`, по умолчанию он выглядит так:
```
<?php
return [
	"server" => [
		"host" => "0.0.0.0",
		"port" => 80
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
git clone https://github.com/Lumetas/lum_framework.git;
cd lum_framework;
composer install;
php console.php init;
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
```
И контроллер используемый тут:
```
class IterateController extends Controller{
	private static $i = 0;
	public static function add($rq, $arr) {
		self::$i += $arr["num"];
		return self::prepare_html("value added");
	}
	public static function get(): int{
		return self::$i;
	}
	public static function rem($rq, $arr) {
		self::$i -= $arr["num"];
		return self::prepare_html("value removed");
	}
}

```
Мы можем передать в роутер путь, и коллбэк функцию, так же как и метода контроллера. Так же мы можем установить маршрут для ошибки 404 и директорию для статичных файлов.

Статический метода prepare_html который предоставляет класс Controller принимает html и возвращает, объект класса Response с установленным заголовком `Content-Type : text/html`, просто сокращает ненужный код в контроллерах.

Работает это следующим образом, когда приходит запрос, сервер сначала ищет по прописанным напрямую маршрутам, если не находит и имеется указанная статичная директория, ищет в ней. Если она не указана и/или такого файла нет, выполняется маршрут 404. Если он не установлен тогда пользователь просто увидит "not found"

## view
Шаблоны позволяют упрощать вывод. Синтаксис такой.
```
$content = View::render("main", [
    "property1" => "value1",
    "property2" => "value2"
]);
```
Данный метод рендерит указанный шаблон в html с переданными ему параметрами и возвращает этот самый html который вы может уже использовать на своё усмотрение. Пример есть в контроллерах выше.
Находясь в контроллере можно обернуть в prepare_html и сразу же вернуть.
```
return prepare_html($content = View::render("main", [
    "property1" => "value1",
    "property2" => "value2"
]));
```
### Синтаксис view
Вот самый основной синтаксис:
```
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $property1 ?></title>
</head>
<body>
    <h1>Welcome!</h1>
	<p>Your value: <?= $property2; ?></p>
</body>
</html>
```
## Модели
Особого синтаксиса у моделей нет. Поскольку данный фреймворк это не швейцарский нож с ORM и всем всем всем. Его цель это быстрый, маленький, аккуратный backend, например для сайта на 2-3 страницы.
