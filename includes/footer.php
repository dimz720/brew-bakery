<footer class="footer">
    <div class="footer-container">
        <div class="footer-section">
            <h3>🍞 Brew Bakery</h3>
            <p>Kami menjual roti dan pastry berkualitas tinggi dengan cita rasa istimewa. Diproduksi fresh setiap hari dengan bahan-bahan pilihan terbaik untuk memberikan pengalaman terbaik bagi setiap pelanggan setia kami.</p>
        </div>
        <div class="footer-section">
            <h3>📚 Menu</h3>
            <ul>
                <li><a href="<?php echo BASE_URL; ?>">Beranda</a></li>
                <li><a href="<?php echo CUSTOMER_URL; ?>shop.php">Belanja</a></li>
                <li><a href="<?php echo CUSTOMER_URL; ?>articles.php">Artikel</a></li>
                <li><a href="<?php echo AUTH_URL; ?>login-customer.php">Login</a></li>
            </ul>
        </div>
        <div class="footer-section">
            <h3>☎️ Hubungi Kami</h3>
            <ul>
                <li>📞 +62 812-3456-7890</li>
                <li>📧 info@brewbakery.com</li>
                <li>📍 Jl. Bakery No. 123, Jakarta</li>
            </ul>
        </div>
        <div class="footer-section">
            <h3>🌐 Media Sosial</h3>
            <div class="social-links">
                <a href="https://www.facebook.com" target="_blank" title="Facebook">
                    <i class="fab fa-facebook"></i>
                </a>
                <a href="https://www.instagram.com" target="_blank" title="Instagram">
                    <i class="fab fa-instagram"></i>
                </a>
                <a href="https://wa.me/6281234567890" target="_blank" title="WhatsApp">
                    <i class="fab fa-whatsapp"></i>
                </a>
            </div>
        </div>
    </div>

    <div class="footer-bottom">
        <p>&copy; 2025 Brew Bakery. Semua hak dilindungi.</p>
    </div>
</footer>

<!-- ← PERBAIKAN: Back to Top Button - Z-index tinggi & smooth scroll -->
<button id="backToTopBtn" title="Kembali ke atas" aria-label="Back to top">
    <i class="fas fa-arrow-up"></i>
</button>

<script>
    // Back to Top Button - Optimized
    const backToTopBtn = document.getElementById('backToTopBtn');

    // Debounce scroll event untuk performance
    let ticking = false;
    window.addEventListener('scroll', () => {
        if (!ticking) {
            window.requestAnimationFrame(() => {
                if (window.pageYOffset > 300) {
                    backToTopBtn.classList.add('show');
                } else {
                    backToTopBtn.classList.remove('show');
                }
                ticking = false;
            });
            ticking = true;
        }
    });

    // Smooth scroll to top dengan fallback
    backToTopBtn.addEventListener('click', (e) => {
        e.preventDefault();
        
        // Cek jika browser support smooth scroll
        if ('scrollBehavior' in document.documentElement.style) {
            window.scrollTo({
                top: 0,
                behavior: 'smooth'
            });
        } else {
            // Fallback untuk browser lama
            let scrollPos = window.pageYOffset;
            const timer = setInterval(() => {
                if (scrollPos <= 0) clearInterval(timer);
                scrollPos -= scrollPos / 8;
                window.scrollTo(0, scrollPos);
            }, 16);
        }
    });

    // Keyboard support (tombol End untuk langsung ke atas)
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Home' && backToTopBtn.classList.contains('show')) {
            backToTopBtn.click();
        }
    });
</script>

<style>
    :root {
        /* ← PERBAIKAN: Bright & Warm Bakery Colors */
        --primary: #F4E4C1;
        --secondary: #E8D4B8;
        --accent: #FFF9F0;
        --gold: #D4A574;
        --honey: #C9915D;
        --text-dark: #2D2D2D;
        --text-light: #FFFFFF;
        --bg-light: #FFFBF7;
        --border: #F0E6D8;
    }

    .footer {
        background: linear-gradient(135deg, #F4E4C1 0%, #E8D4B8 100%);
        color: var(--text-dark);
        padding: 3rem 0 1rem;
        margin-top: 4rem;
        border-top: 3px solid var(--gold);
    }

    .footer-container {
        max-width: 1400px;
        margin: 0 auto;
        padding: 0 2rem;
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
        gap: 2.5rem;
        margin-bottom: 2.5rem;
    }

    .footer-section h3 {
        color: var(--honey);
        margin-bottom: 1.2rem;
        font-size: 1.1rem;
        font-weight: 700;
    }

    .footer-section p {
        font-size: 0.95rem;
        line-height: 1.7;
        color: rgba(45, 45, 45, 0.8);
    }

    .footer-section ul {
        list-style: none;
    }

    .footer-section ul li {
        margin-bottom: 0.75rem;
        font-size: 0.95rem;
    }

    .footer-section a {
        color: var(--text-dark);
        text-decoration: none;
        transition: all 0.3s ease;
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
    }

    .footer-section a:hover {
        color: var(--gold);
        transform: translateX(3px);
    }

    .social-links {
        display: flex;
        gap: 1rem;
        margin-top: 1rem;
    }

    .social-links a {
        width: 45px;
        height: 45px;
        background: rgba(212, 165, 116, 0.2);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.2rem;
        transition: all 0.3s ease;
        border: 1px solid rgba(212, 165, 116, 0.3);
        color: var(--honey);
    }

    .social-links a:hover {
        background: var(--gold);
        color: white;
        transform: translateY(-3px);
        border-color: var(--gold);
    }

    .footer-bottom {
        text-align: center;
        padding-top: 1.5rem;
        border-top: 1px solid rgba(0, 0, 0, 0.1);
        color: rgba(45, 45, 45, 0.7);
        font-size: 0.9rem;
    }

    @media (max-width: 768px) {
        .footer-container {
            grid-template-columns: 1fr;
            gap: 2rem;
            padding: 0 1rem;
        }

        .social-links {
            gap: 0.75rem;
        }

        .social-links a {
            width: 40px;
            height: 40px;
            font-size: 1rem;
        }
    }

    /* ← TAMBAHAN: Back to Top Button Styles */
    #backToTopBtn {
        position: fixed;
        bottom: 2rem;
        right: 2rem;
        width: 50px;
        height: 50px;
        background: var(--gold);
        color: var(--text-light);
        border: none;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.5rem;
        cursor: pointer;
        z-index: 1000;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
        transition: all 0.3s ease;
        opacity: 0;
        pointer-events: none;
    }

    #backToTopBtn.show {
        opacity: 1;
        pointer-events: auto;
    }

    #backToTopBtn:hover {
        transform: translateY(-3px);
        box-shadow: 0 6px 12px rgba(0, 0, 0, 0.3);
    }
</style>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
