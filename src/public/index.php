<?php
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

require '../vendor/autoload.php';

// config database
$config['displayErrorDetails'] = true; 
$config['addContentLengthHeader'] = false;
$config['db']['host'] = "localhost"; 
$config['db']['user'] = "root"; 
$config['db']['pass'] = ""; 
$config['db']['dbname'] = "projeto_dm107";

//config Slim and NotORM
$app = new \Slim\App(["config" => $config]);
$container = $app->getContainer();
$container['db'] = function ($c) { 
    $dbConfig = $c['config']['db']; 
    $pdo = new PDO("mysql:host=" . $dbConfig['host'] . ";dbname=" . $dbConfig['dbname'], 
    $dbConfig['user'], $dbConfig['pass']); 
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION); 
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC); 
    $db = new NotORM($pdo); 
    return $db; 
};

$app->get('/entrega/{numeroPedido}',function(Request $request, Response $response){
    $numeroPedido = $request->getAttribute('numeroPedido');
    $entrega = $this->db->entrega("numeroPedido=?",$numeroPedido)->fetch();
    
    if(empty($entrega)){
        return $response->withJson("404 NOT FOUND", 404);
    }
    
    return $response->withJson($entrega);
    });

$app->put('/entrega/{numeroPedido}', function(Request $request, Response $response) {
    $numeroPedido = $request->getAttribute('numeroPedido');
    $entrega = $this->db->entrega("numeroPedido=?",$numeroPedido)->fetch();

    if(empty($entrega)){
        return $response->withJson("404 NOT FOUND", 404);
    }

    $body = json_decode($request->getBody());

    // Update some fields
    $NomeRecebedor = $body->NomeRecebedor;
    $CPFRecebedor = $body->CPFRecebedor;
    $DataEntrega = $body->DataEntrega;

    $newEntrega = array(
        "nomeRecebedor" => $NomeRecebedor,
        "CPFRecebedor" => $CPFRecebedor,
        "DataEntrega" => date("Y-m-d H:i:s",strtotime(str_replace('/','-',$DataEntrega)))
        );

    if ($entrega->update($newEntrega) == 0) {
         return $response->withStatus(500);
    }

    return $this->db->entrega("numeroPedido=?",$numeroPedido)->fetch();

});


$app->run();

?>