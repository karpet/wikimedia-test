<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);

require 'UploadException.php';

define('UPLOAD_DIR', '/tmp/wiki-uploads');
if (!file_exists(UPLOAD_DIR)) {
    mkdir(UPLOAD_DIR, 0775, true); // ugly
}

define('OUR_ARTICLE', 'Latest_plane_crash');
define('ARTICLE_PATH', 'article.html');
$ALLOWED_FILES = array('jpg', 'jpeg', 'gif', 'png');
$FIB_MEMO = array();

main();


/**
 *
 */
function main() {
    route_request(get_uri_path());
    exit(0);
}


/**
 *
 *
 * @param unknown $request_uri
 */
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


/**
 *
 *
 * @param unknown $request_uri
 */
function handle_options_request($request_uri) {
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Headers: Content-Type, Content-Range, Content-Disposition, Content-Description, X-Requested-With');
}


/**
 *
 *
 * @param unknown $request_uri
 */
function handle_get_request($request_uri) {
    if ($request_uri == '/api/' . OUR_ARTICLE) {
        fibonacci(34);
        header('X-Wikimedia: The plane has crashed', false, 200);
        render_article();
    }
    elseif ($request_uri == '/edit/' . OUR_ARTICLE) {
        header('X-Wikimedia: Edit the article', false, 200);
        render_editable_article();
    }
    else {
        header('X-Wikimedia: Truly, a mini site with one article', false, 404);
        echo 'Sorry, we still have not found what you are looking for.';
    }
}


/**
 *
 *
 * @param unknown $request_uri
 */
function handle_post_request($request_uri) {
    if ($request_uri == '/edit/' . OUR_ARTICLE) {
        process_form();
    }
    else {
    }
}


/**
 *
 */
function process_form() {
    $post_params = array();
    foreach ($_POST as $key=>$value) {
        $post_params[$key] = $value;
    }
    process_file_upload($post_params);
    write_upload($post_params);
}


/**
 *
 *
 * @param unknown $post_params
 */
function process_file_upload(&$post_params) {
    // handle any files
    $upload_error = false;
    global $ALLOWED_FILES;
    if ($_FILES) {
        foreach ($_FILES as $key=>$file) {
            error_log("key: $key file: " .var_export($file, true));
            if (isset($file['error']) && $file['error'] === UPLOAD_ERR_NO_FILE) {
                error_log("UPLOAD_ERR_NO_FILE");
                $post_params[$key] = false;
                continue; // silently skip it
            }
            error_log('file present');
            if ($file['error'] !== UPLOAD_ERR_OK) {
                error_log($file['error']);
                $err = new UploadException($file['error']);
                error_log("$key: $err");
                $upload_error = $key;
                break;
            }
            error_log('no POST error');
            $filename = $file['name'];
            $path_parts = pathinfo($filename);
            $file_ext = isset($path_parts['extension']) ? $path_parts['extension'] : "";
            $file_ext_norm = strtolower($file_ext);
            if (!strlen($file_ext) || !in_array($file_ext_norm, $ALLOWED_FILES)) {
                error_log("bad file extension $file_ext_norm");
                $err = new UploadException(UPLOAD_ERR_EXTENSION);
                $upload_error = $key;
                break;
            }
            error_log('upload allowed');
            $file_id = hash('sha256', $filename . uniqid());
            $post_params[$key] = array(
                'tmp_name'  => $file['tmp_name'],
                'orig_name' => $filename,
                'file_ext'  => $file_ext,
                'type'      => $file['type'],
                'target'    => $file_id . ".${file_ext}",
            );
        }
    }
    if ($upload_error) {
        throw $upload_error;
    }

    error_log(var_export($post_params, true));

    return $post_params;
}


/**
 *
 *
 * @return unknown
 */
function get_uri_path() {
    return preg_replace('/^.*index.php/', '', $_SERVER['REQUEST_URI']);
}


/**
 *
 *
 * @return unknown
 */
function get_base_url() {
    return preg_replace('/\/index.php.*/', '', $_SERVER['REQUEST_URI']);
}


/**
 * we memoize (cache) the calculations to optimize. This might be defeating
 * the "data massaging approximation" point of the exercise, but it also makes the simple
 * use case 2000x faster.
 *
 * @param unknown $n
 * @return unknown
 */
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


/**
 *
 *
 * @return unknown
 */
function get_article() {
    return array(
        'title' => OUR_ARTICLE,
        'path' => ARTICLE_PATH,
    );
}


/**
 *
 */
function render_article() {
    $is_api = true;
    $article = get_article();
    include 'template.php';
}


/**
 *
 */
function render_editable_article() {
    $is_api = false;
    $article = get_article();
    include 'template.php';
}


/**
 *
 *
 * @param unknown $post_params
 */
function write_upload($post_params) {
    $json = json_encode($post_params);
    $filename = hash('sha256', $json . uniqid());
    $upload_meta = UPLOAD_DIR . "/${filename}.json";
    if (!file_put_contents($upload_meta, $json)) {
        error_log("Failed to write $upload_meta");
    }
    // write uploaded files
    foreach ($post_params as $key => $value) {
        if (is_array($value) && $value['tmp_name']) {
            $target_file = UPLOAD_DIR . "/" . $value['target'];
            if (move_uploaded_file($value['tmp_name'], $target_file)) {
                chmod($target_file, 0664);
            }
        }
    }
}
