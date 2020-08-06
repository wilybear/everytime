<?php
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

    return $res[0];
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
    $query = "INSERT INTO User (userId,userPwd,nickName,studentId,collegeName,profileImg) VALUES (?,?,?,?,?,?)";

    $st = $pdo->prepare($query);
    $st->execute([$userId,$userPwd,$nickName,$studentId,$collegeName,$profileImg]);

    $st = null;
    $pdo = null;
}

function getUserNoFromHeader($jwt, $key)
{
    try {
        $data = getDataByJWToken($jwt, $key);
        $pdo = pdoSqlConnect();
        $query = "SELECT no as userNo FROM User WHERE userId = ? and userPwd = ?;";

        $st = $pdo->prepare($query);
        $st->execute([$data->id,$data->pw]);
        $st->setFetchMode(PDO::FETCH_ASSOC);
        $res = $st->fetchAll();

        $st = null;
        $pdo = null;

        return $res[0]["userNo"];

    } catch (\Exception $e) {
        return false;
    }
}

function isNickNameUnique($nick){
    $pdo = pdoSqlConnect();
    $query = "SELECT EXISTS(SELECT * FROM User WHERE nickName = ?) AS exist;";

    $st = $pdo->prepare($query);
    //    $st->execute([$param,$param]);
    $st->execute([$nick]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st=null;$pdo = null;
    return intval($res[0]["exist"]);

}

function isIdUnique($id){
    $pdo = pdoSqlConnect();
    $query = "SELECT EXISTS(SELECT * FROM User WHERE userId = ?) AS exist;";

    $st = $pdo->prepare($query);
    //    $st->execute([$param,$param]);
    $st->execute([$id]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st=null;$pdo = null;
    return intval($res[0]["exist"]);

}