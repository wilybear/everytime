<?php
require 'function.php';

const JWT_SECRET_KEY = "TEST_KEYTEST_KEYTEST_KEYTEST_KEYTEST_KEYTEST_KEYTEST_KEYTEST_KEYTEST_KEYTEST_KEYTEST_KEYTEST_KEYTEST_KEY";
$id_regex="/^[!^0-9a-zA-Z]{5,15}$/u";
$pwd_regex = "/^[0-9A-Za-z~!@#$%^&*]{10,20}$/i";
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
        case "validateJwt":
            // jwt 유효성 검사

            $jwt = $_SERVER["HTTP_X_ACCESS_TOKEN"];

            if (!isValidHeader($jwt, JWT_SECRET_KEY)) {
                $res->isSuccess = FALSE;
                $res->code = 201;
                $res->message = "유효하지 않은 토큰입니다";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                addErrorLogs($errorLogs, $res, $req);
                return;
            }

            http_response_code(200);
            $res->isSuccess = TRUE;
            $res->code = 100;
            $res->message = "테스트 성공";

            echo json_encode($res, JSON_NUMERIC_CHECK);
            break;
        /*
         * API No. 1
         * API Name : JWT 생성 테스트 API (로그인)
         * 마지막 수정 날짜 : 19.04.25
         */
        case "createJwt":
            // jwt 유효성 검사
            http_response_code(200);
            $check_id = preg_match($id_regex,$req->id);
            if($check_id!=true){
                failRes($res,"올바르지 않은 아이디값입니다.",203);
                //5~15자 영어 숫자
                break;
            }
            $check_pwd = preg_match($pwd_regex,$req->pw);
            if($check_pwd!=true){
                failRes($res,"올바르지 않은 비밀번호값입니다.",203);
                //10~20자 영어 숫자 특수문자
                break;
            }
            if(!isValidUser($req->id, $req->pw)){
                $res->isSuccess = FALSE;
                $res->code = 200;
                $res->message = "유효하지 않은 아이디 입니다";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                return;
            }

            //페이로드에 맞게 다시 설정 요함
            $jwt = getJWToken($req->id, $req->pw, JWT_SECRET_KEY);
            $res->result->jwt = $jwt;
            $res->isSuccess = TRUE;
            $res->code = 100;
            $res->message = "토큰 발급 성공";
            echo json_encode($res, JSON_NUMERIC_CHECK);
            break;

    }
} catch (\Exception $e) {
    return getSQLErrorException($errorLogs, $e, $req);
}
