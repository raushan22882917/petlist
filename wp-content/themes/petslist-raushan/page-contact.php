<?php
/**
 * Template Name: Contact Page
 *
 * @author  RadiusTheme / Custom
 * @since   1.0.0
 * @version 1.0.0
 */

// Run the loop so post data is available (title, etc.) but we render our own layout.
if ( have_posts() ) { the_post(); }

get_header();
?>

<link rel="stylesheet" href="<?php echo esc_url( get_template_directory_uri() ); ?>/assets/css/contact-page.css?v=3.0">

<main id="primary" class="content-area petslist-contact-page">

    <section class="contact-main-section">
        <div class="container">
            <div class="contact-layout-grid">

                <!-- ── Left: Map ── -->
                <div class="contact-map-col">
                    <div class="contact-map-wrap">
                        <iframe
                            src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d193595.15830869428!2d-74.11976397304603!3d40.69766374874431!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x89c24fa5d33f083b%3A0xc80b8f06e177fe62!2sNew%20York%2C%20NY%2C%20USA!5e0!3m2!1sen!2s!4v1702000000000!5m2!1sen!2s"
                            width="100%"
                            height="100%"
                            style="border:0; display:block;"
                            allowfullscreen=""
                            loading="lazy"
                            referrerpolicy="no-referrer-when-downgrade"
                            title="<?php esc_attr_e( 'Office Location Map', 'petslist' ); ?>">
                        </iframe>
                    </div>
                </div>

                <!-- ── Right: Contact Form ── -->
                <div class="contact-form-col">
                    <div class="contact-form-card">

                        <div class="form-card-header">
                            <div class="form-card-header-icon">
                                <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
                            </div>
                            <div>
                                <h2><?php esc_html_e( 'Send Us a Message', 'petslist' ); ?></h2>
                                <p><?php esc_html_e( "Fill out the form and we'll be in touch shortly.", 'petslist' ); ?></p>
                            </div>
                        </div>

                        <form class="petslist-contact-form" id="petslistContactForm" novalidate>

                            <div class="form-row-double">
                                <div class="petslist-form-group">
                                    <label for="cf_name"><?php esc_html_e( 'Your Name', 'petslist' ); ?> <span aria-hidden="true">*</span></label>
                                    <div class="input-icon-wrap">
                                        <svg class="input-icon" width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                                        <input type="text" id="cf_name" name="name" placeholder="<?php esc_attr_e( 'John Doe', 'petslist' ); ?>" required autocomplete="name">
                                    </div>
                                    <span class="field-error"><?php esc_html_e( 'Please enter your name.', 'petslist' ); ?></span>
                                </div>
                                <div class="petslist-form-group">
                                    <label for="cf_email"><?php esc_html_e( 'Email Address', 'petslist' ); ?> <span aria-hidden="true">*</span></label>
                                    <div class="input-icon-wrap">
                                        <svg class="input-icon" width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
                                        <input type="email" id="cf_email" name="email" placeholder="<?php esc_attr_e( 'john@example.com', 'petslist' ); ?>" required autocomplete="email">
                                    </div>
                                    <span class="field-error"><?php esc_html_e( 'Please enter a valid email.', 'petslist' ); ?></span>
                                </div>
                            </div>

                            <div class="form-row-double">
                                <div class="petslist-form-group">
                                    <label for="cf_phone"><?php esc_html_e( 'Phone Number', 'petslist' ); ?></label>
                                    <div class="input-icon-wrap">
                                        <svg class="input-icon" width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07A19.5 19.5 0 0 1 4.73 11.5a19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 3.64 2h3a2 2 0 0 1 2 1.72c.12.5.35 1.67.7 2.81a2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45c1.14.35 2.31.58 2.81.7A2 2 0 0 1 22 16.92z"/></svg>
                                        <input type="tel" id="cf_phone" name="phone" placeholder="<?php esc_attr_e( '+1 (555) 000-0000', 'petslist' ); ?>" autocomplete="tel">
                                    </div>
                                </div>
                                <div class="petslist-form-group">
                                    <label for="cf_subject"><?php esc_html_e( 'Subject', 'petslist' ); ?> <span aria-hidden="true">*</span></label>
                                    <div class="input-icon-wrap">
                                        <svg class="input-icon" width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="8" y1="6" x2="21" y2="6"/><line x1="8" y1="12" x2="21" y2="12"/><line x1="8" y1="18" x2="21" y2="18"/><line x1="3" y1="6" x2="3.01" y2="6"/><line x1="3" y1="12" x2="3.01" y2="12"/><line x1="3" y1="18" x2="3.01" y2="18"/></svg>
                                        <select id="cf_subject" name="subject" required>
                                            <option value=""><?php esc_html_e( 'Select a topic…', 'petslist' ); ?></option>
                                            <option value="general"><?php esc_html_e( 'General Inquiry', 'petslist' ); ?></option>
                                            <option value="adoption"><?php esc_html_e( 'Pet Adoption', 'petslist' ); ?></option>
                                            <option value="listing"><?php esc_html_e( 'List My Pet', 'petslist' ); ?></option>
                                            <option value="partnership"><?php esc_html_e( 'Partnership', 'petslist' ); ?></option>
                                            <option value="support"><?php esc_html_e( 'Technical Support', 'petslist' ); ?></option>
                                        </select>
                                    </div>
                                    <span class="field-error"><?php esc_html_e( 'Please select a subject.', 'petslist' ); ?></span>
                                </div>
                            </div>

                            <div class="petslist-form-group">
                                <label for="cf_message"><?php esc_html_e( 'Your Message', 'petslist' ); ?> <span aria-hidden="true">*</span></label>
                                <div class="input-icon-wrap textarea-wrap">
                                    <svg class="input-icon input-icon-top" width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
                                    <textarea id="cf_message" name="message" rows="5" placeholder="<?php esc_attr_e( 'Tell us how we can help…', 'petslist' ); ?>" required></textarea>
                                </div>
                                <span class="field-error"><?php esc_html_e( 'Please enter your message.', 'petslist' ); ?></span>
                            </div>

                            <div class="petslist-form-group form-checkbox-row">
                                <label class="checkbox-label">
                                    <input type="checkbox" name="privacy" required>
                                    <span class="checkbox-box"></span>
                                    <span><?php esc_html_e( 'I agree to the ', 'petslist' ); ?><a href="<?php echo esc_url( get_privacy_policy_url() ); ?>"><?php esc_html_e( 'Privacy Policy', 'petslist' ); ?></a></span>
                                </label>
                            </div>

                            <button type="submit" class="petslist-contact-submit" id="cfSubmitBtn">
                                <span class="btn-text">
                                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="22" y1="2" x2="11" y2="13"/><polygon points="22 2 15 22 11 13 2 9 22 2"/></svg>
                                    <?php esc_html_e( 'Send Message', 'petslist' ); ?>
                                </span>
                                <span class="btn-loader" aria-hidden="true"></span>
                            </button>

                            <div class="form-success-message" id="cfSuccessMsg" role="alert" aria-live="polite">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
                                <?php esc_html_e( 'Thank you! Your message has been sent.', 'petslist' ); ?>
                            </div>

                        </form>
                    </div>
                </div>

            </div><!-- .contact-layout-grid -->
        </div><!-- .container -->
    </section>

</main>

<style>
/* ── Contact page layout ── */
.petslist-contact-page .contact-main-section {
    padding: 60px 0;
}
.contact-layout-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 0;
    min-height: 580px;
    border-radius: 12px;
    overflow: hidden;
    box-shadow: 0 4px 32px rgba(0,0,0,.10);
}
.contact-map-col {
    position: relative;
}
.contact-map-wrap {
    position: absolute;
    inset: 0;
}
.contact-map-wrap iframe {
    width: 100%;
    height: 100%;
    min-height: 400px;
}
.contact-form-col {
    background: #fff;
}
.contact-form-card {
    padding: 44px 40px;
    height: 100%;
    box-sizing: border-box;
}
.form-card-header {
    display: flex;
    align-items: flex-start;
    gap: 14px;
    margin-bottom: 28px;
}
.form-card-header-icon {
    flex-shrink: 0;
    width: 48px;
    height: 48px;
    border-radius: 10px;
    background: var(--primary-color, #e8622a);
    color: #fff;
    display: flex;
    align-items: center;
    justify-content: center;
}
.form-card-header h2 {
    margin: 0 0 4px;
    font-size: 1.35rem;
    font-weight: 700;
    line-height: 1.3;
}
.form-card-header p {
    margin: 0;
    font-size: .9rem;
    color: #666;
}
/* Responsive */
@media (max-width: 900px) {
    .contact-layout-grid {
        grid-template-columns: 1fr;
        min-height: auto;
    }
    .contact-map-col {
        position: relative;
        min-height: 320px;
    }
    .contact-map-wrap {
        position: relative;
        inset: auto;
        height: 320px;
    }
    .contact-map-wrap iframe {
        height: 320px;
    }
    .contact-form-card {
        padding: 32px 24px;
    }
}
</style>

<script>
(function () {
    'use strict';

    const form = document.getElementById('petslistContactForm');
    if (!form) return;

    form.addEventListener('submit', function (e) {
        e.preventDefault();
        let valid = true;

        form.querySelectorAll('[required]').forEach(function (field) {
            const group = field.closest('.petslist-form-group');
            const empty = field.type === 'checkbox' ? !field.checked : !field.value.trim();
            if (group) group.classList.toggle('has-error', empty);
            if (empty) valid = false;
        });

        if (!valid) return;

        const btn = document.getElementById('cfSubmitBtn');
        btn.classList.add('loading');
        btn.disabled = true;

        // Replace setTimeout with real wp_ajax call as needed
        setTimeout(function () {
            btn.classList.remove('loading');
            btn.style.display = 'none';
            const msg = document.getElementById('cfSuccessMsg');
            if (msg) msg.classList.add('visible');
            form.reset();
        }, 1600);
    });

    form.querySelectorAll('input, select, textarea').forEach(function (field) {
        field.addEventListener('input', function () {
            const group = this.closest('.petslist-form-group');
            if (group && this.value.trim()) group.classList.remove('has-error');
        });
    });

    document.querySelectorAll('.input-icon-wrap input, .input-icon-wrap textarea, .input-icon-wrap select').forEach(function (el) {
        el.addEventListener('focus', function () { el.closest('.input-icon-wrap')?.classList.add('focused'); });
        el.addEventListener('blur',  function () { el.closest('.input-icon-wrap')?.classList.remove('focused'); });
    });
}());
</script>

<?php get_footer(); ?>
