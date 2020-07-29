<?php

//READ
function test()
{
    $pdo = pdoSqlConnect();
    $query = "SELECT * FROM Test;";

    $st = $pdo->prepare($query);
    //    $st->execute([$param,$param]);
    $st->execute();
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return $res;
}

//READ
function testDetail($testNo)
{
    $pdo = pdoSqlConnect();
    $query = "SELECT * FROM Test WHERE no = ?;";

    $st = $pdo->prepare($query);
    $st->execute([$testNo]);
    //    $st->execute();
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return $res[0];
}


function testPost($name)
{
    $pdo = pdoSqlConnect();
    $query = "INSERT INTO Test (name) VALUES (?);";

    $st = $pdo->prepare($query);
    $st->execute([$name]);

    $st = null;
    $pdo = null;
}


//user functions
function userDetail($userNo)
{
    $pdo = pdoSqlConnect();
    $query = "SELECT userId,nickName, studentId, collegeName,profileImg FROM User WHERE no = ? and isDeleted = 0;";

    $st = $pdo->prepare($query);
    $st->execute([$userNo]);
    //    $st->execute();
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return $res;
}
function updateUserNickName($userNo,$newName){
    $pdo = pdoSqlConnect();
    $query = "UPDATE User set nickName = ? WHERE no = ?;";

    $st = $pdo->prepare($query);
    $st->execute([$newName,$userNo]);

    $st = null;
    $pdo = null;
}
function updateUserProfileImg($userNo,$newProfileImg){
        $pdo = pdoSqlConnect();
        $query = "UPDATE User set profileImg = ? WHERE no = ?;";
        $st = $pdo->prepare($query);
        $st->execute([$newProfileImg,$userNo]);
        $st = null;
        $pdo = null;

}
function deleteUser($userNo){
    $pdo = pdoSqlConnect();
    $query = "UPDATE User set isDeleted = 1 WHERE no = ?;";

    $st = $pdo->prepare($query);
    $st->execute([$userNo]);

    $st = null;
    $pdo = null;
}
function createUser($userId,$userPwd,$nickName,$studentId,$collegeName,$profileImg){
    $pdo = pdoSqlConnect();
    echo "hi";
    $query = "INSERT INTO User (userId,userPwd,nickName,studentId,collegeName,profileImg) VALUES (?,?,?,?,?,?)";

    $st = $pdo->prepare($query);
    $st->execute([$userId,$userPwd,$nickName,$studentId,$collegeName,$profileImg]);

    $st = null;
    $pdo = null;
}

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

function getPostListByBoardId($boardId){
    $pdo = pdoSqlConnect();
    $query = "select * from
    (select Post.postId, count(commentId) as commentCnt  ,title,content,Post.createTime,Post.writerNo
    from Post left outer join Comment on Post.postId = Comment.postId
    where boardId = ?
    group by Post.postId)CommentTT
    natural join(select Post.postId, count(userNo) as likeCnt from
    Post left outer join LikeTB on Post.postId = LikeTB.postId
    where boardId = ?
    group by Post.postId)likeTT
    order by createTime DESC
    limit 0, 10;";
    $st = $pdo->prepare($query);
    //    $st->execute([$param,$param]);
    $st->execute([$boardId,$boardId]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return $res;
}

function getPostListByUserNo($userNo){
    $pdo = pdoSqlConnect();
    $query = "select * from
    (select Post.postId, count(commentId) as commentCnt  ,title,content,Post.createTime,Post.writerNo
    from Post left outer join Comment on Post.postId = Comment.postId
    where Post.writerNo = ?
    group by Post.postId)CommentTT
    natural join(  select Post.postId, count(userNo) as likeCnt from
    Post left outer join LikeTB on Post.postId = LikeTB.postId
    where writerNo = ?
    group by Post.postId)likeTT
    order by createTime DESC
    limit 0, 10;";
    $st = $pdo->prepare($query);
    $st->execute([$userNo,$userNo]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return $res;
}
function getPostListByComment($userNo){
    $pdo = pdoSqlConnect();
    $query = "select * from
        (select Post.postId, count(commentId) as commentCnt  ,title,content,Post.createTime,Post.writerNo
        from Post left outer join Comment on Post.postId = Comment.postId
        where Comment.writerNo = ?
        group by Post.postId) as CommentTT
    natural join(  select Post.postId, count(userNo) as likeCnt from
        Post left outer join LikeTB on Post.postId = LikeTB.postId
        group by Post.postId)likeTT
    order by createTime DESC
    limit 0, 10;";
    $st = $pdo->prepare($query);
    $st->execute([$userNo]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return $res;
}

function getPostListByScrap($userNo){
    $pdo = pdoSqlConnect();
    $query = "select * from 
(select Post.postId, count(commentId) as commentCnt  ,title,content,Post.createTime,Post.writerNo
from Post left outer join Comment on Post.postId = Comment.postId
inner join Scrap on Post.postId = Scrap.postId
where Scrap.userNo = ?
group by Post.postId) as CommentTT
    natural join( select Post.postId, count(LikeTB.userNo) as likeCnt from
    Post left outer join LikeTB on Post.postId = LikeTB.postId
    inner join Scrap on Post.postId = Scrap.postId
    where Scrap.userNo = ?
    group by Post.postId)likeTT
order by createTime DESC
    limit 0, 10;";
    $st = $pdo->prepare($query);
    $st->execute([$userNo,$userNo]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return $res;
}

function getPost($postId){
    $pdo = pdoSqlConnect();
    $query = "select nickName,profileImg,Title,content,IFNULL(likeCnt,0),IFNULL(commentCnt,0),IFNULL(scrapCnt,0),createTime from
(select postId,writerNo,Title,content,createTime from Post where postId = :postId)PostT
left outer join (select postId, count(commentId)commentCnt from Comment group by postId having postId = :postId)commentT
    using(postId)
left outer join (select postId, count(userNo)likeCnt from LikeTB group by postId having postId = :postId)likeT
    using(postId)
left outer join (select postId, count(userNo)scrapCnt from Scrap group by postId having postId = :postId)scrapT
    using (postId)
left outer join (select no as writerNo,nickName,profileImg from User where User.no = (SELECT writerNo from Post where postId = :postId))UserT
    using (writerNo);
";
    $st = $pdo->prepare($query);
    $st->bindParam(":postId",$postId);
    $st->execute();
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

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

function isValidUser($id, $pw){
    $pdo = pdoSqlConnect();
    //쿼리는 잘 실행되는데 왜 안될까??
    $query = "SELECT EXISTS(SELECT * FROM User WHERE id= ? AND pw = ?) AS exist;";

    $st = $pdo->prepare($query);
    //    $st->execute([$param,$param]);
    $st->execute([$id, $pw]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st=null;$pdo = null;

    return intval($res[0]["exist"]);

}

function isDeletePermission($postId, $userNo){
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

function searchPost($searchKey){
    $pdo = pdoSqlConnect();
    $query = "select * from
(select Post.postId, count(commentId) as commentCnt ,title,content,Post.createTime,Post.writerNo
from Post left outer join Comment on Post.postId = Comment.postId
where  title LIKE ? OR content LIKE ?
group by Post.postId) as A
natural join( select Post.postId, count(userNo) as likeCnt from
Post left outer join LikeTB on Post.postId = LikeTB.postId
group by Post.postId)B
order by createTime DESC
limit 0, 10;";
    $st = $pdo->prepare($query);
    //$st->bindParam(":searchKey","%".$searchKey."%");
    $st->execute(["%".$searchKey."%","%".$searchKey."%"]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return $res;
}


function getHotPosts(){
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
    limit 0, 10;";
    $st = $pdo->prepare($query);
    $st->execute();
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return $res;
}

function getBestPosts(){
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
    limit 0, 10;";
    $st = $pdo->prepare($query);
    $st->execute();
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return $res;
}
// CREATE
//    function addMaintenance($message){
//        $pdo = pdoSqlConnect();
//        $query = "INSERT INTO MAINTENANCE (MESSAGE) VALUES (?);";
//
//        $st = $pdo->prepare($query);
//        $st->execute([$message]);
//
//        $st = null;
//        $pdo = null;
//
//    }


// UPDATE
//    function updateMaintenanceStatus($message, $status, $no){
//        $pdo = pdoSqlConnect();
//        $query = "UPDATE MAINTENANCE
//                        SET MESSAGE = ?,
//                            STATUS  = ?
//                        WHERE NO = ?";
//
//        $st = $pdo->prepare($query);
//        $st->execute([$message, $status, $no]);
//        $st = null;
//        $pdo = null;
//    }

// RETURN BOOLEAN
//    function isRedundantEmail($email){
//        $pdo = pdoSqlConnect();
//        $query = "SELECT EXISTS(SELECT * FROM USER_TB WHERE EMAIL= ?) AS exist;";
//
//
//        $st = $pdo->prepare($query);
//        //    $st->execute([$param,$param]);
//        $st->execute([$email]);
//        $st->setFetchMode(PDO::FETCH_ASSOC);
//        $res = $st->fetchAll();
//
//        $st=null;$pdo = null;
//
//        return intval($res[0]["exist"]);
//
//    }
