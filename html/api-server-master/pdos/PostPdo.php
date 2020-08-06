<?php
function getCategoryList()
{
    $pdo = pdoSqlConnect();
    $query = "SELECT * FROM Category;";

    $st = $pdo->prepare($query);
    //    $st->execute([$param,$param]);
    $st->execute();
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return $res;
}
function createPost($boardId, $userNo,$title ,$content){
    $pdo = pdoSqlConnect();
    $query = "INSERT INTO Post (writerNo,boardId,content,title) VALUES (?,?,?,?);";

    $st = $pdo->prepare($query);
    $st->execute([$userNo,$boardId,$content,$title]);

    $st = null;
    $pdo = null;
}
function getBoardWithCategory($categoryId){
    $pdo = pdoSqlConnect();
    $query = "SELECT boardId,boardName FROM Board WHERE categoryNo = ?;";
    $st = $pdo->prepare($query);
    //    $st->execute([$param,$param]);
    $st->execute([$categoryId]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return $res;
}

function getRecentPosts($categoryId){
    $pdo = pdoSqlConnect();
    $query = "SELECT post FROM Board WHERE categoryNo = ?;";
    $st = $pdo->prepare($query);
    //    $st->execute([$param,$param]);
    $st->execute([$categoryId]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return $res;
}

function getPostListByBoardId($boardId,$lastIdx){
    $pdo = pdoSqlConnect();
    $query = "select postId, title, content, createTime, writerNo from Post 
where boardId = ? and isDeleted = 0 order by createTime DESC limit ".$lastIdx.",10;";
    $st = $pdo->prepare($query);
    //    $st->execute([$param,$param]);
    $st->execute([$boardId]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();
    foreach ($res as $key => $result){
        $res[$key]["likeCnt"] = getLikeCnt($result["postId"]);
        $res[$key]["CommentCnt"] = getCommentCnt($result["postId"]);
    }

    $st = null;
    $pdo = null;

    return $res;
}

function getPostListByUserNo($userNo,$lastIdx){
    $pdo = pdoSqlConnect();
    $query = "select postId, title, content, createTime, writerNo from Post where writerNo = ? and isDeleted = 0 order by createTime DESC limit ".$lastIdx.",10;";
    $st = $pdo->prepare($query);
    $st->execute([$userNo]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();
    foreach ($res as $key => $result){
        $res[$key]["likeCnt"] = getLikeCnt($result["postId"]);
        $res[$key]["CommentCnt"] = getCommentCnt($result["postId"]);
    }
    $st = null;
    $pdo = null;

    return $res;
}
function getPostListByComment($userNo,$lastIdx){
    $pdo = pdoSqlConnect();
    $query = "select Post.postId, title, content, Post.createTime, Post.writerNo from Post left outer join Comment on Post.postId = Comment.postId
    where Comment.writerNo = ? and Post.isDeleted = 0
    order by createTime DESC
    limit ".$lastIdx.",10;";
    $st = $pdo->prepare($query);
    $st->execute([$userNo]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();
    foreach ($res as $key => $result){
        $res[$key]["likeCnt"] = getLikeCnt($result["postId"]);
        $res[$key]["CommentCnt"] = getCommentCnt($result["postId"]);
    }
    $st = null;
    $pdo = null;

    return $res;
}

function getPostListByScrap($userNo,$lastIdx){
    $pdo = pdoSqlConnect();
    $query = "select Post.postId, title, content, Post.createTime, Post.writerNo from Post left outer join Scrap on Post.postId = Scrap.postId
    where Scrap.userNo = ? and isDeleted = 0
    order by createTime DESC
    limit ".$lastIdx.", 10;";
    $st = $pdo->prepare($query);
    $st->execute([$userNo]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();
    foreach ($res as $key => $result){
        $res[$key]["likeCnt"] = getLikeCnt($result["postId"]);
        $res[$key]["CommentCnt"] = getCommentCnt($result["postId"]);
    }
    $st = null;
    $pdo = null;

    return $res;
}

function getPost($postId){
    $pdo = pdoSqlConnect();
    $query = "select postId,writerNo,Title,content,createTime from Post where postId = :postId
 and isDeleted = 0";
    $st = $pdo->prepare($query);
    $st->bindParam(":postId",$postId);
    $st->execute();
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();
    foreach ($res as $key => $result){
        $res[$key]["likeCnt"] = getLikeCnt($result["postId"]);
        $res[$key]["CommentCnt"] = getCommentCnt($result["postId"]);
        $res[$key]["ScrapCnt"] = getScrapCnt($result["postId"]);
        $res[$key] += getUserInfo($result["writerNo"]);
        $res[$key]["comment"] = getComments($postId);
    }

    $st = null;
    $pdo = null;

    return $res[0];
}

function deletePost($postId){
    $pdo = pdoSqlConnect();
    $query = "UPDATE Post set isDeleted = 1 WHERE postId = ?;";

    $st = $pdo->prepare($query);
    $st->execute([$postId]);

    $st = null;
    $pdo = null;
}

function updatePost($postId,$title,$content){
    $pdo = pdoSqlConnect();
    $query = "Update Post set title = ?, content =? where postId = ?;";

    $st = $pdo->prepare($query);
    $st->execute([$title,$content,$postId]);

    $st = null;
    $pdo = null;
}

function isUpdatePermissionOnPost($postId, $userNo){
    $pdo = pdoSqlConnect();
    $query = "SELECT EXISTS(SELECT * FROM Post WHERE postId= ? AND writerNo = ?) AS exist;";

    $st = $pdo->prepare($query);
    //    $st->execute([$param,$param]);
    $st->execute([$postId, $userNo]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st=null;$pdo = null;
    return intval($res[0]["exist"]);

}

function searchPost($searchKey,$lastIdx){
    $pdo = pdoSqlConnect();
    $query = "select postId,title,content,Post.createTime,writerNo from Post where title LIKE ? OR content LIKE ?
 order by createTime DESC limit ".$lastIdx.", 10;";
    $st = $pdo->prepare($query);
    //$st->bindParam(":searchKey","%".$searchKey."%");
    $st->execute(["%".$searchKey."%","%".$searchKey."%"]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();
    foreach ($res as $key => $result){
        $res[$key]["likeCnt"] = getLikeCnt($result["postId"]);
        $res[$key]["CommentCnt"] = getCommentCnt($result["postId"]);
    }
    $st = null;
    $pdo = null;

    return $res;
}


function getHotPosts($lastIdx){
    $pdo = pdoSqlConnect();
    $query = "select * from
    (select Post.postId, count(commentId) as commentCnt  ,title,content,Post.createTime,Post.writerNo
    from Post left outer join Comment on Post.postId = Comment.postId
    group by Post.postId)CommentTT
    natural join(select Post.postId, count(userNo) as likeCnt from
    Post left outer join LikeTB on Post.postId = LikeTB.postId
    group by Post.postId)likeTT
    where likeCnt > 10
    order by createTime DESC
    limit ".$lastIdx.", 10;";
    $st = $pdo->prepare($query);
    $st->execute();
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return $res;
}

function getBestPosts($lastIdx){
    $pdo = pdoSqlConnect();
    $query = "select * from
    (select Post.postId, count(commentId) as commentCnt  ,title,content,Post.createTime,Post.writerNo
    from Post left outer join Comment on Post.postId = Comment.postId
    group by Post.postId)CommentTT
    natural join(select Post.postId, count(userNo) as likeCnt from
    Post left outer join LikeTB on Post.postId = LikeTB.postId
    group by Post.postId)likeTT
    where likeCnt > 100
    order by createTime DESC
    limit ".$lastIdx.", 10;";
    $st = $pdo->prepare($query);
    $st->execute();
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return $res;
}

function getCommentCnt($postId){
    $pdo = pdoSqlConnect();
    $query = "select count(commentId)as commentCnt from Comment where postId =?;";
    $st = $pdo->prepare($query);
    $st->execute([$postId]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return $res[0]["commentCnt"];
}

function getLikeCnt($postId){
    $pdo = pdoSqlConnect();
    $query = "select count(userNo) as likeCnt from LikeTB where postId = ?;";
    $st = $pdo->prepare($query);
    $st->execute([$postId]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return $res[0]["likeCnt"];
}

function getScrapCnt($postId){
    $pdo = pdoSqlConnect();
    $query = "select count(userNo) as scrapCnt from Scrap where postId = ?;";
    $st = $pdo->prepare($query);
    $st->execute([$postId]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return $res[0]["scrapCnt"];
}

function getUserInfo($writerNo){
    $pdo = pdoSqlConnect();
    $query = "select no as writerNo,nickName,profileImg from User where User.no = ?";
    $st = $pdo->prepare($query);
    $st->execute([$writerNo]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return $res[0];
}

function isValidCategory($categoryId){
    $pdo = pdoSqlConnect();
    $query = "SELECT EXISTS(SELECT * FROM Category WHERE no = ?) AS exist;";

    $st = $pdo->prepare($query);
    //    $st->execute([$param,$param]);
    $st->execute([$categoryId]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st=null;$pdo = null;
    return intval($res[0]["exist"]);

}

function isValidPost($postId){
    $pdo = pdoSqlConnect();
    $query = "SELECT EXISTS(SELECT * FROM Post WHERE postId = ? and isDeleted = 0) AS exist;";

    $st = $pdo->prepare($query);
    //    $st->execute([$param,$param]);
    $st->execute([$postId]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st=null;$pdo = null;
    return intval($res[0]["exist"]);

}
