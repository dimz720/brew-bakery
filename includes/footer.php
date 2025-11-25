<footer class="footer">
    <div class="footer-container">
        
        <!-- Section 1: Brew Bakery -->
        <div class="footer-section">
            <h3>🍞 Brew Bakery</h3>
            <p>Kami menjual roti dan pastry berkualitas tinggi dengan cita rasa istimewa. 
               Diproduksi fresh setiap hari dengan bahan-bahan pilihan terbaik.</p>
        </div>

        <!-- Section 2: Kontak -->
        <div class="footer-section">
            <h3>Hubungi Kami</h3>
            <ul>
                <li>📞 <a href="tel:+6281234567890">+62 812-3456-7890</a></li>
                <li>📧 <a href="mailto:info@brewbakery.com">info@brewbakery.com</a></li>
                <li>📍 Jl. Ketintang No.156, Ketintang</li>
                <li style="font-size: 0.85rem; opacity: 0.85;">Gayungan, Surabaya, Jawa Timur</li>
            </ul>
        </div>

        <!-- Section 3: Jam Buka -->
        <div class="footer-section">
            <h3>Jam Buka</h3>
            <div class="opening-hours">
                <div class="hours-item">
                    <span class="day">Senin - Jumat</span>
                    <span class="time">09:00 - 20:00</span>
                </div>
                <div class="hours-item">
                    <span class="day">Sabtu - Minggu</span>
                    <span class="time">09:00 - 21:00</span>
                </div>
            </div>
        </div>

        <!-- Section 4: Maps -->
        <div class="footer-section map-section">
            <h3>Lokasi</h3>
            <div class="maps-wrapper">
                <iframe 
                    src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3958.123456789!2d112.71234567!3d-7.28765432!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x2dd7c8b5c6d7a89f%3A0x1234567890abcdef!2sJl.%20Ketintang%20No.156!5e0!3m2!1sid!2sid!4v1234567890123"
                    loading="lazy"
                    referrerpolicy="no-referrer-when-downgrade">
                </iframe>
            </div>
            <a href="https://www.google.com/maps/search/Jl.+Ketintang+No.156" 
               target="_blank" 
               class="maps-link">
                🗺️ Buka Google Maps
            </a>
        </div>

        <!-- Section 5: Sosial Media -->
        <div class="footer-section">
            <h3>Ikuti Kami</h3>
            <div class="social-links">
                <a href="https://www.facebook.com" target="_blank"><i class="fab fa-facebook"></i></a>
                <a href="https://www.instagram.com" target="_blank"><i class="fab fa-instagram"></i></a>
                <a href="https://wa.me/6281234567890" target="_blank"><i class="fab fa-whatsapp"></i></a>
            </div>
        </div>

    </div>

    <div class="footer-bottom">
        <p>&copy; 2025 Brew Bakery. Semua hak dilindungi.</p>
    </div>
</footer>

<!-- Back to Top -->
<button id="backToTopBtn" title="Kembali ke atas">
    <i class="fas fa-arrow-up"></i>
</button>

<!-- ===================== FOOTER SCRIPT ===================== -->
<script>
    const backToTopBtn = document.getElementById('backToTopBtn');

    window.addEventListener('scroll', () => {
        backToTopBtn.classList.toggle('show', window.pageYOffset > 300);
    });

    backToTopBtn.addEventListener('click', () => {
        window.scrollTo({ top: 0, behavior: 'smooth' });
    });
</script>

<!-- ===================== FOOTER STYLE ===================== -->
<style>
    :root {
        --primary: #8B6F47;
        --secondary: #D4A574;
        --accent: #F5E6D3;
        --text-light: #ffffff;
    }

    .footer {
        background: linear-gradient(135deg, #8B6F47 0%, #6B4423 100%);
        color: white;
        padding: 3rem 0 1.5rem;
    }

    /* GRID 5 KOLOM — RAPIH TIDAK ADA YANG TURUN */
    .footer-container {
        max-width: 1400px;
        margin: auto;
        padding: 0 2rem;
        display: grid;
        grid-template-columns: repeat(5, 1fr);
        align-items: start;
        gap: 2rem;
    }

    .footer-section {
        min-height: 230px;
    }

    .footer-section h3 {
        color: var(--accent);
        margin-bottom: 1rem;
        font-size: 1rem;
        font-weight: 700;
    }

    .footer-section p,
    .footer-section li {
        font-size: 0.9rem;
        opacity: 0.85;
    }

    /* === Hilangkan bullet list supaya rapi === */
    .footer-section ul {
        list-style: none;
        padding: 0;
        margin: 0;
    }

    .footer-section ul li {
        margin-bottom: 6px;
        display: flex;
        align-items: center;
    }

    .footer-section a:hover {
        color: var(--accent);
    }

    /* JAM BUKA */
    .opening-hours {
        background: rgba(255,255,255,0.15);
        padding: 0.8rem;
        border-radius: 0.4rem;
    }

    .hours-item {
        display: flex;
        justify-content: space-between;
        padding: 0.5rem 0;
        border-bottom: 1px solid rgba(255,255,255,0.2);
        font-size: 0.85rem;
    }

    .hours-item:last-child {
        border-bottom: none;
    }

    /* MAPS */
    .map-section .maps-wrapper {
        width: 100%;
        height: 150px;
        border-radius: 0.4rem;
        overflow: hidden;
        border: 1px solid rgba(255,255,255,0.3);
    }

    .maps-section iframe {
        width: 100%;
        height: 100%;
    }

    .maps-link {
        display: block;
        margin-top: 0.7rem;
        background: rgba(212,165,116,0.35);
        padding: 0.45rem 0.8rem;
        border-radius: 6px;
        text-align: center;
        font-size: 0.9rem;
        color: var(--accent);
        transition: .3s;
    }

    .maps-link:hover {
        background: rgba(212,165,116,0.6);
    }

    /* SOSMED */
    .social-links {
        display: flex;
        gap: 0.7rem;
    }

    .social-links a {
        width: 38px;
        height: 38px;
        border-radius: 50%;
        background: rgba(255,255,255,0.25);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.1rem;
        transition: 0.3s;
        color: white;
    }

    .social-links a:hover {
        background: var(--accent);
        color: var(--primary);
    }

    .footer-bottom {
        text-align: center;
        margin-top: 1rem;
        opacity: 0.8;
        font-size: 0.85rem;
    }

    /* Back To Top Button */
    #backToTopBtn {
        position: fixed;
        bottom: 2rem;
        right: 2rem;
        width: 48px;
        height: 48px;
        border-radius: 50%;
        border: none;
        background: linear-gradient(135deg, #D4A574 0%, #8B6F47 100%);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.4rem;
        color: white;
        opacity: 0;
        pointer-events: none;
        transition: 0.3s;
        z-index: 999;
    }

    #backToTopBtn.show {
        opacity: 1;
        pointer-events: auto;
    }

    /* RESPONSIVE GRID */
    @media(max-width: 1000px) {
        .footer-container {
            grid-template-columns: repeat(3, 1fr);
        }
    }
    @media(max-width: 768px) {
        .footer-container {
            grid-template-columns: repeat(2, 1fr);
        }
    }
    @media(max-width: 480px) {
        .footer-container {
            grid-template-columns: 1fr;
        }
        .footer-section {
            min-height: auto;
        }
    }
</style>

<!-- ICONS FONT AWESOME -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">