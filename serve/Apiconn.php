<?php

class Api
{
    public static function headers($key = null, $value = null, $then = null, $orelse = null)
    {
        $headers = getallheaders();
        if ($headers === false) {
            http_response_code(500);
            echo json_encode(["Error" => "Failed to retrieve headers."]);
            exit;
        }

        $returnStatus = false;
        $returnData = $headers;

        if (!empty($headers) && $key !== null) {
            if (isset($headers[$key])) {
                $returnStatus = true;
                if ($value !== null && $headers[$key] !== $value) {
                    $returnStatus = false;
                }
            }
        }

        $rout = [
          "status" => $returnStatus,
          "data" => $returnData,
        ];

        if ($returnStatus && is_callable($then)) {
            call_user_func($then, $rout);
        } elseif (!$returnStatus && is_callable($orelse)) {
            call_user_func($orelse, $rout);
        }

        return $rout;
    }

    public static function body($key = null, $value = null, $then = null, $orelse = null)
    {
        $body = file_get_contents("php://input");
        if ($body === false) {
            http_response_code(500);
            echo json_encode(["Error" => "Failed to retrieve request body."]);
            exit;
        }

        $returnStatus = false;
        $returnData = $body;

        if (!empty($body) && $key !== null) {
            $parsedBody = json_decode($body, true);
            if ($parsedBody === null) {
                http_response_code(400);
                echo json_encode(["Error" => "Failed to parse request body as JSON."]);
                exit;
            }
            if (isset($parsedBody[$key])) {
                $returnStatus = true;
                if ($value !== null && $parsedBody[$key] !== $value) {
                    $returnStatus = false;
                }
            }
        }

        $rout = [
          "status" => $returnStatus,
          "data" => $returnData,
        ];

        if ($returnStatus && is_callable($then)) {
            call_user_func($then, $rout);
        } elseif (!$returnStatus && is_callable($orelse)) {
            call_user_func($orelse, $rout);
        }

        return $rout;
    }

    public static function auth($prefix = null, $token = null, $hash = null, $then = null, $orelse = null, $allowfail = false)
    {
        $authorization = isset($_SERVER["HTTP_AUTHORIZATION"])
            ? $_SERVER["HTTP_AUTHORIZATION"]
            : null;

        
        $returnStatus = false;
        $returnData = $authorization;
        if ($authorization === null) {
            $returnStatus = false;
            if($allowfail == false){
                http_response_code(401);
                echo json_encode(["Error" => "Authorization header is missing."]);
                exit;
            }  
        }else{
            $returnStatus = true;
        }

      
        if($hash != null){
          $authorization = hash($hash, $authorization);
        }

        if ($prefix != null) {
            $token = rtrim($prefix) . " " . $token;
        }

        if ($token !== null && $authorization !== $token) {
            $returnStatus = false;
            if($allowfail == false){
                http_response_code(403);
                echo json_encode(["Error" => "Invalid or missing authentication token."]);
                exit;
            }
        }else{
            $returnStatus = true;
        }


        $rout = [
          "status" => $returnStatus,
          "data" => $returnData,
        ];

        if ($returnStatus && is_callable($then)) {
            call_user_func($then, $rout);
        } elseif (!$returnStatus && is_callable($orelse)) {
            call_user_func($orelse, $rout);
        }

        return $rout;
    }

    public static function params($key = null, $value = null, $then = null, $orelse = null)
    {
        $urlParams = $_GET;
        if ($urlParams === false) {
            http_response_code(500);
            echo json_encode(["Error" => "Failed to retrieve URL parameters."]);
            exit;
        }

        $returnStatus = false;
        $returnData = $urlParams;

        if (!empty($urlParams) && $key !== null) {
            if (isset($urlParams[$key])) {
                $returnStatus = true;
                if ($value !== null && $urlParams[$key] !== $value) {
                    $returnStatus = false;
                }
            }
        }

        $rout = [
          "status" => $returnStatus,
          "data" => $returnData,
        ];

        if ($returnStatus && is_callable($then)) {
            call_user_func($then, $rout);
        } elseif (!$returnStatus && is_callable($orelse)) {
            call_user_func($orelse, $rout);
        }

        return $rout;
    }

    public static function join(...$args)
    {
        $result = [];

        foreach ($args as $arr) {
            if (is_array($arr) && count($arr) > 0) {
                $key = key($arr);
                $value = $arr[$key];
                $result[$key] = $value;
            } else {
                http_response_code(500);
                echo json_encode(["Error" => "Server Side Error Caused. J-ARGS"]);
                exit;
            }
        }

        return $result;
    }

    public static function segment($name, $value)
    {
        if (!is_array($value)) {
            http_response_code(500);
            echo json_encode(["Error" => "Server Error On Segment"]);
            exit;
        }
        return array($name => $value);
    }

  public static function send($data, $key = 'segments', $status = 200, $format = 'json', $header = null, $footer = null, $ignoreformat = false)
  {
      $finalArray = [];

      if ($format === 'json') {
          $finalArray = self::prepareJsonResponse($data, $key, $header, $footer);
      } elseif ($format === 'xml') {
          $finalArray = self::prepareXmlResponse($data, $key, $header, $footer);
      } else {
          // Unsupported format
          http_response_code(400);
          if ($ignoreformat) {
              $errArr = self::prepareArrayResponse($data, $key, $header, $footer);
            echo "<pre>";
            print_r($errArr);
            echo "</pre>";
          }else{
            echo json_encode(["Error" => "Unsupported response format: $format"]);
          }    
          exit;
      }

      http_response_code($status);
      echo $finalArray;
  }

  private static function prepareJsonResponse($data, $key, $header, $footer) {
      $finalArray = [];
      if ($header != null && is_array($header) && count($header) > 0) {
          $finalArray['header'] = $header;
      }
      if (is_array($data) && count($data) > 0) {
          $finalArray[$key] = $data;
      }
      if ($footer != null && is_array($footer) && count($footer) > 0) {
          $finalArray['footer'] = $footer;
      }
      return json_encode($finalArray);
  }

  private static function prepareArrayResponse($data, $key, $header, $footer) {
      $finalArray = [];
      if ($header != null && is_array($header) && count($header) > 0) {
          $finalArray['header'] = $header;
      }
      if (is_array($data) && count($data) > 0) {
          $finalArray[$key] = $data;
      }
      if ($footer != null && is_array($footer) && count($footer) > 0) {
          $finalArray['footer'] = $footer;
      }
      return $finalArray;
  }

  private static function prepareXmlResponse($data, $key, $header, $footer) {
      $xml = new SimpleXMLElement('<root/>');
      if ($header != null && is_array($header) && count($header) > 0) {
          $headerElement = $xml->addChild('header');
          self::arrayToXml($header, $headerElement);
      }
      if (is_array($data) && count($data) > 0) {
          $dataElement = $xml->addChild($key);
          self::arrayToXml($data, $dataElement);
      }
      if ($footer != null && is_array($footer) && count($footer) > 0) {
          $footerElement = $xml->addChild('footer');
          self::arrayToXml($footer, $footerElement);
      }
      return $xml->asXML();
  }

  private static function arrayToXml($array, &$xml) {
      foreach ($array as $key => $value) {
          if (is_array($value)) {
              if (is_numeric($key)) {
                  $key = 'item'; 
              }
              $subnode = $xml->addChild($key);
              self::arrayToXml($value, $subnode);
          } else {
              $xml->addChild("$key", htmlspecialchars("$value"));
          }
      }
  }

  public static function status($code){
    http_response_code($code);
  }

  public static function sendraw($data, $status = null){
    if($status != null){
      http_response_code($status);
    }
    echo $data;
  }

  public static function b64() {
      return new class {
          public static function Encrypt($plaintext) {
              return base64_encode($plaintext);
          }

          public static function Decrypt($encrypted) {
              return base64_decode($encrypted);
          }
      };
  }

  public static function newkey($length = 39) {
      $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789-_';

      $numChars = strlen($chars);

      $apiKey = '';

      for ($i = 0; $i < $length; $i++) {
          if ($i == 0) {
              $apiKey .= chr(rand(65, 90));
          } else {
              $apiKey .= $chars[rand(0, $numChars - 1)];
          }
      }

      if (strpos($apiKey, '-') === false) {
          $position = rand(0, $length - 1);
          $apiKey = substr_replace($apiKey, '-', $position, 0);
      }

      return $apiKey;
  }

  public static function verifykey($key, $orignal){
    $rval = false;
    if(hash('sha265', $key) == hash('sha265', $orignal)){
      $rval = true; 
    }
    return $rval;
  }
  
}

?>
