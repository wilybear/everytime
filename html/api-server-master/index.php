
<?php

require './pdos/DatabasePdo.php';
require './pdos/UserPdo.php';
require './pdos/CommentPdo.php';
require './pdos/PostPdo.php';
require './vendor/autoload.php';

use \Monolog\Logger as Logger;
use Monolog\Handler\StreamHandler;

date_default_timezone_set('Asia/Seoul');
ini_set('default_charset', 'utf8mb4');

//에러출력하게 하는 코드
error_reporting(E_ALL); ini_set("display_errors", 1);

//Main Server API
$dispatcher = FastRoute\simpleDispatcher(function (FastRoute\RouteCollector $r) {
    /* ******************   Test   ****************** */
    $r->addRoute('GET', '/', ['IndexController', 'index']);
    $r->addRoute('GET', '/test', ['IndexController', 'test']);
    $r->addRoute('GET', '/test/{testNo}', ['IndexController', 'testDetail']);
    $r->addRoute('POST', '/test', ['IndexController', 'testPost']);
    $r->addRoute('GET', '/jwt', ['MainController', 'validateJwt']);
    $r->addRoute('POST', '/jwt', ['MainController', 'createJwt']);

    $r->addRoute('GET', '/user/post', ['PostController', 'userPost']);
    $r->addRoute('GET', '/user/{userNo}', ['UserController', 'userDetail']);
    $r->addRoute('POST', '/user', ['UserController', 'createUser']);
    $r->addRoute('PUT', '/user', ['UserController', 'updateUser']);
    $r->addRoute('DELETE', '/user/{userNo}', ['UserController', 'deleteUser']);

    $r->addRoute('GET', '/category', ['PostController', 'category']);
    $r->addRoute('POST', '/post/write', ['PostController', 'createPost']);
    $r->addRoute('GET', '/post/list', ['PostController', 'postList']);
    //$r->addRoute('GET', '/{categoryId}/{boardId}/post/{postId}', ['IndexController', 'postList']);
    $r->addRoute('GET', '/post/{postId}', ['PostController', 'viewPost']);
    $r->addRoute('DELETE', '/post', ['PostController', 'deletePost']);
    $r->addRoute('PUT', '/post', ['PostController', 'updatePost']);
    $r->addRoute('GET', '/post', ['PostController', 'searchPost']);

    $r->addRoute('POST', '/post/like', ['CommentController', 'like']);
    $r->addRoute('POST', '/post/scrap', ['CommentController', 'scrap']);
    $r->addRoute('POST', '/post/comment/like', ['CommentController', 'commentLike']);

    $r->addRoute('POST', '/post/{postId}/comment', ['CommentController', 'createComment']);
    $r->addRoute('PUT', '/post/{postId}/comment', ['CommentController', 'updateComment']);
    $r->addRoute('DELETE', '/post/{postId}/comment', ['CommentController', 'deleteComment']);
    $r->addRoute('GET', '/post/{postId}/comment', ['CommentController', 'viewComments']);
    $r->addRoute('GET', '/post/{postId}/reply', ['CommentController', 'viewReplies']);


    //categoryID, boardID 말고 각각 이름읗 PK로 하고 url에 넣었으면 좋았을텐데..



//    $r->addRoute('GET', '/users', 'get_all_users_handler');
//    // {id} must be a number (\d+)
//    $r->addRoute('GET', '/user/{id:\d+}', 'get_user_handler');
//    // The /{title} suffix is optional
//    $r->addRoute('GET', '/articles/{id:\d+}[/{title}]', 'get_article_handler');
});

// Fetch method and URI from somewhere
$httpMethod = $_SERVER['REQUEST_METHOD'];
$uri = $_SERVER['REQUEST_URI'];

// Strip query string (?foo=bar) and decode URI
if (false !== $pos = strpos($uri, '?')) {
    $uri = substr($uri, 0, $pos);
}
$uri = rawurldecode($uri);

$routeInfo = $dispatcher->dispatch($httpMethod, $uri);

// 로거 채널 생성
$accessLogs = new Logger('ACCESS_LOGS');
$errorLogs = new Logger('ERROR_LOGS');
// log/your.log 파일에 로그 생성. 로그 레벨은 Info
$accessLogs->pushHandler(new StreamHandler('logs/access.log', Logger::INFO));
$errorLogs->pushHandler(new StreamHandler('logs/errors.log', Logger::ERROR));
// add records to the log
//$log->addInfo('Info log');
// Debug 는 Info 레벨보다 낮으므로 아래 로그는 출력되지 않음
//$log->addDebug('Debug log');
//$log->addError('Error log');

switch ($routeInfo[0]) {
    case FastRoute\Dispatcher::NOT_FOUND:
        // ... 404 Not Found
        echo "404 Not Found";
        break;
    case FastRoute\Dispatcher::METHOD_NOT_ALLOWED:
        $allowedMethods = $routeInfo[1];
        // ... 405 Method Not Allowed
        echo "405 Method Not Allowed";
        break;
    case FastRoute\Dispatcher::FOUND:
        $handler = $routeInfo[1];
        $vars = $routeInfo[2];

        switch ($routeInfo[1][0]) {
            case 'UserController':
                $handler = $routeInfo[1][1];
                $vars = $routeInfo[2];
                require './controllers/UserController.php';
                break;
            case 'MainController':
                $handler = $routeInfo[1][1];
                $vars = $routeInfo[2];
                require './controllers/MainController.php';
                break;
            case 'PostController':
                $handler = $routeInfo[1][1]; $vars = $routeInfo[2];
                require './controllers/PostController.php';
                break;
            case 'CommentController':
                $handler = $routeInfo[1][1]; $vars = $routeInfo[2];
                require './controllers/CommentController.php';
                break;
           /* case 'SearchController':
                $handler = $routeInfo[1][1]; $vars = $routeInfo[2];
                require './controllers/SearchController.php';
                break;
            case 'ReviewController':
                $handler = $routeInfo[1][1]; $vars = $routeInfo[2];
                require './controllers/ReviewController.php';
                break;
            case 'ElementController':
                $handler = $routeInfo[1][1]; $vars = $routeInfo[2];
                require './controllers/ElementController.php';
                break;
            case 'AskFAQController':
                $handler = $routeInfo[1][1]; $vars = $routeInfo[2];
                require './controllers/AskFAQController.php';
                break;*/
        }

        break;
}
