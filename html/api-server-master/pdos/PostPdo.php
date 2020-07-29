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