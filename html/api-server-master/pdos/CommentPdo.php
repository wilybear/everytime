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

function isCommentExists($postId, $reply){
    $pdo = pdoSqlConnect();
    $query = "SELECT EXISTS(SELECT * FROM Comment WHERE postId= ? AND commentId = ?) AS exist;";

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

function viewComments($postId){
    $pdo = pdoSqlConnect();
    $query = "select A.commentId,contents,isDeleted,createTime,A.writerNo,nickName,profileImg,IFNULL(likeCnt,0) as likeCnt from
(SELECT contents,isDeleted,createTime,writerNo,commentId from Comment where postId = ?
    and reply is null)A
natural join (
select no as writerNo ,nickName,profileImg from User)B
left outer join (select commentId,count(userNo) as likeCnt from CommentLike group by commentId)C
on A.commentId = C.commentId
order by createTime;";
    $st = $pdo->prepare($query);
    $st->execute([$postId]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return $res;
}

function viewReplies($postId){
    $pdo = pdoSqlConnect();
    $query = "select A.commentId,contents,isDeleted,createTime,A.writerNo,nickName,profileImg,IFNULL(likeCnt,0) as likeCnt,reply  from
(SELECT contents,isDeleted,createTime,writerNo,commentId,reply from Comment where postId = ?
    and reply is not null)A
natural join (
select no as writerNo ,nickName,profileImg from User)B
left outer join (select commentId,count(userNo) as likeCnt from CommentLike group by commentId)C
on A.commentId = C.commentId
order by createTime;";
    $st = $pdo->prepare($query);
    $st->execute([$postId]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return $res;
}
