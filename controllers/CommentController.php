
<?php
require 'function.php';

const JWT_SECRET_KEY = "TEST_KEYTEST_KEYTEST_KEYTEST_KEYTEST_KEYTEST_KEYTEST_KEYTEST_KEYTEST_KEYTEST_KEYTEST_KEYTEST_KEYTEST_KEY";
$comment_content_regex="/^[\p{Z}\s]*(?:[^\p{Z}\s][\p{Z}\s]*){2,200}$/u";
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
        case "commentLike":
            http_response_code(200);
            $jwt = $_SERVER["HTTP_X_ACCESS_TOKEN"];
            if (!isValidHeader($jwt, JWT_SECRET_KEY)) {
                failRes($res,"유효하지 않은 토큰입니다",201);
                addErrorLogs($errorLogs, $res, $req);
                break;
            }
            $userNo = getUserNoFromHeader($jwt, JWT_SECRET_KEY);
            if(isCommentLikeDuplicated($userNo,$req->commentId)){
                deleteCommentLike($userNo,$req->commentId);
                successRes($res,"좋아요 해제 성공");
            }else{
                createCommentLike($userNo,$req->commentId);
                successRes($res,"좋아요 성공");
            }
            break;
        case "like":
            http_response_code(200);
            $jwt = $_SERVER["HTTP_X_ACCESS_TOKEN"];
            if (!isValidHeader($jwt, JWT_SECRET_KEY)) {
                failRes($res,"유효하지 않은 토큰입니다",201);
                addErrorLogs($errorLogs, $res, $req);
                break;
            }
            $userNo = getUserNoFromHeader($jwt, JWT_SECRET_KEY);
            if(isDuplicated($userNo,$req->postId,"LikeTB")){
                deleteLikeOrScrap($userNo,$req->postId,"LikeTB");
                successRes($res,"좋아요 해제 성공");
            }else{
                createLikeOrScrap($userNo,$req->postId,"LikeTB");
                successRes($res,"좋아요 성공");
            }
            break;
        case "scrap":
            http_response_code(200);
            $jwt = $_SERVER["HTTP_X_ACCESS_TOKEN"];
            if (!isValidHeader($jwt, JWT_SECRET_KEY)) {
                failRes($res,"유효하지 않은 토큰입니다",201);
                addErrorLogs($errorLogs, $res, $req);
                break;
            }
            $userNo = getUserNoFromHeader($jwt, JWT_SECRET_KEY);
            if(isDuplicated($userNo,$req->postId,"Scrap")){
                deleteLikeOrScrap($userNo,$req->postId,"Scrap");
                successRes($res,"스크랩 해제 성공");
            }else{
                createLikeOrScrap($userNo,$req->postId,"Scrap");
                successRes($res,"스크랩 성공");
            }
            break;
        case "createComment":
            $jwt = $_SERVER["HTTP_X_ACCESS_TOKEN"];
            if (!isValidHeader($jwt, JWT_SECRET_KEY)) {
                failRes($res,"유효하지 않은 토큰입니다",201);
                addErrorLogs($errorLogs, $res, $req);
                break;
            }
            $userNo = getUserNoFromHeader($jwt, JWT_SECRET_KEY);
            if(!isValidPost($req->postId)){
                failRes($res,"존재하지 않는 게시글입니다",214);
                //삭제되었거나 없는 게시글
                break;
            }
            if($req->reply != null){
                if(!isCommentExistsInPost($req->postId,$req->reply)){
                    failRes($res,"존재하지 않는 댓글에 답글을 쓸 수 없습니다.",216);
                    break;
                }
            }
            if(!preg_match($comment_content_regex,$req->contents)){
                failRes($res,"댓글 내용이 올바르지 않습니다.",218);
                break;
            }
            createComment($req->postId, $userNo,$req->contents,$req->reply);
            successRes($res,"댓글 작성 성공");
            break;
        case "updateComment":
            http_response_code(200);
            $jwt = $_SERVER["HTTP_X_ACCESS_TOKEN"];
            if (!isValidHeader($jwt, JWT_SECRET_KEY)) {
                failRes($res,"유효하지 않은 토큰입니다",201);
                addErrorLogs($errorLogs, $res, $req);
                break;
            }
            $userNo = getUserNoFromHeader($jwt, JWT_SECRET_KEY);
            if(!isCommentExists($req->commentId)){
                failRes($res,"존재하지 않는 댓글입니다.",217);
                break;
            }
            if(!isCommentExistsInPost($req->postId,$req->commentId)){
                failRes($res,"해당 게시글에 존재하지 않는 댓글",216);
                break;
            }
            if(!isUpdatePermissionOnComment($req->commentId,$userNo)){
                failRes($res,"권한이 없습니다.",215);
                break;
            }
            if(!preg_match($comment_content_regex,$req->contents)){
                failRes($res,"댓글 내용이 올바르지 않습니다.",218);
                break;
            }
            updateComment($req->commentId,$req->contents);
            successRes($res,"댓글 수정 성공");
            break;
        case "deleteComment":
            http_response_code(200);
            $jwt = $_SERVER["HTTP_X_ACCESS_TOKEN"];
            if (!isValidHeader($jwt, JWT_SECRET_KEY)) {
                failRes($res,"유효하지 않은 토큰입니다",201);
                addErrorLogs($errorLogs, $res, $req);
                break;
            }
            $userNo = getUserNoFromHeader($jwt, JWT_SECRET_KEY);
            if(!isCommentExists($_GET["commentId"])){
                failRes($res,"존재하지 않는 댓글입니다",217);
                break;
            }
            if(!isUpdatePermissionOnComment($_GET["commentId"],$userNo)){
                failRes($res,"권한이 없습니다.",215);
                break;
            }
            deleteComment($_GET["commentId"]);
            successRes($res,"댓글 삭제 성공");
            break;
    }
} catch (\Exception $e) {
    return getSQLErrorException($errorLogs, $e, $req);
}
