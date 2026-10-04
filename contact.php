<?php
require_once __DIR__ . '/php/db.php';
require_once __DIR__ . '/php/auth.php';

$pageTitle = "Contact Sevam";
$msgSubmitted = false;
$contactError = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim(filter_var($_POST['email'] ?? '', FILTER_SANITIZE_EMAIL));
    $phone = trim($_POST['phone'] ?? '');
    $subject = trim($_POST['subject'] ?? '');
    $message = trim($_POST['message'] ?? '');

    if (empty($name) || empty($email) || empty($subject) || empty($message)) {
        $contactError = 'Please fill in all required fields (Name, Email, Subject, Message).';
    } else {
        try {
            $stmt = $pdo->prepare("INSERT INTO contact_messages (name, email, phone, subject, message) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$name, $email, $phone, $subject, $message]);
            $msgId = $pdo->lastInsertId();
            $msgSubmitted = true;

            // Record action and sync contact message to Supabase
            require_once __DIR__ . '/php/supabase.php';
            supabase_record_action('contact_message_sent', [
                'name' => $name,
                'email' => $email,
                'phone' => $phone,
                'subject' => $subject
            ], 'contact', $msgId);
            supabase_sync_contact_message([
                'id' => $msgId,
                'name' => $name,
                'email' => $email,
                'phone' => $phone,
                'subject' => $subject,
                'message' => $message
            ]);
        } catch (Exception $e) {
            $contactError = 'Unable to send message: ' . $e->getMessage();
        }
    }
}

require_once __DIR__ . '/php/header.php';
?>

<div class="container" style="padding-top: 3.5rem; padding-bottom: 5rem;">
    <div style="max-width: 860px; margin: 0 auto;">
        <div class="section-header" style="text-align: left; margin-bottom: 2.5rem;">
            <span class="section-kicker">Get In Touch</span>
            <h1 style="font-size: 2.35rem; margin-bottom: 0.75rem;">Contact the Sevam Platform Team</h1>
            <p class="section-desc">
                Have questions about registering your kitchen or volunteer group? Send us a message and our field support team will respond promptly.
            </p>
        </div>

        <div class="cards-grid-2" style="align-items: start; gap: 2.5rem;">
            <div>
                <div class="form-card">
                    <?php if ($msgSubmitted): ?>
                        <div class="alert alert-success">
                            <span>Thank you! Your message has been received and stored in our system. An administrator will review it shortly.</span>
                        </div>
                    <?php endif; ?>

                    <?php if ($contactError): ?>
                        <div class="alert alert-danger">
                            <span><?= escape($contactError) ?></span>
                        </div>
                    <?php endif; ?>

                    <form action="/contact.php" method="POST">
                        <div class="form-group">
                            <label class="form-label" for="name">Your Name <span class="req">*</span></label>
                            <input type="text" id="name" name="name" class="form-input" placeholder="e.g. Ramesh Patel" required value="<?= escape($_POST['name'] ?? '') ?>">
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label class="form-label" for="email">Email Address <span class="req">*</span></label>
                                <input type="email" id="email" name="email" class="form-input" placeholder="name@example.com" required value="<?= escape($_POST['email'] ?? '') ?>">
                            </div>
                            <div class="form-group">
                                <label class="form-label" for="phone">Phone Number</label>
                                <input type="tel" id="phone" name="phone" class="form-input" placeholder="+91 98765 43210" value="<?= escape($_POST['phone'] ?? '') ?>">
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="form-label" for="subject">Subject <span class="req">*</span></label>
                            <input type="text" id="subject" name="subject" class="form-input" placeholder="e.g. Inquiry regarding hotel kitchen onboard" required value="<?= escape($_POST['subject'] ?? '') ?>">
                        </div>

                        <div class="form-group">
                            <label class="form-label" for="message">Message <span class="req">*</span></label>
                            <textarea id="message" name="message" class="form-textarea" rows="5" placeholder="Share your question or requirement..." required><?= escape($_POST['message'] ?? '') ?></textarea>
                        </div>

                        <button type="submit" class="btn btn-primary btn-block">
                            Send Message
                        </button>
                    </form>
                </div>
            </div>

            <div>
                <div class="card" style="margin-bottom: 1.5rem;">
                    <h3 style="font-size: 1.15rem; margin-bottom: 0.75rem;">Platform Operations</h3>
                    <p style="font-size: 0.9rem; margin-bottom: 1rem;">
                        Sevam operates as a non-commercial community initiative dedicated to eliminating urban food wastage.
                    </p>
                    <div style="font-size: 0.875rem; color: var(--color-text-main); display:flex; flex-direction:column; gap:0.5rem;">
                        <div><strong>Email:</strong> support@sevam.org</div>
                        <div><strong>Hours:</strong> 9:00 AM – 8:00 PM IST (Mon – Sat)</div>
                        <div><strong>Emergency Food Line:</strong> +91 1800-200-SEVAM</div>
                    </div>
                </div>

                <div class="card" style="background-color: var(--color-surface-subtle);">
                    <h4 style="font-size: 1rem; margin-bottom: 0.5rem;">Looking for quick onboarding?</h4>
                    <p style="font-size: 0.85rem; margin-bottom: 1rem;">
                        If you are ready to list surplus food or discover listings right now, you can create your account in two minutes.
                    </p>
                    <a href="/register.php" class="btn btn-secondary btn-sm">Start Registration →</a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/php/footer.php'; ?>
