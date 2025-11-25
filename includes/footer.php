<footer class="footer">
    <div class="footer-container">
        <!-- ← PERBAIKAN: Hanya tentang Brew Bakery -->
        <div class="footer-section">
            <h3>🍞 Brew Bakery</h3>
            <p>Kami menjual roti dan pastry berkualitas tinggi dengan cita rasa istimewa. Diproduksi fresh setiap hari dengan bahan-bahan pilihan terbaik.</p>
        </div>

        <!-- ← PERBAIKAN: Kontak & Jam Buka -->
        <div class="footer-section">
            <h3> Hubungi Kami</h3>
            <ul>
                <li>📞 <a href="tel:+6281234567890">+62 812-3456-7890</a></li>
                <li>📧 <a href="mailto:info@brewbakery.com">info@brewbakery.com</a></li>
                <li>📍 Jl. Ketintang No.156, Ketintang</li>
                <li style="font-size: 0.85rem; opacity: 0.85;">Gayungan, Surabaya, Jawa Timur</li>
            </ul>
        </div>

        <!-- ← PERBAIKAN: Jam Buka Lebih Rapi -->
        <div class="footer-section">
            <h3> Jam Buka</h3>
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

        <!-- ← PERBAIKAN: Maps Lebih Kecil & Compact -->
        <div class="footer-section">


        <h3> Lokasi</h3>
            <div class="maps-wrapper">
                <iframe 
                    src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3958.123456789!2d112.71234567!3d-7.28765432!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x2dd7c8b5c6d7a89f%3A0x1234567890abcdef!2sJl.%20Ketintang%20No.156%2C%20Ketintang%2C%20Kec.%20Gayungan%2C%20Surabaya%2C%20Jawa%20Timur%2060231!5e0!3m2!1sid!2sid!4v1234567890123" 
                    width="100%" 
                    height="150" 
                    style="border:0; border-radius: 0.5rem;" 
                    allowfullscreen="" 
                    loading="lazy" 
                    referrerpolicy="no-referrer-when-downgrade">
                </iframe>
                <a href="https://www.google.com/maps/search/Jl.+Ketintang+No.156" 
                   target="_blank" 
                   class="maps-link">
                    🗺️ Buka Google Maps
                </a>
            </div>
        </div>

        <!-- ← PERBAIKAN: Media Sosial -->
        <div class="footer-section">
            <h3> Ikuti Kami</h3>
            <div class="social-links">
                <a href="https://www.facebook.com" target="_blank" title="Facebook" aria-label="Facebook">
                    <i class="fab fa-facebook"></i>
                </a>
                <a href="https://www.instagram.com" target="_blank" title="Instagram" aria-label="Instagram">
                    <i class="fab fa-instagram"></i>
                </a>
                <a href="https://wa.me/6281234567890" target="_blank" title="WhatsApp" aria-label="WhatsApp">
                    <i class="fab fa-whatsapp"></i>
                </a>
            </div>
        </div>
    </div>

    <div class="footer-bottom">
        <p>&copy; 2025 Brew Bakery. Semua hak dilindungi.</p>
    </div>
</footer>

<!-- ← Back to Top Button -->
<button id="backToTopBtn" title="Kembali ke atas" aria-label="Back to top">
    <i class="fas fa-arrow-up"></i>
</button>

<script>
    // Back to Top Button
    const backToTopBtn = document.getElementById('backToTopBtn');

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

    backToTopBtn.addEventListener('click', (e) => {
        e.preventDefault();
        if ('scrollBehavior' in document.documentElement.style) {
            window.scrollTo({
                top: 0,
                behavior: 'smooth'
            });
        } else {
            let scrollPos = window.pageYOffset;
            const timer = setInterval(() => {
                if (scrollPos <= 0) clearInterval(timer);
                scrollPos -= scrollPos / 8;
                window.scrollTo(0, scrollPos);
            }, 16);
        }
    });

    document.addEventListener('keydown', (e) => {
        if (e.key === 'Home' && backToTopBtn.classList.contains('show')) {
            backToTopBtn.click();
        }
    });
</script>

<style>
        :root {
            --primary: #8B6F47;
            --secondary: #D4A574;
            --accent: #F5E6D3;
            --gold: #D4A574;
            --honey: #6B4423;
            --text-dark: #2D2D2D;
            --text-light: #FFFFFF;
            --bg-light: #F5F1ED;
            --border: #E6CEB3;
        }

        .footer {
            background: linear-gradient(135deg, #8B6F47 0%, #6B4423 100%);
            color: white;
            padding: 2.5rem 0 1rem;
            margin-top: 4rem;
            border-top: 3px solid #D4A574;
        }

        .footer-container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 0 2rem;
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 2rem;
            margin-bottom: 2rem;
        }

        .footer-section h3 {
            color: #F5E6D3;
            margin-bottom: 1rem;
            font-size: 1rem;
            font-weight: 700;
        }

        .footer-section p {
            font-size: 0.9rem;
            line-height: 1.6;
            color: rgba(255, 255, 255, 0.85);
        }

        .footer-section ul {
            list-style: none;
            margin: 0;
            padding: 0;
        }

        .footer-section ul li {
            margin-bottom: 0.6rem;
            font-size: 0.9rem;
            color: rgba(255, 255, 255, 0.85);
        }

        .footer-section a {
            color: rgba(255, 255, 255, 0.85);
            text-decoration: none;
            transition: color 0.3s ease;
        }

        .footer-section a:hover {
            color: #F5E6D3;
        }

        /* Opening Hours */
        .opening-hours {
            background: rgba(255, 255, 255, 0.15);
            border-radius: 0.4rem;
            padding: 0.75rem;
            backdrop-filter: blur(8px);
        }

        .hours-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0.5rem 0;
            border-bottom: 1px solid rgba(255, 255, 255, 0.15);
            color: white;
            font-size: 0.85rem;
        }

        .hours-item:last-child {
            border-bottom: none;
        }

        .hours-item .day {
            font-weight: 600;
        }

        .hours-item .time {
            background: rgba(212, 165, 116, 0.3);
            padding: 0.25rem 0.6rem;
            border-radius: 2rem;
            font-weight: 700;
            font-size: 0.75rem;
            color: #F5E6D3;
        }

        /* Maps */
        .maps-wrapper {
            position: relative;
            border-radius: 0.4rem;
            overflow: hidden;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.2);
        }

        .maps-wrapper iframe {
            width: 100% !important;
            height: 150px !important;
            display: block;
        }

        .maps-link {
            display: inline-block;
            margin-top: 0.75rem;
            padding: 0.5rem 1rem;
            background: rgba(212, 165, 116, 0.3);
            color: #F5E6D3;
            border-radius: 0.4rem;
            font-weight: 600;
            font-size: 0.85rem;
            transition: all 0.3s ease;
            text-decoration: none;
        }

        .maps-link:hover {
            background: rgba(212, 165, 116, 0.5);
            transform: translateY(-2px);
        }

        /* Social Links */
        .social-links {
            display: flex;
            gap: 0.75rem;
            margin-top: 0.75rem;
        }

        .social-links a {
            width: 40px;
            height: 40px;
            background: rgba(212, 165, 116, 0.3);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.1rem;
            transition: all 0.3s ease;
            border: 1px solid rgba(255, 255, 255, 0.3);
            color: white;
        }

        .social-links a:hover {
            background: #F5E6D3;
            color: #8B6F47;
            transform: translateY(-2px);
            border-color: #F5E6D3;
        }

        .footer-bottom {
            text-align: center;
            padding-top: 1rem;
            border-top: 1px solid rgba(255, 255, 255, 0.15);
            color: rgba(255, 255, 255, 0.8);
            font-size: 0.85rem;
        }

        /* Back to Top Button */
        #backToTopBtn {
            position: fixed;
            bottom: 2rem;
            right: 2rem;
            width: 50px;
            height: 50px;
            background: linear-gradient(135deg, #D4A574 0%, #8B6F47 100%);
            color: white;
            border: none;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            cursor: pointer;
            z-index: 9999;
            box-shadow: 0 4px 12px rgba(139, 111, 71, 0.3);
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
            box-shadow: 0 6px 18px rgba(139, 111, 71, 0.4);
            background: linear-gradient(135deg, #8B6F47 0%, #6B4423 100%);
        }

        @media (max-width: 768px) {
            .footer-container {
                grid-template-columns: 1fr 1fr;
                gap: 1.5rem;
                padding: 0 1rem;
            }

            .footer-section h3 {
                font-size: 0.95rem;
            }

            .footer-section p {
                font-size: 0.85rem;
            }

            .maps-wrapper iframe {
                height: 120px !important;
            }

            #backToTopBtn {
                width: 45px;
                height: 45px;
                font-size: 1.2rem;
                bottom: 1.5rem;
                right: 1.5rem;
            }
        }

        @media (max-width: 480px) {
            .footer-container {
                grid-template-columns: 1fr;
                gap: 1.25rem;
            }

            .maps-wrapper iframe {
                height: 120px !important;
            }

            #backToTopBtn {
                width: 40px;
                height: 40px;
                font-size: 1rem;
                bottom: 1rem;
                right: 1rem;
            }
        }
    </style>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
