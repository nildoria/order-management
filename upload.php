<?php
// Load WordPress environment
require_once(dirname(__FILE__) . '/../../../wp-load.php');
function connect_to_ftp($ftp_server, $ftp_user_name, $ftp_user_pass, $max_retries = 3)
{
    $retry_count = 0;
    $ftp_conn = false;

    while ($retry_count < $max_retries) {
        // Attempt to connect
        $ftp_conn = @ftp_connect($ftp_server, 21, 30); // Timeout of 30 seconds
        if ($ftp_conn) {
            // Attempt to login
            $login = @ftp_login($ftp_conn, $ftp_user_name, $ftp_user_pass);
            if ($login) {
                // Enable passive mode
                ftp_pasv($ftp_conn, true);
                // error_log("New FTP connection established.");
                return $ftp_conn; // Success
            } else {
                error_log("FTP login failed.");
                ftp_close($ftp_conn); // Close connection if login fails
            }
        } else {
            error_log("Could not connect to FTP server.");
        }

        // Retry after a short delay
        sleep(2);
        $retry_count++;
    }

    return false; // Failed after all retries
}

function handle_file_upload($file, $order_id, $product_id, $version, $post_id, $ftp_conn)
{
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
                if (!@ftp_mkdir($ftp_conn, $current_dir)) {
                    error_log("Failed to create directory: $current_dir");
                    return array(
                        'success' => false,
                        'message' => "Failed to create directory: $current_dir"
                    );
                }
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
        error_log("File uploaded successfully: $file_path");

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
        error_log("Failed to upload file to FTP: $remote_file");
        return array(
            'success' => false,
            'message' => "Error uploading " . htmlspecialchars($file['name']) . " to $remote_file"
        );
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['file'])) {
    $order_id = filter_input(INPUT_POST, 'order_id', FILTER_VALIDATE_INT);
    $product_id = filter_input(INPUT_POST, 'product_id', FILTER_VALIDATE_INT);
    $version = ml_sanitize_string(filter_input(INPUT_POST, 'version', FILTER_SANITIZE_FULL_SPECIAL_CHARS));
    $post_id = ml_sanitize_string(filter_input(INPUT_POST, 'post_id', FILTER_SANITIZE_FULL_SPECIAL_CHARS));

    error_log("Received POST request: order_id: $order_id, product_id: $product_id, version: $version, post_id: $post_id");

    $files = $_FILES['file'];

    // FTP server details
    $ftp_server = '107.181.244.114';
    $ftp_user_name = 'lukpaluk';
    $ftp_user_pass = 'SK@8Ek9mZam45;';
    $max_retries = 3;

    // Instead of storing the FTP connection, store the FTP credentials
    $ftp_credentials = get_transient('ftp_credentials');

    if (!$ftp_credentials) {
        error_log("No FTP credentials in transient, reconnecting.");
        // Store FTP credentials in a transient (to be used for reconnection)
        $ftp_credentials = array(
            'ftp_server' => $ftp_server,
            'ftp_user_name' => $ftp_user_name,
            'ftp_user_pass' => $ftp_user_pass
        );
        set_transient('ftp_credentials', $ftp_credentials, 10 * MINUTE_IN_SECONDS);
    } else {
        error_log("Reusing stored FTP credentials from transient.");
        // Use the stored FTP credentials
        $ftp_server = $ftp_credentials['ftp_server'];
        $ftp_user_name = $ftp_credentials['ftp_user_name'];
        $ftp_user_pass = $ftp_credentials['ftp_user_pass'];
    }

    // Reconnect to FTP server using credentials
    $ftp_conn = connect_to_ftp($ftp_server, $ftp_user_name, $ftp_user_pass, $max_retries);

    if (!$ftp_conn) {
        error_log("Could not connect to FTP server after retries, Please refresh the page and try again.");
        echo json_encode(array(
            'success' => false,
            'message' => "Could not connect to FTP server, Please refresh the page and try again."
        ));
        exit;
    }

    $responses = array();

    // Handle multiple file uploads using the FTP connection
    foreach ($files['name'] as $index => $name) {
        if ($files['error'][$index] === UPLOAD_ERR_OK) {
            $file = array(
                'name' => $files['name'][$index],
                'type' => $files['type'][$index],
                'tmp_name' => $files['tmp_name'][$index],
                'error' => $files['error'][$index],
                'size' => $files['size'][$index]
            );
            $responses[] = handle_file_upload($file, $order_id, $product_id, $version, $post_id, $ftp_conn);
        } else {
            error_log("Error uploading file: " . htmlspecialchars($name) . " Error code: " . $files['error'][$index]);
            $responses[] = array(
                'success' => false,
                'message' => "Error uploading file: " . htmlspecialchars($name)
            );
        }
    }

    // Close the FTP connection if all files are done
    ftp_close($ftp_conn);
    // error_log("FTP connection closed.");

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