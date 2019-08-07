<?php
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

require '../vendor/autoload.php';
require '../includes/DbOperations.php';
 
$app = new \Slim\App([
    'settings'=>[
        'displayErrorDetails'=>true
    ]
]);
 
$app->post('/createuser', function(Request $request, Response $response){
    if(!haveEmptyParameters(array('name', 'email', 'password'), $request, $response)) {

        $requestData = $request->getParsedBody();

        $name = $requestData['name'];
        $email = $requestData['email'];
        $password = $requestData['password'];

        $hash_password = password_hash($password, PASSWORD_DEFAULT);

        $db = new DbOperations;

        $result = $db->createUser($name, $email, $hash_password);

        if($result == USER_CREATED) {

            $message = array();
            $message['error'] = false;
            $message['message'] = 'User created successfully';

            $response->write(json_encode($message));

            return $response
                        ->withHeader('Content-type', 'application/json')
                        ->withStatus(201);

        } else if($result == USER_FAILURE) {

            $message = array();
            $message['error'] = true;
            $message['message'] = 'Some error occured';

            $response->write(json_encode($message));

            return $response
                        ->withHeader('Content-type', 'application/json')
                        ->withStatus(422);

        } else if($result == USER_EXISTS) {

            $message = array();
            $message['error'] = true;
            $message['message'] = 'User Already Exists';

            $response->write(json_encode($message));

            return $response
                        ->withHeader('Content-type', 'application/json')
                        ->withStatus(422);

        }

    }
    return $response
                ->withHeader('Content-type', 'application/json')
                ->withStatus(422);    
});

$app->post('/userlogin', function(Request $request, Response $response) {

    if(!haveEmptyParameters(array('email', 'password'), $request, $response)) {

        $requestData = $request->getParsedBody();

        $email = $requestData['email'];
        $password = $requestData['password'];

        $db = new DbOperations;
        $result = $db->userLogin($email, $password); 
        //return $response->write(json_encode($result));
        if($result == USER_AUTHENTICATED) {
            $user = $db->getUserByEmail($email);
            $responseData = array();

            $responseData['error'] = false;
            $responseData['message'] = 'Login successfull';
            $responseData['user'] = $user;

            $response->write(json_encode($responseData));

            return $response
                        ->withHeader('Content-type', 'application/json')
                        ->withStatus(200);

        } else if($result == USER_NOT_FOUND) {
            $responseData = array();
            $responseData['error'] = true;
            $responseData['message'] = 'User not exist'; 

            $response->write(json_encode($responseData));

            return $response
                        ->withHeader('Content-type', 'application/json')
                        ->withStatus(404);

        } else if($result == USER_PASSWORD_DO_NOT_MATCH) {
            $responseData = array();

            $responseData['error'] = true;
            $responseData['message'] = 'Invalid credential';

            $response->write(json_encode($responseData));

            return $response
                        ->withHeader('Content-type', 'application/json')
                        ->withStatus(422);
        }

    }

    return $response
                ->withHeader('Content-type', 'application/json')
                ->withStatus(422);

});


function haveEmptyParameters($requiredParams, $request, $response) {
    $error = false;
    $errorParams = '';
    $requestParams = $request->getParsedBody();

    foreach($requiredParams as $param) {
        if(!isset($requestParams[$param]) || strlen($requestParams[$param]) <= 0) {
            $error = true;
            $errorParams .= $param . ',';            
        }
    } 

    if($error) {
        $errorDetail = array();
        $errorDetail['error'] = true;
        $errorDetail['message'] = 'Required parameters ' . substr($errorParams, 0, -2) . ' are missing or empty';
        $response->write(json_encode($errorDetail));        
    }
    return $error;
}

$app->run();