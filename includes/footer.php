    </main>
    <footer class="footer">
        <div class="container">
            <div class="footer-content">
                <div class="footer-section">
                    <h3><?php echo $company['company_name']; ?></h3>
                    <p><?php echo $company['company_about']; ?></p>
                </div>
                <div class="footer-section">
                    <h3>Quick Links</h3>
                    <ul>
                        <li><a href="about.php">About Us</a></li>
                        <li><a href="contact.php">Contact</a></li>
                        <li><a href="#">Privacy Policy</a></li>
                        <li><a href="#">Terms & Conditions</a></li>
                    </ul>
                </div>
                <div class="footer-section">
                    <h3>Contact Info</h3>
                    <p><i class="fas fa-phone"></i> <?php echo $company['company_phone']; ?></p>
                    <p><i class="fas fa-envelope"></i> <?php echo $company['company_email']; ?></p>
                    <p><i class="fas fa-map-marker-alt"></i> <?php echo $company['company_address']; ?></p>
                </div>
            </div>
            <div class="footer-bottom">
                <p>&copy; 2024 <?php echo $company['company_name']; ?>. All rights reserved.</p>
            </div>
        </div>
    </footer>
    <script src="assets/js/script.js"></script>
</body>
</html>