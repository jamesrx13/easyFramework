<?php

namespace core\main\controllers;

use core\main\FrameworkMain;
use core\main\models\JwtModel;
use core\main\models\UserModel;
use core\utils\Utils;

class AuthController
{

    const FOLDER_PROFILE_UPLOAD = 'users/profile';

    public function __construct()
    {
    }

    public static function login()
    {
        $requiredParams = [
            'user_name',
            'password',
        ];

        if (Utils::validateRequestParams($requiredParams)) {
            $values = Utils::getRequestParams($requiredParams);
            $userModel = new UserModel();

            $userData = $userModel->existUser($values);

            if ($userData) {

                $userModel->load(null, $userData);

                if($userModel->status != UserModel::STATUS_ACTIVATE){
                    Utils::userNotActivate($userModel->status);
                }

                if (FrameworkMain::verifyPassword($values['password'], $userModel->password)) {


                    $jwtModel = new JwtModel();

                    $response = $jwtModel->deleteTokenByUserId($userModel->id);

                    if ($response['status']) {
                        $jwt = $jwtModel->generateToken(JwtModel::DAY_TOKEN, $userModel->id);

                        $jwtModel->load(null, [
                            'user_id' => $userModel->id,
                            'token' => $jwt,
                        ]);

                        $jwtModel->save(false);

                        FrameworkMain::genericApiResponse([
                            "status" => true,
                            "AuthToken" => $jwt,
                            "data" => $userModel->publicData()
                        ]);
                    } else {
                        FrameworkMain::genericApiResponse($response);
                    }
                } else {
                    Utils::UserIncorrectPassword();
                }
            } else {
                Utils::UserNotFound();
            }
        }
    }

    public static function userList(){

        $jwtModel = new JwtModel();
        $jwtModel->token = FrameworkMain::getRequestHeader(Utils::getEnv('HEADER_TOKEN'));

        $currentUser = new UserModel($jwtModel->getUserId());

        if($currentUser->id){

            $searchParams = (object) Utils::getRequestParams(['search']);

            $toSearch = '';
    
            if(isset($searchParams->search)){
                $toSearch = $searchParams->search;
            }

            $userList = $currentUser->getAllBy("
                (id != :currentUserId) 
                AND (user_name LIKE :filter 
                OR email LIKE :filter 
                OR name LIKE :filter 
                OR last_name LIKE :filter
                OR CONCAT(name, ' ', last_name) LIKE :filter)",
                [
                    ':currentUserId' => $currentUser->id,
                    ':filter' => "%{$toSearch}%",
                ],
                false,
                true
            );

            foreach($userList['data'] as $key => $user){
                $currentUser->load(null, $user);
                $userList['data'][$key] = $currentUser->publicData();
            }

            FrameworkMain::genericApiResponse($userList);

        } else {
            Utils::UserNotFound();
        }
    }

    public static function isValidToken()
    {
        $jwtModel = new JwtModel();
        $jwtModel->token = FrameworkMain::getRequestHeader(Utils::getEnv('HEADER_TOKEN'));
        return $jwtModel->validateToken();
    }

    public static function userRegister()
    {
        $requiredParams = [
            'user_name',
            'email',
            'password',
            'name',
            'last_name',
        ];

        $files = [
            'profilePhoto'
        ];

        if (Utils::validateRequestParams($requiredParams)) {
            $values = Utils::getRequestParams($requiredParams);

            $userModel = new UserModel();

            if($userModel->existUser($values)){
                FrameworkMain::genericApiResponse([
                   'status' => false,
                   'msg' => "The user {$values['user_name']} or email {$values['email']} already exist"
                ]);
            }
            
            $userModel->load(null, $values);
            
            if(Utils::validateRequestFiles($files, false)){
                $userModel->profilePhoto = Utils::uploadAccess(
                    Utils::getRequestFiles($files), 
                    FrameworkMain::IMAGES_FORMAT, 
                    self::FOLDER_PROFILE_UPLOAD, 
                )[0];
            }
            
            $userModel->password = FrameworkMain::hashPassword($userModel->password);
            $userModel->save();            
        }
        
    }

    public static function customUserRegister()
    {
        $requiredParams = [
            'user_name',
            'email',
            'password',
            'name',
            'last_name',
            'rol',
        ];

        $files = [
            'profilePhoto'
        ];

        if (Utils::validateRequestParams($requiredParams)) {
            $values = Utils::getRequestParams($requiredParams);
            
            $userModel = new UserModel();
            
            if($userModel->existUser($values)){
                FrameworkMain::genericApiResponse([
                    'status' => false,
                    'msg' => "The user {$values['user_name']} or email {$values['email']} already exist"
                ]);
            }

            $values = (object) $values;

            if(!key_exists($values->rol, UserModel::USERS_ROL_PLACEHOLDER)){
                FrameworkMain::genericApiResponse([
                    'status' => false,
                    'msg' => 'Information specification error',
                ]);
            }

            $userModel->load(null, $values);

            if(Utils::validateRequestFiles($files, false)){
                $files = Utils::getRequestFiles($files);
                $userModel->profilePhoto = Utils::uploadAccess(
                    Utils::getRequestFiles($files), 
                    FrameworkMain::IMAGES_FORMAT, 
                    self::FOLDER_PROFILE_UPLOAD, 
                )[0];
            }

            $userModel->password = FrameworkMain::hashPassword($userModel->password);
            $userModel->save();
        }
    }

    public static function updateMyUser()
    {
        $requiredParams = [
            'user_name',
            'email',
            'name',
            'last_name',
            'password',
        ];

        $files = [
            'profilePhoto'
        ];

        if (Utils::validateRequestParams($requiredParams)) {

            $values = (object) Utils::getRequestParams($requiredParams);

            $jwtModel = new JwtModel();
            $jwtModel->token = FrameworkMain::getRequestHeader(Utils::getEnv('HEADER_TOKEN'));

            $tokenData = (object) $jwtModel->getTokenData();

            $userModel = new UserModel($tokenData->userId);

            if ($userModel->id != null) {

                if (FrameworkMain::verifyPassword($values->password, $userModel->password)) {

                    if($userModel->existUser((array) $values) && $userModel->user_name != $values->user_name){
                        FrameworkMain::genericApiResponse([
                            'status' => false,
                            'msg' => 'The user name or email is already in use',
                        ]);
                    }

                    $userModel->load(null, $values);

                    if(Utils::validateRequestFiles($files, false)){

                        $fileName = $userModel->profilePhoto !== null ? Utils::getFileName($userModel->profilePhoto) : '';

                        $userModel->profilePhoto = Utils::uploadAccess(
                            Utils::getRequestFiles($files), 
                            FrameworkMain::IMAGES_FORMAT, 
                            self::FOLDER_PROFILE_UPLOAD, 
                            $fileName,
                        )[0];
                    }

                    $userModel->password = FrameworkMain::hashPassword($userModel->password);
                    
                    $userModel->update(false);

                    FrameworkMain::genericApiResponse([
                        'status' => true,
                        'msg' => 'Your user has been updated',
                        'data' => $userModel->publicData()
                    ]);

                } else {
                    Utils::UserIncorrectPassword();
                }
            } else {
                Utils::UserNotFound();
            }
        }
    }

    public static function changeMyPassword()
    {
        $requiredParams = [
            'currentPassword',
            'newPassword',
            'confirmPassword',
        ];

        if (Utils::validateRequestParams($requiredParams)) {

            $values = (object) Utils::getRequestParams($requiredParams);

            $jwtModel = new JwtModel();
            $jwtModel->token = FrameworkMain::getRequestHeader(Utils::getEnv('HEADER_TOKEN'));
            $tokenData = (object) $jwtModel->getTokenData();
            $userModel = new UserModel($tokenData->userId);

            if ($userModel->id != null) {

                if (FrameworkMain::verifyPassword($values->currentPassword, $userModel->password)) {

                    if($values->newPassword == $values->confirmPassword){
                        $userModel->password = FrameworkMain::hashPassword($values->newPassword);
                        $userModel->update();
                    } else {
                        FrameworkMain::genericApiResponse([
                            'status' => false,
                            'msg' => 'Passwords do not match'
                        ]);
                    }

                } else {
                    Utils::UserIncorrectPassword();
                }
            } else {
                Utils::UserNotFound();
            }
        }
    }

    public static function resetPassword()
    {
        // TODO:
        // Utils::sendEmail('rudasmarinjf@hotmail.com', 'Test demo', 'Hola mundo test');
    }

    public static function logoutFnt()
    {
        $token = FrameworkMain::getRequestHeader(Utils::getEnv('HEADER_TOKEN'));
        $res = (new JwtModel())->executeMainQuery("DELETE FROM :table WHERE token = '{$token}'");
        FrameworkMain::genericApiResponse($res);
    }

    public static function generateCustomToken(){
        $requiredParams = [
            'tokenType',
            'description',
        ];

        if(Utils::validateRequestParams($requiredParams)){
            $values = (object) Utils::getRequestParams($requiredParams);
            $jwtModel = new JwtModel();
            $jwtModel->token = FrameworkMain::getRequestHeader(Utils::getEnv('HEADER_TOKEN'));
            $userId = ((object) $jwtModel->getTokenData())->userId;            

            $newToken = $jwtModel->generateToken($values->tokenType, $userId);

            if(is_bool($newToken) && !$newToken){
                FrameworkMain::genericApiResponse([
                    'status' => false,
                    'msg' => 'Invalid token type'
                ]);
            }

            $jwtModel->token = $newToken;
            $jwtModel->user_id = $userId;
            $jwtModel->isSessionToken = '0';
            $jwtModel->description = $values->description;
            $jwtModel->save(false);

            FrameworkMain::genericApiResponse([
                'status' => true,
                'token' => $newToken,
                'msg' => JwtModel::TOKEN_TEXT[$values->tokenType]
            ]);
        }
    }

    public static function getMyCustomsTokens(){
        $jwtModel = new JwtModel();
        $jwtModel->token = FrameworkMain::getRequestHeader(Utils::getEnv('HEADER_TOKEN'));
        $userId = $jwtModel->getUserId();

        $searchParams = (object) Utils::getRequestParams(['search']);

        $toSearch = '';
    
        if(isset($searchParams->search)){
            $toSearch = $searchParams->search;
        }

        $response = $jwtModel->getAllBy(
            "(user_id = :userId) 
            AND (isSessionToken = :isSessionToken)
            AND (description LIKE :filter OR token LIKE :filter)", 
            [
                ':userId' => $userId,
                ':isSessionToken' => '0',
                ':filter' => "%{$toSearch}%",
            ], 
        false, true);

        if($response['status']){
            foreach($response['data'] as $key => $value){
                $response['data'][$key]['status'] = (bool) $value['status'];
            }
        }

        FrameworkMain::genericApiResponse($response);

    }

    public static function deleteToken(){
        $requiredParams = [
            'tokenId',
            'password',
        ];

        if(Utils::validateRequestParams($requiredParams)){
            $values = (object) Utils::getRequestParams($requiredParams);

            $jwtModel = new JwtModel($values->tokenId);

            if($jwtModel->id){
                $jwtModel->token = FrameworkMain::getRequestHeader(Utils::getEnv('HEADER_TOKEN'));
                $user = new UserModel($jwtModel->getUserId());

                if($user->id){

                    if(FrameworkMain::verifyPassword($values->password, $user->password)){
                        $jwtModel->delete();
                    } else {
                        FrameworkMain::genericApiResponse([
                            'status' => false,
                            'msg' => 'Invalid password'
                        ]);
                    }

                } else {
                    FrameworkMain::genericApiResponse([
                        'status' => false,
                        'msg' => 'User not found'
                    ]);
                }

            } else {
                FrameworkMain::genericApiResponse([
                    'status' => false,
                    'msg' => 'Token not found'
                ]);
            }
            

        }
    }

    public static function changeStatusToken(){
        $requiredParams = [
            'tokenId'
        ];

        if(Utils::validateRequestParams($requiredParams)){
            $values = (object) Utils::getRequestParams($requiredParams);
            $jwtModel = new JwtModel();
            $jwtModel->token = FrameworkMain::getRequestHeader(Utils::getEnv('HEADER_TOKEN'));
            $userId = ((object) $jwtModel->getTokenData())->userId;

            $tokenModel = new JwtModel($values->tokenId);

            if($tokenModel->user_id != $userId){
                FrameworkMain::genericApiResponse([
                    'status' => false,
                    'msg' => 'You cannot modify this token'
                ]);
            }

            $tokenModel->status =  (String) !$tokenModel->status;
            $tokenModel->update();           

        }
    }

    public static function getTokensInfo(){
        $tokens = [];

        foreach(JwtModel::TOKEN_TEXT as $key => $tokenText){
            $tokens[] = [
                'type' => $key,
                'msg' => $tokenText
            ];
        }

        FrameworkMain::genericApiResponse([
            'status' => true,
            'totalElements' => count($tokens),
            'data' => $tokens
        ]);
    }

    public static function changeStatus(){
        $requestParams = [
            'id'
        ];

        if(Utils::validateRequestParams($requestParams)){
            $values = (object) Utils::getRequestParams($requestParams);
            $model = new UserModel($values->id);
            if($model->id){
                $model->changeStatus();
            } else {
                Utils::UserNotFound();
            }
        }
    }

    public static function validateRol($roles){
        $jwtModel = new JwtModel();
        $jwtModel->token = FrameworkMain::getRequestHeader(Utils::getEnv('HEADER_TOKEN'));
        $tokenInfo = (object) $jwtModel->getTokenData(); 

        $userModel = new UserModel($tokenInfo->userId);

        if($userModel->rol == UserModel::USER_ROL_ROOT) return;

        if(is_array($roles)){

            if(!in_array((int) $userModel->rol, $roles)){
                Utils::NoPermission();
            }

        } elseif(is_int($roles)){

            if($userModel->rol != $roles){
                Utils::NoPermission();
            }

        } else {
            FrameworkMain::genericApiResponse([
                'status' => false,
                'msg' => 'Unknown role'
            ]);
        }

    }

    public static function rolesInformation(){
        FrameworkMain::genericApiResponse([
            'status' => true,
            'totalElements' => count(UserModel::USERS_ROL_PLACEHOLDER),
            'data' => UserModel::USERS_ROL_PLACEHOLDER,
        ]);
    }

    public static function verifyToken() {
        $jwtModel = new JwtModel();
        $jwtModel->token = FrameworkMain::getRequestHeader(Utils::getEnv('HEADER_TOKEN'));

        $isValidToken = $jwtModel->validateToken();

        FrameworkMain::genericApiResponse([
            'status' => $isValidToken,
            'data' => $isValidToken ? (new UserModel(((object) $jwtModel->getTokenData())->userId))->publicData() : new UserModel()
        ]);
    }

    public static function updateUser() {
        $requiredParams = [
            'id',
            'user_name',
            'email',
            'name',
            'last_name',
            'validate_password',
        ];

        $files = [
            'profilePhoto'
        ];

        if(Utils::validateRequestParams($requiredParams)){

            $values = (object) Utils::getRequestParams($requiredParams);
            $model = new UserModel($values->id);

            $jwtModel = new JwtModel();
            $jwtModel->token = FrameworkMain::getRequestHeader(Utils::getEnv('HEADER_TOKEN'));
            $currentUser = new UserModel($jwtModel->getUserId());

            if(!FrameworkMain::verifyPassword($values->validate_password, $currentUser->password)){
                FrameworkMain::genericApiResponse([
                   'status' => false,
                   'msg' => 'Invalid admin password',
                ]);
            }
            
            if($model->id){
                
                if($model->existUser((array) $values) && $model->user_name != $values->user_name){
                    FrameworkMain::genericApiResponse([
                       'status' => false,
                       'msg' => 'The user name or email already exist',
                    ]);
                }
                
                $model->user_name = $values->user_name;
                $model->email = $values->email;
                $model->name = $values->name;
                $model->last_name = $values->last_name;

                if(Utils::validateRequestFiles($files, false)){

                    $fileName = $model->profilePhoto !== null ? Utils::getFileName($model->profilePhoto) : '';

                    $model->profilePhoto = Utils::uploadAccess(
                        Utils::getRequestFiles($files), 
                        FrameworkMain::IMAGES_FORMAT, 
                        self::FOLDER_PROFILE_UPLOAD, 
                        $fileName,
                    )[0];
                }

                $model->update();
                
            } else {
                Utils::UserNotFound();
            }

        }
    }

    public static function changeUserPassword() {
        $requiredParams = [
            'id',
            'new_password',
            'comfirm_password',
            'validate_password',
        ];

        if(Utils::validateRequestParams($requiredParams)){
            $values = (object) Utils::getRequestParams($requiredParams);
            $model = new UserModel($values->id);

            $jwtModel = new JwtModel();
            $jwtModel->token = FrameworkMain::getRequestHeader(Utils::getEnv('HEADER_TOKEN'));
            $currentUser = new UserModel($jwtModel->getUserId());
            
            if(!FrameworkMain::verifyPassword($values->validate_password, $currentUser->password)){
                FrameworkMain::genericApiResponse([
                   'status' => false,
                   'msg' => 'Invalid admin password',
                ]);
            }

            if($values->new_password != $values->comfirm_password){
                FrameworkMain::genericApiResponse([
                   'status' => false,
                   'msg' => 'Passwords not mach',
                ]);
            }

            if($model->id){
                $model->password = FrameworkMain::hashPassword($values->new_password);
                $model->update();
            } else {
                Utils::UserNotFound();
            }

        }
    }

    public static function routes($operation)
    {
        // Se establece la ruta por defecto

        if (is_object($operation)) {
            $operation = $operation->route;
        }

        if ($operation == '') {
            // $operation = 'login';
        }

        $adminOpt = UserModel::USER_ROL_ADMIN;

        $operations = [
            'login' => [
                'fnt' => 'login',
                'method' => 'POST',
            ],
            'register' => [
                'fnt' => 'userRegister',
                'method' => 'POST',
            ],
            'list' => [
                'fnt' => 'userList',
                'method' => 'GET',
                'auth' => true,
                'roles' => $adminOpt,
            ],
            'registerCustomUser' => [
                'fnt' => 'customUserRegister',
                'method' => 'POST',
                'auth' => true,
                'roles' => $adminOpt,
            ],
            'updateMyUser' => [
                'fnt' => 'updateMyUser',
                'method' => 'POST',
                'auth' => true,
            ],
            'changeMyPassword' => [
                'fnt' => 'changeMyPassword',
                'method' => 'POST',
                'auth' => true,
            ],
            'generateToken' => [
                'fnt' => 'generateCustomToken',
                'method' => 'POST',
                'auth' => true,
                'roles' => $adminOpt,
            ],
            'tokenChangeStatus' => [
                'fnt' => 'changeStatusToken',
                'method' => 'POST',
                'auth' => true,
                'roles' => $adminOpt,
            ],
            'tokensInformation' => [
                'fnt' => 'getTokensInfo',
                'method' => 'GET',
                'auth' => true,
                'roles' => $adminOpt,
            ],
            'rolesInformation' => [
                'fnt' => 'rolesInformation',
                'method' => 'GET',
                'auth' => true,
                'roles' => $adminOpt,
            ],
            'changeStatus' => [
                'fnt' => 'changeStatus',
                'method' => 'POST',
                'auth' => true,
                'roles' => $adminOpt,
            ],
            'verifyToken' => [
                'fnt' => 'verifyToken',
                'method' => 'POST',
            ],
            'updateUser' => [
                'fnt' => 'updateUser',
                'method' => 'POST',
                'auth' => true,
                'roles' => $adminOpt,
            ],
            'changeUserPassword' => [
                'fnt' => 'changeUserPassword',
                'method' => 'POST',
                'auth' => true,
                'roles' => $adminOpt,
            ],
            'getMyCustomsTokens' => [
                'fnt' => 'getMyCustomsTokens',
                'method' => 'GET',
                'auth' => true,
                'roles' => $adminOpt,
            ],
            'deleteToken' => [
                'fnt' => 'deleteToken',
                'method' => 'POST',
                'auth' => true,
                'roles' => $adminOpt,
            ],
            // 'resetPassword' => [
            //     'fnt' => 'resetPassword',
            //     'method' => 'POST',
            // ],
            'logout' => [
                'fnt' => 'logoutFnt',
                'method' => 'POST',
                'auth' => true,
            ],
        ];

        if (!array_key_exists($operation, $operations)) {
            Utils::RouteNotFound();
            die();
        }

        return (object) $operations[$operation];
    }
}