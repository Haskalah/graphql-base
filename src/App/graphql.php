<?php
// Test this using following command
// php -S localhost:8080 ./graphql.php
require_once __DIR__ . '/../../vendor/autoload.php';

use App\Models\Firebase_Model;
use App\Models\User_Model;
use App\Type\Types;
use App\Context\AppContext;
use \GraphQL\Type\Schema;
use \GraphQL\GraphQL;
use \GraphQL\Error\FormattedError;
use \GraphQL\Error\Debug;

// Disable default PHP error reporting - we have better one for debug mode (see bellow)
ini_set('display_errors', 0);

$debug = false;
if (!empty($_GET['debug'])) {
    set_error_handler(function($severity, $message, $file, $line) use (&$phpErrors) {
        throw new ErrorException($message, 0, $severity, $file, $line);
    });
    $debug = Debug::INCLUDE_DEBUG_MESSAGE | Debug::INCLUDE_TRACE;
}
header('Access-Control-Allow-Methods: GET,PUT,POST,DELETE,PATCH,OPTIONS');
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_METHOD']) && $_SERVER['HTTP_ACCESS_CONTROL_REQUEST_METHOD'] == 'POST') {
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Headers: X-Requested-With, content-type, access-control-allow-origin, access-control-allow-methods, access-control-allow-headers, authorization');
    }
}

header('Access-Control-Allow-Origin: *');

try {

    // Prepare context that will be available in all field resolvers (as 3rd argument):
    $appContext = new AppContext();
    $appContext->rootUrl = 'http://localhost:8080';
    $appContext->request = $_REQUEST;

    // Parse incoming query and variables
    if (isset($_SERVER['CONTENT_TYPE']) && strpos($_SERVER['CONTENT_TYPE'], 'application/graphql') !== false) {
        $raw = file_get_contents('php://input') ?: '';
        $data = json_decode($raw, true) ?: [];
    } else {
        $data = $_REQUEST;
    }
    $testData = [];

    try {
      $firebase = new Firebase_Model();
      $userToken = $firebase->authenticate();
      $appContext->user = (new User_Model())->loadByFirebaseToken($userToken);
      $testData['auth'] = $userToken;
    } catch (Exception $e) {
      $testData['authError'] = $e->getMessage();
    }

    if (!isset($data['query'])) {
      $output = null;
      $httpStatus = 200;
    } else {
      if (null === $data['query']) {
        $testData['foundNull'] = true;
        //throw new Exception("Query cannot be null!");
      }

      // GraphQL schema to be passed to query executor:
      $schema = new Schema(['query' => Types::query()]);

      $result = GraphQL::executeQuery(
          $schema,
          $data['query'],
          null,
          $appContext,
          (array) $data['variables']
      );
      $output = $result->toArray($debug);
      $output['test'] = $testData;
      $httpStatus = 200;
    }

} catch (\Exception $error) {
    $httpStatus = 500;
    $output['errors'] = [
        FormattedError::createFromException($error, $debug)
    ];
}

header('Content-Type: application/json', true, $httpStatus);
if ($output !== null) {
  echo json_encode($output);
}
