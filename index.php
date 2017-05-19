<?php

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
