<?php
namespace App\Handlers;

class Service {
	public static function load() {
        try {
            $response = new \stdClass;
            $response->id = null;
            $response->result = null;
            $response->error = null;

            $raw = file_get_contents('php://input');
            $request = json_decode($raw);

            if(!is_object($request)) {
                throw new \InvalidArgumentException('Request is not an object');
            }

            if(!property_exists($request, 'id') || !is_string($request->id)) {
                throw new \InvalidArgumentException('Request id is not a string');
            }

			$response->id = $request->id;

            if(!property_exists($request, 'method') && !is_string($request->method)) {
                throw new \InvalidArgumentException('Request method is not a string');
            }

            if(!property_exists($request, 'params')) {
                throw new \InvalidArgumentException('Request has no params field');
            }

            if(!preg_match('/^([a-z0-9]+)::([a-z0-9]+)$/i', $request->method, $matches)) {
				throw new \InvalidArgumentException('Request method is misformed');
            }

            list( , $moduleKey, $method) = $matches;

			$class = '\\App\\Service\\'.$moduleKey;
			$filename = 'code/server/php/App/Service/'.$moduleKey.'.php';

			if(!is_file($filename) || !class_exists($class)) {
				throw new \InvalidArgumentException('Controller does not exist');
			}

			if(!is_callable(Array($class, $method))) {
				throw new \InvalidArgumentException('Method is not callable');
			}

			$response->result = $class::$method($request->params);
		}
		catch(\Exception $e) {
			if(is_null($response->id)) {
				$response->id = uniqid();
			}

			$response->error = Array(
                'code' => $e->getCode(),
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            );
		}

		header('Content-Type: application/json; charset=utf-8');
        $output = json_encode($response);
        // TODO detect json_encode problem
        print($output);
	}
}
