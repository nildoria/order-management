<?php
// Load WordPress environment
require_once (dirname(__FILE__) . '/../../../wp-load.php');

function handle_file_upload($file, $order_id, $product_id, $version, $post_id)
{
    // FTP server details
    $ftp_server = '107.181.244.114';
    $ftp_user_name = 'lukpaluk';
    $ftp_user_pass = 'SK@8Ek9mZam45;';

    // Connect to FTP server
    $ftp_conn = ftp_connect($ftp_server) or die("Could not connect to $ftp_server");

    // Login to FTP server
    $login = ftp_login($ftp_conn, $ftp_user_name, $ftp_user_pass);
    if (!$login) {
        ftp_close($ftp_conn);
        die("Could not log in to FTP server");
    }

    // Enable passive mode
    ftp_pasv($ftp_conn, true);

    // Define the directory structure
    $remote_directory = "/public_html/artworks/$order_id/$product_id/$version/";

    // Check if directory exists, if not, create it
    if (!@ftp_chdir($ftp_conn, $remote_directory)) {
        $parts = explode('/', $remote_directory);
        $current_dir = '';
        foreach ($parts as $part) {
            if (empty($part))
                continue;
            $current_dir .= '/' . $part;
            if (!@ftp_chdir($ftp_conn, $current_dir)) {
                ftp_mkdir($ftp_conn, $current_dir);
            }
        }
        ftp_chdir($ftp_conn, $remote_directory);
    }

    // Generate a unique ID for the file
    $unique_id = uniqid();

    // Define the remote file path with the new filename
    $new_filename = $product_id . '-' . $version . '-' . $unique_id . '.jpeg';
    $remote_file = $remote_directory . $new_filename;

    // Upload the file
    if (ftp_put($ftp_conn, $remote_file, $file['tmp_name'], FTP_BINARY)) {
        $file_path = "https://lukpaluk.xyz/artworks/$order_id/$product_id/$version/$new_filename";
        error_log("file_path: $file_path");

        if (!empty($version) && !empty($post_id)) {
            $version_count = ml_extract_number($version);
            update_post_meta($post_id, '_mockup_count', $version_count);
            error_log('Mockup count saved: ' . $version_count);
        }

        return array(
            'success' => true,
            'message' => "Successfully uploaded " . htmlspecialchars($file['name']) . " to $file_path",
            'file_path' => $file_path
        );
    } else {
        return array(
            'success' => false,
            'message' => "Error uploading " . htmlspecialchars($file['name']) . " to $remote_file"
        );
    }

    // Close the FTP connection
    ftp_close($ftp_conn);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['file'])) {
    $order_id = filter_input(INPUT_POST, 'order_id', FILTER_VALIDATE_INT);
    $product_id = filter_input(INPUT_POST, 'product_id', FILTER_VALIDATE_INT);
    $version = ml_sanitize_string(filter_input(INPUT_POST, 'version', FILTER_SANITIZE_FULL_SPECIAL_CHARS));
    $post_id = ml_sanitize_string(filter_input(INPUT_POST, 'post_id', FILTER_SANITIZE_FULL_SPECIAL_CHARS));

    error_log("order_id: $order_id, product_id: $product_id, version: $version, post_id: $post_id");

    $files = $_FILES['file'];

    $responses = array();

    foreach ($files['name'] as $index => $name) {
        if ($files['error'][$index] === UPLOAD_ERR_OK) {
            $file = array(
                'name' => $files['name'][$index],
                'type' => $files['type'][$index],
                'tmp_name' => $files['tmp_name'][$index],
                'error' => $files['error'][$index],
                'size' => $files['size'][$index]
            );
            $responses[] = handle_file_upload($file, $order_id, $product_id, $version, $post_id);
        } else {
            $responses[] = array(
                'success' => false,
                'message' => "Error uploading file: " . htmlspecialchars($name)
            );
        }
    }

    echo json_encode($responses);
}

function ml_extract_number($string)
{
    if (preg_match('/\d+/', $string, $matches)) {
        return $matches[0];
    }
    return null;
}

/**
 * Sanitizes string values.
 *
 * @param string $string The string being sanitized.
 *
 * @return string $string The sanitized version of the string.
 */
function ml_sanitize_string($string)
{
    // Replace HTML tags and entities with their plain text equivalents
    $string = htmlspecialchars_decode($string, ENT_QUOTES);

    // Remove any remaining HTML tags
    $string = strip_tags($string);

    return $string;
}
