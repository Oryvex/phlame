<?php 
class Source{

  
  public static function nulltype(){
      return "SET:NULL";
  }

  public static function type($type){
    header("Content-Type: $type");
  }
  
  public static function set($page, $variables = [], $format = "application/json") {

      $docroot = $_SERVER['DOCUMENT_ROOT'];
      if ($format != "SET:NULL") {
          header("Content-Type: $format");
      }

      $spage = $_SERVER['DOCUMENT_ROOT'] . "/source/$page.src.php";

      if (is_file($spage)) {
          if (!empty($variables)) {
              extract($variables); 
          }

          ob_start();
          include $spage;
          $html = ob_get_clean();
          echo $html;
      } else {
          include $_SERVER['DOCUMENT_ROOT'] . "/.config/_404.php";
      }
  }

  public static function empty(&$var, $value){
      if(!isset($var)){
          $var = $value;
      }
  }
}

?>