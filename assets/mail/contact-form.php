<?php
// Contact Form Handler for TBH Business Services
// Set error reporting for debugging (remove in production)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Helper function to detect AJAX request
function is_ajax_request() {
    return isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get form data and sanitize
    $name = isset($_POST['cfName']) ? htmlspecialchars(trim($_POST['cfName'])) : '';
    $email = isset($_POST['cfEmail']) ? filter_var(trim($_POST['cfEmail']), FILTER_SANITIZE_EMAIL) : '';
    $phone = isset($_POST['cfPhone']) ? htmlspecialchars(trim($_POST['cfPhone'])) : '';
    $subject = isset($_POST['cfSubject']) ? htmlspecialchars(trim($_POST['cfSubject'])) : '';
    $message = isset($_POST['cfMessage']) ? htmlspecialchars(trim($_POST['cfMessage'])) : '';
    
    // Validation
    $errors = array();
    if (empty($name)) {
        $errors[] = "Name is required";
    }
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Valid email is required";
    }
    if (empty($phone)) {
        $errors[] = "Phone number is required";
    }
    if (empty($message)) {
        $errors[] = "Message is required";
    }
    // If no errors, send email
    if (empty($errors)) {
        $to = "Tbhservicesqa@gmail.com"; // Change this to your email
        $subject_line = "New Contact Form Submission - TBH Business Services";
        $subject_map = array(
            '0' => 'General Inquiry',
            '1' => 'Company Formation',
            '2' => 'PRO services', 
            '3' => 'Audit and Accounting',
            '4' => 'Corporate Bank Account Services',
            '5' => 'Transalation Services',
            '6' => 'Notary Services',
        );
        $subject_text = isset($subject_map[$subject]) ? $subject_map[$subject] : 'General Inquiry';
        $email_content = "\n        <html>\n        <head>\n            <title>New Contact Form Submission</title>\n        </head>\n        <body>\n            <h2>New Contact Form Submission</h2>\n            <p><strong>Name:</strong> {$name}</p>\n            <p><strong>Email:</strong> {$email}</p>\n            <p><strong>Phone:</strong> {$phone}</p>\n            <p><strong>Subject:</strong> {$subject_text}</p>\n            <p><strong>Message:</strong></p>\n            <p>" . nl2br($message) . "</p>\n            <hr>\n            <p><small>This email was sent from the TBH Business Services contact form.</small></p>\n        </body>\n        </html>\n        ";
        $headers = array();
        $headers[] = "MIME-Version: 1.0";
        $headers[] = "Content-Type: text/html; charset=UTF-8";
        $headers[] = "From: {$name} <{$email}>";
        $headers[] = "Reply-To: {$email}";
        $headers[] = "X-Mailer: PHP/" . phpversion();
        $mail_sent = mail($to, $subject_line, $email_content, implode("\r\n", $headers));
        if ($mail_sent) {
            $response = array(
                'status' => 'success',
                'message' => 'Thank you! Your message has been sent successfully. We will get back to you soon.'
            );
        } else {
            $response = array(
                'status' => 'error',
                'message' => 'Sorry, there was an error sending your message. Please try again later.'
            );
        }
    } else {
        $response = array(
            'status' => 'error',
            'message' => 'Please correct the following errors: ' . implode(', ', $errors)
        );
    }
    // Respond differently for AJAX and normal POST
    if (is_ajax_request()) {
        header('Content-Type: application/json');
        echo json_encode($response);
    } else {
        // Redirect to contact.html with status and message as query parameters
        $redirect_url = '/contact.html?status=' . urlencode($response['status']) . '&msg=' . urlencode($response['message']);
        header('Location: ' . $redirect_url);
    }
    exit;
} else {
    // Not a POST AJAX request: show a simple message or redirect
    header('Content-Type: text/html; charset=UTF-8');
    echo '<!DOCTYPE html><html><head><title>Contact Form</title></head><body style="font-family:sans-serif;text-align:center;padding:40px;">'
        . '<h2>This page is for form submissions only.</h2>'
        . '<p>Please use the <a href="/contact.html">contact form</a> to get in touch with us.</p>'
        . '</body></html>';
    exit;
}
