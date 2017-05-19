<?php

require 'UploadException.php';

define('OUR_ARTICLE', 'Latest_plane_crash');
$FIB_MEMO = array();

main();

function main() {
  route_request(get_uri_path());
  exit(0);
}

function route_request($request_uri) {
  $valid_methods = array('GET', 'POST', 'OPTIONS');
  $request_method = $_SERVER['REQUEST_METHOD'];

  if (!in_array($request_method, $valid_methods)) {
    header('X-Wikimedia: invalid request: ' . $request_method, false, 405);
    header('Allow: ' . implode(' ', $valid_methods));
    exit(0);
  }

  $handler = "handle_" . strtolower($request_method) . "_request";
  $handler($request_uri);
}

function handle_options_request($request_uri) {
  header('Access-Control-Allow-Origin: *');
  header('Access-Control-Allow-Headers: Content-Type, Content-Range, Content-Disposition, Content-Description, X-Requested-With');
}

function handle_get_request($request_uri) {
  if ($request_uri == '/api/' . OUR_ARTICLE) {
    fibonacci(34);
    header('X-Wikimedia: The plane has crashed', false, 200);
    render_article();
  }
  else if ($request_uri == '/edit/' . OUR_ARTICLE) {
    header('X-Wikimedia: Edit the article', false, 200);
    render_editable_article();
  }
  else {
    header('X-Wikimedia: Truly, a mini site with one article', false, 404);
    echo 'Sorry, we still have not found what you are looking for.';
  }
}

function handle_post_request($request_uri) {
  if ($request_uri == '/edit/' . OUR_ARTICLE) {
    process_form();
  }
  else {
  }
}

function process_form() {
  $post_params = array();
  foreach ($_POST as $key=>$value) {
    $post_params[$key] = $value;
  }
  process_file_upload($post_params);
  print(var_export($post_params, true));
}

function process_file_upload($post_params) {
  // handle any files
  $upload_error = false;
  if ($_FILES) {
    foreach ($_FILES as $key=>$file) {
      if (isset($file['error']) && $file['error'] === UPLOAD_ERR_NO_FILE) {
        $post_params[$key] = false;
        continue; // silently skip it
      }
      if ($file['error'] !== UPLOAD_ERR_OK) {
        $err = new UploadException($file['error']);
        error_log("$key: $err");
        $upload_error = $key;
        break;
      }
      $filename = $file['name'];
      $path_parts = pathinfo($filename);
      $file_ext = isset($path_parts['extension']) ? $path_parts['extension'] : "";
      $post_params[$key] = array(
        'tmp_name'  => $file['tmp_name'],
        'orig_name' => $filename,
        'file_ext'  => $file_ext,
      );
    }
  }
  if ($upload_error) {
    throw $upload_error;
  }
}

function get_uri_path() {
  $path = preg_replace('/^.*index.php/', '', $_SERVER['REQUEST_URI']);
  return $path;
}

// we memoize (cache) the calculations to optimize. This might be defeating
// the "data massaging approximation" point of the exercise, but it also makes the simple
// use case 2000x faster.
function fibonacci($n) {
  global $FIB_MEMO;
  if (array_key_exists($n, $FIB_MEMO)) {
    return $FIB_MEMO[$n]; // comment this line out to eliminate optimization.
  }
  if ($n <= 1) {
    return $n;
  }
  $res = fibonacci($n-1) + fibonacci($n-2);
  $FIB_MEMO[$n] = $res;
  return $res;
}

function render_article() {
  echo 'A terrible thing has happened.';
}
