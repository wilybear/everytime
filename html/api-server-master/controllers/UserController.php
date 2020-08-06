
<?php
require 'function.php';

const JWT_SECRET_KEY = "TEST_KEYTEST_KEYTEST_KEYTEST_KEYTEST_KEYTEST_KEYTEST_KEYTEST_KEYTEST_KEYTEST_KEYTEST_KEYTEST_KEYTEST_KEY";
$nick_regex="/^[!^\x{1100}-\x{11FF}\x{3130}-\x{318F}\x{AC00}-\x{D7AF}0-9a-zA-Z]{4,15}$/u";
$id_regex="/^[!^0-9a-zA-Z]{5,15}$/u";
$pwd_regex = "/^[0-9A-Za-z~!@#$%^&*]{10,20}$/i";
$img_regex = "/\.(png|jpg|bmp)$/i";
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
        /*
         * API No. 0
         * API Name : 테스트 API
         * 마지막 수정 날짜 : 19.04.29
         */
        case "test":
            http_response_code(200);
            $res->result = test();
            $res->isSuccess = TRUE;
            $res->code = 100;
            $res->message = "테스트 성공";
            echo json_encode($res, JSON_NUMERIC_CHECK);
            break;

        /*
         * API No. 0
         * API Name : 테스트 Path Variable API
         * 마지막 수정 날짜 : 19.04.29
         */
        case "testDetail":
            http_response_code(200);
            $res->result = testDetail($vars["testNo"]);
            $res->isSuccess = TRUE;
            $res->code = 100;
            $res->message = "테스트 성공";
            echo json_encode($res, JSON_NUMERIC_CHECK);
            break;
        /*
         * API No. 0
         * API Name : 테스트 Body & Insert API
         * 마지막 수정 날짜 : 19.04.29
         */
        case "testPost":
            http_response_code(200);
            $res->result = testPost($req->name);
            $res->isSuccess = TRUE;
            $res->code = 100;
            $res->message = "테스트 성공";
            echo json_encode($res, JSON_NUMERIC_CHECK);
            break;

        case "userDetail":
            http_response_code(200);
            $jwt = $_SERVER["HTTP_X_ACCESS_TOKEN"];

            if (!isValidHeader($jwt, JWT_SECRET_KEY)) {
                failRes($res,"유효하지 않은 토큰입니다",201);
                addErrorLogs($errorLogs, $res, $req);
                break;
            }
            $userNo = getUserNoFromHeader($jwt, JWT_SECRET_KEY);
            $res->result = userDetail($userNo);
            successRes($res,"유저정보 조회 성공");
            break;

        case "updateUserNick":
            http_response_code(200);
            $jwt = $_SERVER["HTTP_X_ACCESS_TOKEN"];
            if (!isValidHeader($jwt, JWT_SECRET_KEY)) {
                failRes($res,"유효하지 않은 토큰입니다",201);
                addErrorLogs($errorLogs, $res, $req);
                break;
            }
            $userNo = getUserNoFromHeader($jwt, JWT_SECRET_KEY);
            $check_nick = preg_match($nick_regex,$req->nickName);

            if($check_nick!=true){
                failRes($res,"nickname값 에러",203);
                //4~15자 영어 숫자 한글만
                break;
            }
            if(isNickNameUnique($req->nickName)){
                failRes($res,"해당 닉네임 존재",204);
                break;
            }
            if($req->nickName) {
                $res->result = updateUserNickName($userNo, $req->nickName);
            }else{
                failRes($res,"wrong query",202);
                break;
            }
            successRes($res,"닉네임 업데이트 성공");
            break;

        case "updateUserProfileImg":
            http_response_code(200);
            $jwt = $_SERVER["HTTP_X_ACCESS_TOKEN"];
            if (!isValidHeader($jwt, JWT_SECRET_KEY)) {
                failRes($res,"유효하지 않은 토큰입니다",201);
                addErrorLogs($errorLogs, $res, $req);
                break;
            }
            $userNo = getUserNoFromHeader($jwt, JWT_SECRET_KEY);
            $check_img = preg_match($img_regex,$req->profileImg);
            if(!$check_img){
                failRes($res,"jpg png bmp 형식이 아님",203);
                break;
            }

            if($req->profileImg){
                $res->result = updateUserProfileImg($userNo,$req->profileImg);
            }else{
                failRes($res,"wrong query",202);
                break;
            }
            successRes($res,"프로필 업데이트 성공");
            break;
        case "createUser":

            $check_nick = preg_match($nick_regex,$req->nickName);
            if($check_nick!=true){
                failRes($res,"nickname값 에러",203);
                //4~15자 영어 숫자 한글만
                break;
            }
            if(isNickNameUnique($req->nickName)){
                failRes($res,"해당 닉네임 존재",204);
                break;
            }
            if($req->profileImg!=null){
                $check_img = preg_match($img_regex,$req->profileImg);
                if(!$check_img){
                    failRes($res,"jpg png bmp 형식이 아님",203);
                    break;
                }
            }
            $check_id = preg_match($id_regex,$req->userId);
            if($check_id!=true){
                failRes($res,"아이디 값 에러",203);
                //5~15자 영어 숫자
                break;
            }
            if(isIdUnique($req->userId)){
                failRes($res,"해당 ID 존재",204);
                break;
            }
            $check_pwd = preg_match($pwd_regex,$req->userPwd);
            if($check_pwd!=true){
                failRes($res,"비밀번호 값 에러",203);
                //10~20자 영어 숫자 특수문자
                break;
            }
            $res->result = createUser($req->userId,$req->userPwd,$req->nickName,$req->studentId,$req->collegeName,$req->profileImg);
            successRes($res,"유저 생성 성공");
            break;
        case "deleteUser":

            $jwt = $_SERVER["HTTP_X_ACCESS_TOKEN"];
            if (!isValidHeader($jwt, JWT_SECRET_KEY)) {
                failRes($res,"유효하지 않은 토큰입니다",201);
                addErrorLogs($errorLogs, $res, $req);
                break;
            }
            $userNo = getUserNoFromHeader($jwt, JWT_SECRET_KEY);
            http_response_code(200);
            $res->result = deleteUser($userNo);
            successRes($res,"유저 삭제 성공");
            break;
     

    }
} catch (\Exception $e) {
    return getSQLErrorException($errorLogs, $e, $req);
}
