
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

        case "commentLike":
            http_response_code(200);
            if(isCommentLikeDuplicated($req->userNo,$req->commentId)){
                deleteCommentLike($req->userNo,$req->commentId);
            }else{
                createCommentLike($req->userNo,$req->commentId);
            }
            $res->isSuccess = TRUE;
            $res->code = 100;
            $res->message = "테스트 성공";
            echo json_encode($res, JSON_NUMERIC_CHECK);
            break;
        case "like":
            http_response_code(200);
            if(isDuplicated($req->userNo,$req->postId,"LikeTB")){
                deleteLikeOrScrap($req->userNo,$req->postId,"LikeTB");
            }else{
                createLikeOrScrap($req->userNo,$req->postId,"LikeTB");
            }
            $res->isSuccess = TRUE;
            $res->code = 100;
            $res->message = "테스트 성공";
            echo json_encode($res, JSON_NUMERIC_CHECK);
            break;
        case "scrap":
            http_response_code(200);
            if(isDuplicated($req->userNo,$req->postId,"Scrap")){
                deleteLikeOrScrap($req->userNo,$req->postId,"Scrap");
            }else{
                createLikeOrScrap($req->userNo,$req->postId,"Scrap");
            }
            $res->isSuccess = TRUE;
            $res->code = 100;
            $res->message = "테스트 성공";
            echo json_encode($res, JSON_NUMERIC_CHECK);
            break;
        case "viewComments":
            http_response_code(200);
            $res->result =  viewComments($vars["postId"]);
            $res->isSuccess = TRUE;
            $res->code = 100;
            $res->message = "테스트 성공";
            echo json_encode($res, JSON_NUMERIC_CHECK);
            break;
        case "viewReplies":
            http_response_code(200);
            $res->result =  viewReplies($vars["postId"]);
            $res->isSuccess = TRUE;
            $res->code = 100;
            $res->message = "테스트 성공";
            echo json_encode($res, JSON_NUMERIC_CHECK);
            break;
        case "createComment":
            if($req->reply != null){
                if(!isCommentExists($vars["postId"],$req->reply)){
                    $res->isSuccess = FALSE;
                    $res->code = 200;
                    $res->message = "해당 댓글 존재하지 않음";
                    echo json_encode($res, JSON_NUMERIC_CHECK);
                    break;
                }
            }
            $res->result = createComment($vars["postId"],$req->userNo,$req->contents,$req->reply);
            $res->isSuccess = TRUE;
            $res->code = 100;
            $res->message = "테스트 성공";
            echo json_encode($res, JSON_NUMERIC_CHECK);
            break;
        case "updateComment":
            http_response_code(200);
            if(!isCommentExists($vars["postId"],$req->commentId)){
                $res->isSuccess = FALSE;
                $res->code = 200;
                $res->message = "해당 댓글 존재하지 않음";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }
            updateComment($req->commentId,$req->contents);
            $res->isSuccess = TRUE;
            $res->code = 100;
            $res->message = "테스트 성공";
            echo json_encode($res, JSON_NUMERIC_CHECK);
            break;
        case "deleteComment":
            http_response_code(200);
            if(!isCommentExists($vars["postId"],$req->commentId)){
                $res->isSuccess = FALSE;
                $res->code = 200;
                $res->message = "해당 댓글 존재하지 않음";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }
            deleteComment($req->commentId);
            $res->isSuccess = TRUE;
            $res->code = 100;
            $res->message = "테스트 성공";
            echo json_encode($res, JSON_NUMERIC_CHECK);
            break;


    }
} catch (\Exception $e) {
    return getSQLErrorException($errorLogs, $e, $req);
}
