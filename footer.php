    <!-- Footer -->
    <footer class="site-footer">
        <div class="container">
            <div class="footer-grid">

                <!-- Brand Column -->
                <div class="footer-brand">
                    <div class="footer-logo">
                        <img src="<?php echo $base_path; ?>includes/assets/images/logo.png" alt="Landsfy" onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                        <div class="footer-logo-text" style="display:none;">
                            <span style="font-size:22px; font-weight:800; color:var(--primary);">Landsfy</span>
                        </div>
                    </div>
                    <ul class="footer-contact-list">
                        <li>
                            <i class="fa-solid fa-house-chimney"></i>
                            <span>Suit 14, Business Center, Naya Nazimabad, Block A.</span>
                        </li>
                        <li>
                            <i class="fa-solid fa-phone"></i>
                            <span>0318-2923525</span>
                        </li>
                        <li>
                            <i class="fa-solid fa-envelope"></i>
                            <span>info@landsfy.com</span>
                        </li>
                    </ul>
                </div>

                <!-- About Column -->
                <div class="footer-col">
                    <h4 class="footer-heading">About</h4>
                    <ul class="footer-links">
                        <li><a href="<?php echo $base_path; ?>about">About us</a></li>
                        <li><a href="<?php echo $base_path; ?>contact">Contact us</a></li>
                        <li><a href="<?php echo $base_path; ?>terms-conditions">Terms &amp; Conditions</a></li>
                        <li><a href="<?php echo $base_path; ?>privacy-policy">Privacy Policy</a></li>
                    </ul>
                </div>

                <!-- More Information Column -->
                <div class="footer-col">
                    <h4 class="footer-heading">Quick Links</h4>
                    <ul class="footer-links">
                        <li><a href="<?php echo $base_path; ?>properties">All Properties</a></li>
                        <li><a href="<?php echo $base_path; ?>properties?purpose=sale">Buy Properties</a></li>
                        <li><a href="<?php echo $base_path; ?>properties?purpose=rent">Rent Properties</a></li>
                        <li><a href="<?php echo $base_path; ?>agencies">Agencies</a></li>
                        <li><a href="<?php echo $base_path; ?>agents">Agents</a></li>
                    </ul>
                </div>

                <!-- Newsletter Column -->
                <div class="footer-col">
                    <h4 class="footer-heading">Newsletter</h4>
                    <p class="footer-newsletter-text">Subscribe to our newsletter to get latest news and property updates.</p>
                    <div class="footer-newsletter-form">
                        <input type="email" placeholder="Your Email Address" class="footer-email-input">
                        <button class="footer-email-btn"><i class="fa-solid fa-paper-plane"></i></button>
                    </div>
                    <div class="footer-social">
                        <a href="#" class="social-icon"><i class="fa-brands fa-facebook-f"></i></a>
                        <a href="#" class="social-icon"><i class="fa-brands fa-instagram"></i></a>
                        <a href="#" class="social-icon"><i class="fa-brands fa-linkedin-in"></i></a>
                        <a href="#" class="social-icon"><i class="fa-brands fa-x-twitter"></i></a>
                    </div>
                </div>

            </div>
        </div>

        <!-- Footer Bottom Bar -->
        <div class="footer-bottom">
            <div class="container">
                <div class="footer-bottom-inner">
                    <p>&copy; <?= date('Y') ?> <strong>Landsfy</strong>. All Rights Reserved.</p>
                    <p>Designed &amp; Developed by <a href="https://amaanullah.com/" style="color:var(--primary); font-weight:700;">Amaanullah Khan</a></p>
                </div>
            </div>
        </div>
    </footer>

    <!-- Scroll To Top -->
    <div class="scroll-top" id="scrollTop"><i class="fa-solid fa-chevron-up"></i></div>

    <!-- Scripts -->
    <script src="<?php echo $base_path; ?>includes/assets/js/utils.js"></script>
    <script src="<?php echo $base_path; ?>includes/assets/js/script.js"></script>
    <script src="<?php echo $base_path; ?>includes/assets/js/premium-core.js"></script>
</body>
</html>
