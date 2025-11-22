<footer class="footer">
    <div class="container">
        <div class="row g-4">
            <!-- About Section -->
            <div class="col-lg-4 col-md-6">
                <h5 class="fw-bold mb-4" style="color: white;">
                    <i class="fas fa-store me-2"></i>Dagang.in
                </h5>
                <p style="opacity: 0.9; line-height: 1.8;">
                    Platform e-commerce yang menghubungkan UMKM lokal dengan pelanggan di seluruh Indonesia.
                    Bersama membangun ekonomi digital yang inklusif.
                </p>
                <div class="mt-4">
                    <a href="#" class="btn btn-outline-light btn-sm me-2 mb-2" style="border-radius: 50px;">
                        <i class="fab fa-facebook-f"></i>
                    </a>
                    <a href="#" class="btn btn-outline-light btn-sm me-2 mb-2" style="border-radius: 50px;">
                        <i class="fab fa-instagram"></i>
                    </a>
                    <a href="#" class="btn btn-outline-light btn-sm me-2 mb-2" style="border-radius: 50px;">
                        <i class="fab fa-twitter"></i>
                    </a>
                    <a href="#" class="btn btn-outline-light btn-sm mb-2" style="border-radius: 50px;">
                        <i class="fab fa-linkedin-in"></i>
                    </a>
                </div>
            </div>

            <!-- Quick Links -->
            <div class="col-lg-2 col-md-6">
                <h5 class="fw-bold mb-4" style="color: white;">Tautan Cepat</h5>
                <ul class="list-unstyled">
                    <li class="mb-2">
                        <a href="#home"
                            style="color: rgba(255,255,255,0.8); text-decoration: none; transition: all 0.3s;">
                            <i class="fas fa-chevron-right me-2" style="font-size: 0.8rem;"></i>Beranda
                        </a>
                    </li>
                    <li class="mb-2">
                        <a href="#products"
                            style="color: rgba(255,255,255,0.8); text-decoration: none; transition: all 0.3s;">
                            <i class="fas fa-chevron-right me-2" style="font-size: 0.8rem;"></i>Produk
                        </a>
                    </li>
                    <li class="mb-2">
                        <a href="#contact"
                            style="color: rgba(255,255,255,0.8); text-decoration: none; transition: all 0.3s;">
                            <i class="fas fa-chevron-right me-2" style="font-size: 0.8rem;"></i>Kontak
                        </a>
                    </li>
                    <li class="mb-2">
                        <a href="<?php echo BASE_URL; ?>/login.php"
                            style="color: rgba(255,255,255,0.8); text-decoration: none; transition: all 0.3s;">
                            <i class="fas fa-chevron-right me-2" style="font-size: 0.8rem;"></i>Login Admin
                        </a>
                    </li>
                </ul>
            </div>

            <!-- Categories -->
            <div class="col-lg-3 col-md-6">
                <h5 class="fw-bold mb-4" style="color: white;">Layanan Kami</h5>
                <ul class="list-unstyled">
                    <li class="mb-2" style="color: rgba(255,255,255,0.8);">
                        <i class="fas fa-check-circle me-2" style="color: #ffd460;"></i>Produk Original
                    </li>
                    <li class="mb-2" style="color: rgba(255,255,255,0.8);">
                        <i class="fas fa-check-circle me-2" style="color: #ffd460;"></i>Harga Terjangkau
                    </li>
                    <li class="mb-2" style="color: rgba(255,255,255,0.8);">
                        <i class="fas fa-check-circle me-2" style="color: #ffd460;"></i>Pengiriman Cepat
                    </li>
                    <li class="mb-2" style="color: rgba(255,255,255,0.8);">
                        <i class="fas fa-check-circle me-2" style="color: #ffd460;"></i>Customer Service 24/7
                    </li>
                </ul>
            </div>

            <!-- Contact Info -->
            <div class="col-lg-3 col-md-6">
                <h5 class="fw-bold mb-4" style="color: white;">Hubungi Kami</h5>
                <div class="mb-3">
                    <i class="fas fa-phone-alt me-2" style="color: #ffd460;"></i>
                    <span style="color: rgba(255,255,255,0.9);">082197771318</span>
                </div>
                <div class="mb-3">
                    <i class="fas fa-envelope me-2" style="color: #ffd460;"></i>
                    <span style="color: rgba(255,255,255,0.9); word-break: break-all;">
                        gujestokjondrywelay@gmail.com
                    </span>
                </div>
                <div class="mb-3">
                    <i class="fas fa-map-marker-alt me-2" style="color: #ffd460;"></i>
                    <span style="color: rgba(255,255,255,0.9);">UKIM, Makassar</span>
                </div>
            </div>
        </div>

        <hr style="border-color: rgba(255,255,255,0.1); margin: 3rem 0 1.5rem;">

        <div class="row">
            <div class="col-md-6 text-center text-md-start mb-3 mb-md-0">
                <p style="opacity: 0.8; margin: 0;">
                    &copy; <?php echo date('Y'); ?> <strong>Dagang.in</strong>. All Rights Reserved.
                </p>
            </div>
            <div class="col-md-6 text-center text-md-end">
                <p style="opacity: 0.8; margin: 0;">
                    Made with <i class="fas fa-heart" style="color: #ff6b6b;"></i> for Indonesian UMKM
                </p>
            </div>
        </div>
    </div>
</footer>

<!-- Scroll to Top Button -->
<button id="scrollToTop" class="btn btn-primary" style="position: fixed; bottom: 30px; right: 30px; z-index: 999; display: none; 
               width: 50px; height: 50px; border-radius: 50%; box-shadow: var(--shadow-xl);">
    <i class="fas fa-arrow-up"></i>
</button>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>

<!-- Dynamic Product Filter Script -->
<script src="../assets/js/filter-products.js"></script>

<script>
    // Scroll to Top functionality
    window.addEventListener('scroll', function () {
        const scrollBtn = document.getElementById('scrollToTop');
        if (window.pageYOffset > 300) {
            scrollBtn.style.display = 'block';
        } else {
            scrollBtn.style.display = 'none';
        }
    });

    document.getElementById('scrollToTop').addEventListener('click', function () {
        window.scrollTo({
            top: 0,
            behavior: 'smooth'
        });
    });

    // Smooth scrolling for anchor links
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            e.preventDefault();
            const target = document.querySelector(this.getAttribute('href'));
            if (target) {
                target.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            }
        });
    });

    // Navbar scroll effect
    let lastScroll = 0;
    const navbar = document.querySelector('.navbar');

    window.addEventListener('scroll', () => {
        const currentScroll = window.pageYOffset;

        if (currentScroll > 100) {
            navbar.classList.add('scrolled');
        } else {
            navbar.classList.remove('scrolled');
        }

        lastScroll = currentScroll;
    });

    // Add animation on scroll
    const observerOptions = {
        threshold: 0.1,
        rootMargin: '0px 0px -50px 0px'
    };

    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.style.opacity = '1';
                entry.target.style.transform = 'translateY(0)';
            }
        });
    }, observerOptions);

    document.querySelectorAll('.feature-card, .product-card, .stat-card').forEach(el => {
        el.style.opacity = '0';
        el.style.transform = 'translateY(30px)';
        el.style.transition = 'all 0.6s ease-out';
        observer.observe(el);
    });
</script>

<style>
    /* Footer link hover effect */
    .footer a:hover {
        color: #ffd460 !important;
        transform: translateX(5px);
    }

    .footer .btn-outline-light:hover {
        background: white;
        color: var(--primary-color) !important;
        transform: scale(1.1);
    }
</style>

</body>

</html>