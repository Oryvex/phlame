<?php 
$segment = Api::segment("out", ["data" => "status"]);
Api::send($segment);
?>