<?php include 'header.php'; ?>

<main class="contact-page">
    <!-- Contact Hero -->
    <section class="contact-hero">
        <div class="container">
            <div class="contact-hero-content">
                <span class="badge-new">Get In Touch</span>
                <h1>We're Here to Help <br><span class="text-gradient">You Find Your Home</span></h1>
                <p>Have questions about a property or want to join our network? Our team of experts is ready to assist you across Pakistan.</p>
            </div>
        </div>
    </section>

    <!-- Contact Info Cards -->
    <section class="contact-info-section">
        <div class="container">
            <div class="contact-grid">
                <!-- Info Column -->
                <div class="contact-info-column">
                    <div class="info-card" data-aos="fade-up">
                        <div class="info-icon"><i class="fa-solid fa-location-dot"></i></div>
                        <div class="info-details">
                            <h3>Our Headquarters</h3>
                            <p>Suit 14, Business Center, Naya Nazimabad, Block A, Karachi, Pakistan.</p>
                        </div>
                    </div>

                    <div class="info-card" data-aos="fade-up" data-aos-delay="100">
                        <div class="info-icon"><i class="fa-solid fa-phone"></i></div>
                        <div class="info-details">
                            <h3>Call Us</h3>
                            <p><a href="tel:+923182923525">0318-2923525</a></p>
                            <p>Mon - Sat: 9:00 AM - 6:00 PM</p>
                        </div>
                    </div>

                    <div class="info-card" data-aos="fade-up" data-aos-delay="200">
                        <div class="info-icon"><i class="fa-solid fa-envelope"></i></div>
                        <div class="info-details">
                            <h3>Email Support</h3>
                            <p><a href="mailto:info@landsfy.com">info@landsfy.com</a></p>
                            <p>Our team usually responds within 24 hours.</p>
                        </div>
                    </div>

                    <div class="contact-socials">
                        <h4>Follow Our Journey</h4>
                        <div class="social-links">
                            <a href="#" class="social-icon fb"><i class="fa-brands fa-facebook-f"></i></a>
                            <a href="#" class="social-icon insta"><i class="fa-brands fa-instagram"></i></a>
                            <a href="#" class="social-icon ln"><i class="fa-brands fa-linkedin-in"></i></a>
                            <a href="#" class="social-icon x"><i class="fa-brands fa-x-twitter"></i></a>
                        </div>
                    </div>
                </div>

                <!-- Form Column -->
                <div class="contact-form-column">
                    <div class="form-wrapper">
                        <h2>Send Us a Message</h2>
                        <p>Fill out the form below and we'll get back to you shortly.</p>
                        
                        <form id="contactForm" class="premium-form">
                            <div class="form-row">
                                <div class="form-group">
                                    <label>Full Name</label>
                                    <input type="text" name="name" placeholder="Enter your name" required>
                                </div>
                                <div class="form-group">
                                    <label>Email Address</label>
                                    <input type="email" name="email" placeholder="Enter your email" required>
                                </div>
                            </div>

                            <div class="form-row">
                                <div class="form-group">
                                    <label>Phone Number</label>
                                    <input type="tel" name="phone" placeholder="Enter phone number">
                                </div>
                                <div class="form-group">
                                    <label>Subject</label>
                                    <input type="text" name="subject" placeholder="What is this regarding?">
                                </div>
                            </div>

                            <div class="form-group">
                                <label>Your Message</label>
                                <textarea name="message" rows="5" placeholder="Tell us how we can help..." required></textarea>
                            </div>

                            <button type="submit" class="btn-submit-premium">
                                <span>Send Message</span>
                                <i class="fa-solid fa-paper-plane"></i>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>

</main>

<style>
/* Contact Page Specific Styles */
.contact-page {
    background: #fdfdfd;
    padding-bottom: 80px;
}
.contact-hero {
    background: var(--grad-primary);
    color: white;
    padding: 100px 0;
    text-align: center;
}
.contact-hero h1 {
    font-size: 3.5rem;
    font-weight: 800;
    margin-bottom: 20px;
}
.contact-grid {
    display: grid;
    grid-template-columns: 1fr 1.5fr;
    gap: 60px;
    margin-top: -60px;
}
.info-card {
    background: white;
    padding: 30px;
    border-radius: 20px;
    display: flex;
    gap: 20px;
    margin-bottom: 20px;
    box-shadow: 0 10px 30px rgba(0,0,0,0.05);
    transition: transform 0.3s ease;
}
.info-card:hover { transform: translateY(-5px); }
.info-icon {
    width: 60px;
    height: 60px;
    background: rgba(var(--primary-rgb), 0.1);
    border-radius: 15px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.8rem;
    color: var(--primary);
}
.info-details h3 { font-size: 1.2rem; margin-bottom: 5px; color: #333; }
.info-details p, .info-details a { color: #666; font-size: 0.95rem; text-decoration: none; }

.contact-socials {
    margin-top: 40px;
}
.contact-socials h4 {
    font-size: 1.1rem;
    margin-bottom: 20px;
    color: #333;
}
.social-links {
    display: flex;
    gap: 15px;
}
.social-icon {
    width: 45px;
    height: 45px;
    background: white;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.2rem;
    color: #444;
    text-decoration: none;
    box-shadow: 0 5px 15px rgba(0,0,0,0.05);
    transition: all 0.3s ease;
    border: 1px solid #eee;
}
.social-icon:hover {
    transform: translateY(-5px);
    background: var(--primary);
    color: white;
    border-color: var(--primary);
    box-shadow: 0 10px 20px rgba(var(--primary-rgb), 0.2);
}

.form-wrapper {
    background: white;
    padding: 50px;
    border-radius: 30px;
    box-shadow: 0 20px 50px rgba(0,0,0,0.08);
}
.form-wrapper h2 { font-size: 2rem; margin-bottom: 10px; }
.form-wrapper p { color: #888; margin-bottom: 30px; }

.premium-form .form-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 20px;
    margin-bottom: 20px;
}
.form-group { margin-bottom: 20px; }
.form-group label { display: block; margin-bottom: 8px; font-weight: 600; font-size: 0.9rem; color: #444; }
.form-group input, .form-group textarea {
    width: 100%;
    padding: 15px;
    border: 1.5px solid #eee;
    border-radius: 12px;
    font-family: inherit;
    transition: all 0.3s ease;
}
.form-group input:focus, .form-group textarea:focus {
    border-color: var(--primary);
    outline: none;
    background: #fafafa;
}

.btn-submit-premium {
    width: 100%;
    padding: 18px;
    background: var(--grad-primary);
    color: white;
    border: none;
    border-radius: 15px;
    font-weight: 700;
    font-size: 1.1rem;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 10px;
    cursor: pointer;
    transition: all 0.3s ease;
}
.btn-submit-premium:hover {
    transform: scale(1.02);
    box-shadow: 0 10px 20px rgba(var(--primary-rgb), 0.3);
}

.map-container {
    margin-top: 80px;
    border-radius: 30px;
    overflow: hidden;
    box-shadow: 0 20px 40px rgba(0,0,0,0.1);
}

@media (max-width: 992px) {
    .contact-grid { grid-template-columns: 1fr; }
    .contact-hero h1 { font-size: 2.5rem; }
}
</style>

<?php include 'footer.php'; ?>
