<?php
require_once __DIR__ . '/php/db.php';
require_once __DIR__ . '/php/auth.php';

$pageTitle = "Register with Sevam";
$initialRole = ($_GET['role'] ?? '') === 'group' ? 'group' : 'provider';

require_once __DIR__ . '/php/header.php';
?>

<div class="container" style="padding-top: 3.5rem; padding-bottom: 5rem;">
    <div style="max-width: 680px; margin: 0 auto;">
        
        <div style="text-align: center; margin-bottom: 2rem;">
            <h1 style="font-size: 2.15rem; margin-bottom: 0.5rem;">Join the Sevam Network</h1>
            <p style="font-size: 1rem; color: var(--color-text-muted);">
                Choose your organization type to complete your tailored registration.
            </p>
        </div>

        <?php render_flash('register_error'); ?>

        <!-- Role Selector Toggle -->
        <div style="margin-bottom: 2rem; text-align: center;">
            <p style="font-size: 0.875rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; color: var(--color-text-muted); margin-bottom: 0.75rem;">
                Who are you?
            </p>
            <div style="display: inline-flex; background: var(--color-surface-subtle); padding: 4px; border-radius: var(--radius-sm); border: 1px solid var(--color-border); gap: 4px;">
                <button type="button" class="btn btn-sm role-select-btn <?= ($initialRole === 'provider') ? 'btn-primary' : 'btn-secondary' ?>" data-role="provider">
                    🍲 Food Provider (Kitchen / Restaurant)
                </button>
                <button type="button" class="btn btn-sm role-select-btn <?= ($initialRole === 'group') ? 'btn-primary' : 'btn-secondary' ?>" data-role="group">
                    🤝 Social Working Group (NGO / Volunteers)
                </button>
            </div>
        </div>

        <!-- FORM 1: FOOD PROVIDER REGISTRATION -->
        <div id="form-food-provider" class="form-card" style="display: <?= ($initialRole === 'provider') ? 'block' : 'none' ?>;">
            <div style="border-bottom: 1px solid var(--color-border); padding-bottom: 1rem; margin-bottom: 1.5rem;">
                <h3 style="font-size: 1.25rem;">Food Provider Account Details</h3>
                <p style="font-size: 0.875rem; color: var(--color-text-muted);">
                    For restaurants, hotels, banquet halls, messes, and commercial caterers.
                </p>
            </div>

            <form action="/php/register-process.php" method="POST" data-validate="register">
                <input type="hidden" name="role" value="provider">

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label" for="p_business_name">Business / Kitchen Name <span class="req">*</span></label>
                        <input type="text" id="p_business_name" name="business_name" class="form-input" placeholder="e.g. Grand Heritage Banquets" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="p_owner_name">Owner / Contact Person Name <span class="req">*</span></label>
                        <input type="text" id="p_owner_name" name="owner_name" class="form-input" placeholder="e.g. Rajesh Kumar" required>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label" for="p_email">Email Address <span class="req">*</span></label>
                        <input type="email" id="p_email" name="email" class="form-input" placeholder="kitchen@example.com" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="p_phone">Mobile Number <span class="req">*</span></label>
                        <input type="tel" id="p_phone" name="phone" class="form-input" placeholder="+91 98200 12345" required>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label" for="p_business_type">Organization / Business Type <span class="req">*</span></label>
                    <select id="p_business_type" name="business_type" class="form-select" required>
                        <option value="Restaurant">Restaurant / Cafe</option>
                        <option value="Hotel & Banquets">Hotel & Banquet Hall</option>
                        <option value="Catering Service">Outdoor Catering Service</option>
                        <option value="Hostel / College Mess">Hostel / Institutional Mess</option>
                        <option value="Cloud Kitchen">Cloud Kitchen</option>
                        <option value="Event Organizer">Event Organizer</option>
                        <option value="Other Commercial Kitchen">Other Commercial Kitchen</option>
                    </select>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label" for="fssai_status">FSSAI Safety Status <span class="req">*</span></label>
                        <select id="fssai_status" name="fssai_status" class="form-select" required>
                            <option value="Not Certified">Not Certified</option>
                            <option value="Certified" selected>Certified</option>
                        </select>
                    </div>
                    <div class="form-group" id="fssai_number_wrap">
                        <label class="form-label" for="fssai_number">FSSAI License / Registration No. <span class="req">*</span></label>
                        <input type="text" id="fssai_number" name="fssai_number" class="form-input" placeholder="14-digit FSSAI Number">
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label" for="p_address">Kitchen / Pickup Address <span class="req">*</span></label>
                    <textarea id="p_address" name="address" class="form-textarea" rows="2" placeholder="Street, building, landmark" required></textarea>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label" for="p_city">City <span class="req">*</span></label>
                        <input type="text" id="p_city" name="city" class="form-input" placeholder="e.g. Mumbai" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="p_state">State <span class="req">*</span></label>
                        <input type="text" id="p_state" name="state" class="form-input" placeholder="e.g. Maharashtra" required>
                    </div>
                </div>

                <div style="border-top: 1px solid var(--color-border); padding-top: 1.25rem; margin-top: 1rem; margin-bottom: 1.25rem;">
                    <h4 style="font-size: 0.95rem; margin-bottom: 1rem; color: var(--color-text-main);">Login Credentials</h4>
                    
                    <div class="form-group">
                        <label class="form-label" for="p_username">Username <span class="req">*</span></label>
                        <input type="text" id="p_username" name="username" class="form-input" placeholder="Choose a username" required>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label" for="p_password">Password <span class="req">*</span></label>
                            <input type="password" id="p_password" name="password" class="form-input" placeholder="Min. 6 characters" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label" for="p_confirm_password">Confirm Password <span class="req">*</span></label>
                            <input type="password" id="p_confirm_password" name="confirm_password" class="form-input" placeholder="Repeat password" required>
                        </div>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary btn-block btn-lg">
                    Create Food Provider Account
                </button>
            </form>
        </div>

        <!-- FORM 2: SOCIAL WORKING GROUP REGISTRATION -->
        <div id="form-social-group" class="form-card" style="display: <?= ($initialRole === 'group') ? 'block' : 'none' ?>;">
            <div style="border-bottom: 1px solid var(--color-border); padding-bottom: 1rem; margin-bottom: 1.5rem;">
                <h3 style="font-size: 1.25rem;">Social Working Group Account Details</h3>
                <p style="font-size: 0.875rem; color: var(--color-text-muted);">
                    For registered NGOs, volunteer networks, community clubs, and relief groups.
                </p>
            </div>

            <form action="/php/register-process.php" method="POST" data-validate="register">
                <input type="hidden" name="role" value="group">

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label" for="g_group_name">Organization / Group Name <span class="req">*</span></label>
                        <input type="text" id="g_group_name" name="group_name" class="form-input" placeholder="e.g. Care & Share Relief Mission" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="g_rep_name">Representative Name <span class="req">*</span></label>
                        <input type="text" id="g_rep_name" name="representative_name" class="form-input" placeholder="e.g. Sunita Kulkarni" required>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label" for="g_email">Email Address <span class="req">*</span></label>
                        <input type="email" id="g_email" name="email" class="form-input" placeholder="ngo@example.org" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="g_phone">Mobile Number <span class="req">*</span></label>
                        <input type="tel" id="g_phone" name="phone" class="form-input" placeholder="+91 98111 22334" required>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label" for="g_org_type">Organization Type <span class="req">*</span></label>
                    <select id="g_org_type" name="organization_type" class="form-select" required>
                        <option value="Registered NGO">Registered NGO</option>
                        <option value="Community Volunteer Group">Community Volunteer Group</option>
                        <option value="Charitable Trust">Charitable Trust</option>
                        <option value="Youth Welfare Club">Youth Welfare Club</option>
                        <option value="Homeless Shelter">Homeless Shelter</option>
                        <option value="Religious / Community Kitchen">Community Kitchen (Langar / Seva)</option>
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label" for="darpan_id">NGO / Darpan ID (If Applicable)</label>
                    <input type="text" id="darpan_id" name="darpan_id" class="form-input" placeholder="e.g. MH/2021/0129845">
                    <div class="form-hint">NGO Darpan registration adds a verified badge to your requests.</div>
                </div>

                <div class="form-group">
                    <label class="form-label" for="g_address">Office / Operational Address <span class="req">*</span></label>
                    <textarea id="g_address" name="address" class="form-textarea" rows="2" placeholder="Center location or dispatch point" required></textarea>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label" for="g_city">City <span class="req">*</span></label>
                        <input type="text" id="g_city" name="city" class="form-input" placeholder="e.g. Mumbai" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="g_state">State <span class="req">*</span></label>
                        <input type="text" id="g_state" name="state" class="form-input" placeholder="e.g. Maharashtra" required>
                    </div>
                </div>

                <div style="border-top: 1px solid var(--color-border); padding-top: 1.25rem; margin-top: 1rem; margin-bottom: 1.25rem;">
                    <h4 style="font-size: 0.95rem; margin-bottom: 1rem; color: var(--color-text-main);">Login Credentials</h4>
                    
                    <div class="form-group">
                        <label class="form-label" for="g_username">Username <span class="req">*</span></label>
                        <input type="text" id="g_username" name="username" class="form-input" placeholder="Choose a username" required>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label" for="g_password">Password <span class="req">*</span></label>
                            <input type="password" id="g_password" name="password" class="form-input" placeholder="Min. 6 characters" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label" for="g_confirm_password">Confirm Password <span class="req">*</span></label>
                            <input type="password" id="g_confirm_password" name="confirm_password" class="form-input" placeholder="Repeat password" required>
                        </div>
                    </div>
                </div>

                <button type="submit" class="btn btn-accent btn-block btn-lg">
                    Create Social Working Group Account
                </button>
            </form>
        </div>

        <div style="text-align: center; margin-top: 1.75rem; font-size: 0.9rem; color: var(--color-text-muted);">
            Already registered? <a href="/login.php" style="font-weight: 600; color: var(--color-primary);">Log in here</a>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/php/footer.php'; ?>
