<?
// just create from the proto file a pb_prot[NAME].php file
require_once('../parser/pb_parser.php');

$test = new PBParser();
$test->parse('./message.proto');

?>