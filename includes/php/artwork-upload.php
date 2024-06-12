<?php
if ($_FILES['file']['error'] === UPLOAD_ERR_OK) {
    $ftp_server = '107.181.244.114';
    $ftp_user_name = 'lukpaluk';
    $ftp_user_pass = 'SK@8Ek9mZam45;';
    $destination_dir = '/public_html/product-artworks/';
    $public_url_base = 'https://lukpaluk.xyz/product-artworks/';

    // Connect to FTP server
    $ftp_conn = ftp_connect($ftp_server) or die("Could not connect to $ftp_server");
    $login = ftp_login($ftp_conn, $ftp_user_name, $ftp_user_pass);

    if (!$login) {
        echo json_encode(['success' => false, 'message' => 'FTP login failed']);
        exit;
    }

    // Generate a unique file name
    $file = $_FILES['file']['tmp_name'];
    $file_name = basename($_FILES['file']['name']);
    $file_ext = pathinfo($file_name, PATHINFO_EXTENSION);
    $file_base_name = pathinfo($file_name, PATHINFO_FILENAME);

    // Function to sanitize the file name
    function sanitize_file_name($file_name)
    {
        $file_name = preg_replace('/[^A-Za-z0-9\-]/', '_', $file_name); // Replace special characters with underscores
        $file_name = preg_replace('/_+/', '_', $file_name); // Replace multiple underscores with a single underscore
        return $file_name;
    }

    $file_base_name = sanitize_file_name($file_base_name);

    // Append a random string to the file name
    $unique_file_name = $file_base_name . '-' . uniqid() . '.' . $file_ext;
    $destination_file = $destination_dir . $unique_file_name;

    if (ftp_put($ftp_conn, $destination_file, $file, FTP_BINARY)) {
        $public_url = $public_url_base . $unique_file_name;
        echo json_encode(['success' => true, 'file_path' => $public_url]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to upload file']);
    }

    // Close the FTP connection
    ftp_close($ftp_conn);
} else {
    echo json_encode(['success' => false, 'message' => 'File upload error']);
}