<?php
// api/login.php
header("Content-Type: application/json");
require_once '../controller/AuthController.php';

$auth = new AuthController();
$data = json_decode(file_get_contents("php://input"));

if ($data) {
  $res = $auth->login($data->username, $data->password);
  // Thêm dòng này để xem JS gửi gì lên
  $res['check_input'] = [
    "user_sent" => $data->username,
    "pass_sent" => $data->password
  ];
  echo json_encode($res);
}