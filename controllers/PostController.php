
<?php
require 'function.php';

const JWT_SECRET_KEY = "TEST_KEYTEST_KEYTEST_KEYTEST_KEYTEST_KEYTEST_KEYTEST_KEYTEST_KEYTEST_KEYTEST_KEYTEST_KEYTEST_KEYTEST_KEY";
$title_regex="/^[\p{Z}\s]*(?:[^\p{Z}\s][\p{Z}\s]*){2,40}$/u";
$content_regex="/^[\p{Z}\s]*(?:[^\p{Z}\s][\p{Z}\s]*){2,300}$/u";

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
            http_response_code(200);
            $jwt = $_SERVER["HTTP_X_ACCESS_TOKEN"];
            if (!isValidHeader($jwt, JWT_SECRET_KEY)) {
                failRes($res,"유효하지 않은 토큰입니다",201);
                addErrorLogs($errorLogs, $res, $req);
                break;
            }
            $userNo = getUserNoFromHeader($jwt, JWT_SECRET_KEY);

            if(!preg_match($title_regex,$req->title)){
                failRes($res,"제목이 올바르지 않습니다.",212);
                break;
            }
            if(!preg_match($content_regex,$req->content)){
                failRes($res,"내용이 올바르지 않습니다.",213);
                break;
            }
            createPost($req->boardId,$userNo,$req->title,$req->content);
            successRes($res,"post생성 성공");
            break;
        case "category":
            $res->result = getCategoryList();
            successRes($res,"카테고리 조회 성공");
            break;
        case "boardInCategory":
            http_response_code(200);
            if(!isValidCategory($vars["categoryId"])){
                failRes($res,"존재하지 않는 카테고리",209);
                break;
            }
            $res->result = getBoardWithCategory($vars["categoryId"]);
            successRes($res,"카테고리 내 게시판 조회 성공");
            break;
        case "postList":
            http_response_code(200);
            switch ($_GET["option"]){
                case "all":
                    if($_GET["boardId"] == null){
                        failRes($res,"게시판 id가 필요합니다.",210);
                    }
                    $res->result =  getPostListByBoardId($_GET["boardId"],$_GET["lastIdx"]);
                    successRes($res,"게시판 불러오기");
                    break;
                case "hot":
                    $res->result = getHotPosts($_GET["lastIdx"]);
                    successRes($res,"hot게시판 불러오기");
                    break;
                case "best":
                    $res->result = getBestPosts($_GET["lastIdx"]);
                    successRes($res,"best게시판 불러오기");
                    break;
                case "default":
                    failRes($res,"wrong option",211);
                    break;
            }
            break;
        case "viewPost":
            http_response_code(200);
            if(!isValidPost($vars["postId"])){
                failRes($res,"존재하지 않는 게시글",204);
                //삭제되었거나 없는 게시글
                break;
            }
            $res->result =  getPost($vars["postId"]);
            successRes($res,"post 조회 성공");
            break;

        case "deletePost":
            http_response_code(200);
            $jwt = $_SERVER["HTTP_X_ACCESS_TOKEN"];
            if (!isValidHeader($jwt, JWT_SECRET_KEY)) {
                failRes($res,"유효하지 않은 토큰입니다",201);
                addErrorLogs($errorLogs, $res, $req);
                break;
            }
            $userNo = getUserNoFromHeader($jwt, JWT_SECRET_KEY);

            if(!isValidPost($_GET["postId"])){
                failRes($res,"존재하지 않는 게시글입니다.",214);
                //삭제되었거나 없는 게시글
                break;
            }
            if(!isUpdatePermissionOnPost($_GET["postId"],$userNo)){
                failRes($res,"권한이 없습니다",215);
                break;
            }
            deletePost($_GET["postId"]);
            successRes($res,"post 삭제 성공");
            break;
        case "updatePost":
            http_response_code(200);
            $jwt = $_SERVER["HTTP_X_ACCESS_TOKEN"];
            if (!isValidHeader($jwt, JWT_SECRET_KEY)) {
                failRes($res,"유효하지 않은 토큰입니다",201);
                addErrorLogs($errorLogs, $res, $req);
                break;
            }
            $userNo = getUserNoFromHeader($jwt, JWT_SECRET_KEY);

            if(!isValidPost($req->postId)){
                failRes($res,"존재하지 않는 게시글입니다.",214);
                //삭제되었거나 없는 게시글
                break;
            }
            if(!isUpdatePermissionOnPost($req->postId,$userNo)){
                failRes($res,"권한이 없습니다",215);
                break;
            }
            if(!preg_match($title_regex,$req->title)){
                failRes($res,"제목이 올바르지 않습니다.",212);
                break;
            }
            if(!preg_match($content_regex,$req->content)){
                failRes($res,"내용이 올바르지 않습니다.",213);
                break;
            }
            updatePost($req->postId,$req->title, $req->content);
            successRes($res,"post 수정 성공");
            break;

        case "searchPost":
            http_response_code(200);
            if($_GET["lastIdx"]==null || $_GET["searchKey"]==null){
                failRes($res, "wrong options", 211);
                break;
            }
            $res->result = searchPost($_GET["searchKey"],$_GET["lastIdx"]);
            successRes($res,"post 검색 성공");
            break;
        case "userPost":
            http_response_code(200);
            $jwt = $_SERVER["HTTP_X_ACCESS_TOKEN"];
            if (!isValidHeader($jwt, JWT_SECRET_KEY)) {
                failRes($res,"유효하지 않은 토큰입니다",201);
                addErrorLogs($errorLogs, $res, $req);
                break;
            }
            $userNo = getUserNoFromHeader($jwt, JWT_SECRET_KEY);
            switch ($_GET["option"]) {
                case "post":
                    $res->result = getPostListByUserNo($userNo,$_GET["lastIdx"]);
                    successRes($res, "Post 불러오기 성공");
                    break;
                case "comment":
                    $res->result = getPostListByComment($userNo,$_GET["lastIdx"]);
                    successRes($res, "Comment 불러오기 성공");
                    break;
                case "scrap":
                    $res->result = getPostListByScrap($userNo,$_GET["lastIdx"]);
                    successRes($res, "Scrap 불러오기 성공");
                    break;
                case "default":
                    failRes($res, "wrong options", 211);
                    break;
            }

            break;

    }
} catch (\Exception $e) {
    return getSQLErrorException($errorLogs, $e, $req);
}

