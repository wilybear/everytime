<?php
//Like and Scrap
function isDuplicated($userNo, $postId, $table){
    $pdo = pdoSqlConnect();
    $query = "SELECT EXISTS(SELECT * FROM ".$table." WHERE postId= ? AND userNo = ?) AS exist;";

    $st = $pdo->prepare($query);
    //    $st->execute([$param,$param]);
    $st->execute([$postId,$userNo ]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st=null;$pdo = null;
    return intval($res[0]["exist"]);
}

function createLikeOrScrap($userNo, $postId, $table){
    $pdo = pdoSqlConnect();
    $query = "INSERT INTO ".$table." (userNo,postId) VALUES (?,?);";

    $st = $pdo->prepare($query);
    $st->execute([$userNo, $postId]);

    $st = null;
    $pdo = null;
}

function deleteLikeOrScrap($userNo, $postId, $table){
    $pdo = pdoSqlConnect();
    $query = "DELETE FROM ".$table." WHERE userNo = ? and postId = ?;";

    $st = $pdo->prepare($query);
    $st->execute([$userNo, $postId]);

    $st = null;
    $pdo = null;
}

function isCommentLikeDuplicated($userNo, $commentId){
    $pdo = pdoSqlConnect();
    $query = "SELECT EXISTS(SELECT * FROM CommentLike WHERE commentId= ? AND userNo = ?) AS exist;";

    $st = $pdo->prepare($query);
    //    $st->execute([$param,$param]);
    $st->execute([$commentId,$userNo ]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st=null;$pdo = null;
    return intval($res[0]["exist"]);
}

function deleteCommentLike($userNo, $commentId){
    $pdo = pdoSqlConnect();
    $query = "DELETE FROM CommentLike WHERE userNo = ? and commentId = ?;";

    $st = $pdo->prepare($query);
    $st->execute([$userNo, $commentId]);

    $st = null;
    $pdo = null;
}

function createCommentLike($userNo, $commentId){
    $pdo = pdoSqlConnect();
    $query = "INSERT INTO CommentLike (userNo,commentId) VALUES (?,?);";

    $st = $pdo->prepare($query);
    $st->execute([$userNo, $commentId]);

    $st = null;
    $pdo = null;
}

function createComment($postId,$userNo,$contents,$reply){
    $pdo = pdoSqlConnect();
    $query = "INSERT INTO Comment (postId,writerNo,contents,reply) VALUES (?,?,?,?);";

    $st = $pdo->prepare($query);
    $st->execute([$postId,$userNo,$contents,$reply]);

    $st = null;
    $pdo = null;
}

function updateComment($commentId,$contents){
    $pdo = pdoSqlConnect();
    $query = "UPDATE Comment set contents = ? WHERE commentId = ? and isDeleted = 0;";

    $st = $pdo->prepare($query);
    $st->execute([$contents,$commentId]);

    $st = null;
    $pdo = null;
}

function isCommentExistsInPost($postId, $reply){
    $pdo = pdoSqlConnect();
    $query = "SELECT EXISTS(SELECT * FROM Comment WHERE postId= ? AND commentId = ? AND isDeleted = 0) AS exist;";

    $st = $pdo->prepare($query);
    //    $st->execute([$param,$param]);
    $st->execute([$postId,$reply]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st=null;$pdo = null;
    return intval($res[0]["exist"]);
}

function deleteComment($commentId){
    $pdo = pdoSqlConnect();
    $query = "UPDATE Comment set isDeleted = 1 WHERE commentId = ?;";

    $st = $pdo->prepare($query);
    $st->execute([$commentId]);

    $st = null;
    $pdo = null;
}

function getComments($postId){
    $pdo = pdoSqlConnect();
    $query = "select commentId,writerNo,contents,isDeleted from Comment where postId = ? and reply is null
order by createTime;";
    $st = $pdo->prepare($query);
    $st->execute([$postId]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();
    foreach ($res as $key => $result){
        $res[$key]["commentCnt"] =  getCommentLikeCnt($result["commentId"]);
        $res[$key]["reply"] = getReplies($result["commentId"]);
    }
    $st = null;
    $pdo = null;

    return $res;
}

function getReplies($commentId){
    $pdo = pdoSqlConnect();
    $query = "select commentId,writerNo,contents,isDeleted from Comment where reply = ?
order by createTime";
    $st = $pdo->prepare($query);
    $st->execute([$commentId]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();
    foreach ($res as $key => $result){
        $res[$key]["commentCnt"] =  getCommentLikeCnt($result["commentId"]);
    }
    $st = null;
    $pdo = null;

    return $res;
}

function getCommentLikeCnt($commentId){
    $pdo = pdoSqlConnect();
    $query = "select count(userNo) as likeCnt from CommentLike where commentId = ?;";
    $st = $pdo->prepare($query);
    $st->execute([$commentId]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return $res[0]["likeCnt"];
}

function isUpdatePermissionOnComment($commentId, $userNo){
    $pdo = pdoSqlConnect();
    $query = "SELECT EXISTS(SELECT * FROM Comment WHERE commentId= ? AND writerNo = ? AND isDeleted = 0) AS exist;";

    $st = $pdo->prepare($query);
    //    $st->execute([$param,$param]);
    $st->execute([$commentId, $userNo]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st=null;$pdo = null;
    return intval($res[0]["exist"]);

}

function isCommentExists($commentId){
    $pdo = pdoSqlConnect();
    $query = "SELECT EXISTS(SELECT * FROM Comment WHERE commentId = ? AND isDeleted = 0) AS exist;";

    $st = $pdo->prepare($query);
    //    $st->execute([$param,$param]);
    $st->execute([$commentId]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st=null;$pdo = null;
    return intval($res[0]["exist"]);
}
