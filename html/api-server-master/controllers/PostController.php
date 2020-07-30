
<?php
require 'function.php';

const JWT_SECRET_KEY = "TEST_KEYTEST_KEYTEST_KEYTEST_KEYTEST_KEYTEST_KEYTEST_KEYTEST_KEYTEST_KEYTEST_KEYTEST_KEYTEST_KEYTEST_KEY";

$res = (Object)Array();
header('Content-Type: json');
$req = json_decode(file_get_contents("php://input"));
try {
    addAccessLogs($accessLogs, $req);
    switch ($handler) {
        case "index":
            echo "API Server";
            break;
        case "ACCESS_LOGS":
            //            header('content-type text/html charset=utf-8');
            header('Content-Type: text/html; charset=UTF-8');
            getLogs("./logs/access.log");
            break;
        case "ERROR_LOGS":
            //            header('content-type text/html charset=utf-8');
            header('Content-Type: text/html; charset=UTF-8');
            getLogs("./logs/errors.log");
            break;

        case "createPost":
            //이렇게 해도 되는것인가
            http_response_code(200);
            $res->result = createPost($req->boardId,$req->userNo,$req->title,$req->content);
            $res->isSuccess = TRUE;
            $res->code = 100;
            $res->message = "테스트 성공";
            echo json_encode($res, JSON_NUMERIC_CHECK);
            break;
        case "category":
            http_response_code(200);
            if($_GET["categoryId"]==NULL) {
                $res->result = getCategoryList();
            }else{
                $res->result = getBoardWithCategory($_GET["categoryId"]);
            }
            $res->isSuccess = TRUE;
            $res->code = 100;
            $res->message = "테스트 성공";
            echo json_encode($res, JSON_NUMERIC_CHECK);
            break;
        case "postList":
            http_response_code(200);
            switch ($_GET["option"]){
                case "all":
                    //여기 문제있어
                    $res->result =  getPostListByBoardId($_GET["boardId"]);
                    break;
                case "hot":
                    $res->result = getHotPosts();
                    successRes($res,"hot게시판 불러오기");
                    break;
                case "best":
                    $res->result = getBestPosts();
                    successRes($res,"best게시판 불러오기");
                    break;
                case "default":
                    failRes($res,"wrong option",200);
            }
            break;
        case "viewPost":
            http_response_code(200);
            $res->result =  getPost($vars["postId"]);
            $res->isSuccess = TRUE;
            $res->code = 100;
            $res->message = "테스트 성공";
            echo json_encode($res, JSON_NUMERIC_CHECK);
            break;

        case "deletePost":
            http_response_code(200);
            //작성자만 지울수 있게
            IF(isDeletePermission($_GET["postId"],$_GET["userNo"])){
                deletePost($_GET["postId"]);
                $res->isSuccess = TRUE;
                $res->code = 100;
                $res->message = "테스트 성공";
            }else{
                $res->isSuccess = FALSE;
                $res->code = 100;
                $res->message = "권한 없음";
            }
            echo json_encode($res, JSON_NUMERIC_CHECK);
            break;
        case "updatePost":
            http_response_code(200);
            $res->result = updatePost($req->postId,$req->title, $req->content);
            $res->isSuccess = TRUE;
            $res->code = 100;
            $res->message = "테스트 성공";
            echo json_encode($res, JSON_NUMERIC_CHECK);
            break;

        case "searchPost":
            http_response_code(200);
            $res->result = searchPost($_GET["searchKey"]);
            $res->isSuccess = TRUE;
            $res->code = 100;
            $res->message = "테스트 성공";
            echo json_encode($res, JSON_NUMERIC_CHECK);
            break;
        case "bestPostList":
            http_response_code(200);
            $res->isSuccess = TRUE;
            $res->code = 100;
            $res->message = "테스트 성공";
            echo json_encode($res, JSON_NUMERIC_CHECK);
            break;
        case "hotPostList":
            http_response_code(200);
            $res->isSuccess = TRUE;
            $res->code = 100;
            $res->message = "테스트 성공";
            echo json_encode($res, JSON_NUMERIC_CHECK);
            break;
        case "userPost":
            http_response_code(200);
            switch ($_GET["option"]) {
                case "post":
                    $res->result = getPostListByUserNo($_GET["userNo"]);
                    successRes($res, "Post 불러오기 성공");
                    break;
                case "comment":
                    $res->result = getPostListByComment($_GET["userNo"]);
                    successRes($res, "Comment 불러오기 성공");
                    break;
                case "scrap":
                    $res->result = getPostListByScrap($_GET["userNo"]);
                    successRes($res, "Scrap 불러오기 성공");
                    break;
                case "default":
                    failRes($res, "wrong options", 200);
                    break;
            }
            break;

    }
} catch (\Exception $e) {
    return getSQLErrorException($errorLogs, $e, $req);
}

function successRes($res,$message){
    $res->isSuccess = TRUE;
    $res->code = 100;
    $res->message = $message;
    echo json_encode($res, JSON_NUMERIC_CHECK);
}

function failRes($res,$message,$code){
    $res->isSuccess = FALSE;
    $res->code = $code;
    $res->message = $message;
    echo json_encode($res, JSON_NUMERIC_CHECK);
}
