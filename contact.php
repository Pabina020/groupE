<?php 
$errors = '';
$myemail = 'pabinashrestha1234@gmail.com'; // <-- Your email address

if(empty($_POST['name']) || empty($_POST['email']) || empty($_POST['message'])) {
    $errors .= "\n Error: all fields are required";
}

$name = strip_tags(trim($_POST['name'])); 
$email_address = filter_var(trim($_POST['email']), FILTER_SANITIZE_EMAIL); 
$message = strip_tags(trim($_POST['message'])); 

if (!filter_var($email_address, FILTER_VALIDATE_EMAIL)) {
    $errors .= "\n Error: Invalid email address";
}

if(empty($errors)) {
    $to = $myemail;
    $email_subject = "Contact form submission: $name";
    $email_body = "You have received a new message.\n\n".
                  "Here are the details:\n".
                  "Name: $name\n".
                  "Email: $email_address\n".
                  "Message:\n$message\n";

    $headers = "From: $myemail\n";
    $headers .= "Reply-To: $email_address";

    mail($to, $email_subject, $email_body, $headers);

    // Redirect to thank-you page
    header('Location: thank-you.html');
    exit;
} else {
    // During development, show errors
    echo nl2br($errors);
}
?>
