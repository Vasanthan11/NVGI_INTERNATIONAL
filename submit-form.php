<?php
// submit-form.php

// 1. Only allow POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo "Method Not Allowed";
    exit;
}

// 2. Get and sanitize inputs
function clean_input($key)
{
    return isset($_POST[$key]) ? trim($_POST[$key]) : '';
}

$name          = clean_input('name');
$mobile        = clean_input('mobile');
$email         = clean_input('email');
$section       = clean_input('section');        // rental / sale / both / import_export etc.
$equipment     = clean_input('equipment_type'); // boom_lifts / scissor_lifts / telehandlers / other
$message       = clean_input('message');

// 3. Basic validation
$errors = [];

if ($name === '') {
    $errors[] = "Name is required.";
}

if ($mobile === '') {
    $errors[] = "Mobile number is required.";
}

// You can make email required if you want:
// if ($email === '') {
//     $errors[] = "Email is required.";
// }

if (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors[] = "Please enter a valid email address.";
}

// 4. If errors, show them and stop
if (!empty($errors)) {
?>
    <!DOCTYPE html>
    <html lang="en">

    <head>
        <meta charset="UTF-8">
        <title>NVGI – Enquiry Error</title>
        <style>
            body {
                font-family: Arial, sans-serif;
                background: #f5f7fb;
                margin: 0;
                padding: 0;
            }

            .wrapper {
                max-width: 600px;
                margin: 40px auto;
                background: #ffffff;
                border-radius: 10px;
                padding: 20px 24px;
                box-shadow: 0 10px 30px rgba(0, 0, 0, 0.05);
            }

            h1 {
                font-size: 1.4rem;
                margin-bottom: 0.5rem;
                color: #0D5EA6;
            }

            ul {
                margin-left: 1.2rem;
                color: #a10000;
            }

            a {
                color: #0D5EA6;
                text-decoration: none;
            }

            a:hover {
                text-decoration: underline;
            }

            .back-link {
                margin-top: 1rem;
                display: inline-block;
                font-size: 0.95rem;
            }
        </style>
    </head>

    <body>
        <div class="wrapper">
            <h1>There was a problem with your enquiry.</h1>
            <p>Please check the following:</p>
            <ul>
                <?php foreach ($errors as $error): ?>
                    <li><?php echo htmlspecialchars($error); ?></li>
                <?php endforeach; ?>
            </ul>
            <a href="javascript:history.back();" class="back-link">← Go back and fix the form</a>
        </div>
    </body>

    </html>
<?php
    exit;
}

// 5. Build the email
$to      = 'sales@nvgiuae.com'; // change if needed
$subject = 'New Website Enquiry - NVGI International DMCC';

$ip       = $_SERVER['REMOTE_ADDR'] ?? 'Unknown';
$datetime = date('Y-m-d H:i:s');

$bodyLines = [
    "You have received a new enquiry from the NVGI website.",
    "",
    "Name:           " . $name,
    "Mobile:         " . $mobile,
    "Email:          " . ($email !== '' ? $email : 'Not provided'),
    "Section:        " . ($section !== '' ? $section : 'Not specified'),
    "Equipment type: " . ($equipment !== '' ? $equipment : 'Not specified'),
    "",
    "Message / Requirements:",
    $message !== '' ? $message : '(No additional message provided)',
    "",
    "----",
    "Submitted on:   " . $datetime,
    "IP Address:     " . $ip,
];

$body = implode("\n", $bodyLines);

// 6. Email headers
$headers = "From: NVGI Website <no-reply@nvgi.com>\r\n";
if (!empty($email)) {
    // set reply-to if user provided email
    $headers .= "Reply-To: " . $email . "\r\n";
}
$headers .= "Content-Type: text/plain; charset=UTF-8\r\n";

// 7. Send the mail
$sent = mail($to, $subject, $body, $headers);

// 8. Show success / failure page
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>NVGI – Enquiry Received</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f5f7fb;
            margin: 0;
            padding: 0;
        }

        .wrapper {
            max-width: 600px;
            margin: 40px auto;
            background: #ffffff;
            border-radius: 10px;
            padding: 20px 24px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.05);
            text-align: left;
        }

        h1 {
            font-size: 1.4rem;
            margin-bottom: 0.5rem;
            color: #0D5EA6;
        }

        p {
            font-size: 0.95rem;
            color: #333333;
            line-height: 1.6;
        }

        .back-link {
            margin-top: 1.2rem;
            display: inline-block;
            font-size: 0.95rem;
            color: #0D5EA6;
            text-decoration: none;
        }

        .back-link:hover {
            text-decoration: underline;
        }
    </style>
</head>

<body>
    <div class="wrapper">
        <?php if ($sent): ?>
            <h1>Thank you for your enquiry.</h1>
            <p>
                Your details have been sent to <strong>NVGI International DMCC</strong>. Our team will review your
                requirement and get back to you as soon as possible.
            </p>
        <?php else: ?>
            <h1>We could not send your enquiry.</h1>
            <p>
                There was an issue while sending your message. Please try again later or contact us directly at
                <a href="mailto:sales@nvgiuae.com">sales@nvgiuae.com</a> or
                <a href="tel:+971507287749">+971 50 728 7749</a>.
            </p>
        <?php endif; ?>

        <a href="javascript:history.back();" class="back-link">← Go back to the website</a>
    </div>
</body>

</html>