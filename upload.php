<?php
function handle_file_upload($file, $order_id, $product_id, $version)
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

    // Define the remote file path with the new filename
    $new_filename = $product_id . '-' . $version . '.jpeg';
    $remote_file = $remote_directory . $new_filename;

    // Upload the file
    if (ftp_put($ftp_conn, $remote_file, $file['tmp_name'], FTP_BINARY)) {
        $file_path = "https://lukpaluk.xyz/artworks/$order_id/$product_id/$version/$new_filename";
        echo "Successfully uploaded " . htmlspecialchars($file['name']) . " to $file_path<br>";
    } else {
        echo "Error uploading " . htmlspecialchars($file['name']) . " to $remote_file<br>";
    }

    // Close the FTP connection
    ftp_close($ftp_conn);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['file'])) {
    $order_id = filter_input(INPUT_POST, 'order_id');
    $product_id = filter_input(INPUT_POST, 'product_id');
    $version = filter_input(INPUT_POST, 'version');

    $file = $_FILES['file'];

    if ($file['error'] === UPLOAD_ERR_OK) {
        handle_file_upload($file, $order_id, $product_id, $version);
    } else {
        echo "Error uploading file: " . htmlspecialchars($file['name']) . "<br>";
    }
}
