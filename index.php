<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CRiSP - Steam n' Press</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@100..800&family=Poppins:wght@400;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div id="app">
        <header class="header">
            <div class="container">
                <div class="logo">
                    <a href="#home">
                        <img src="images/logo.svg" alt="CRiSP">
                    </a>
                </div>
                <nav class="nav">
                    <ul>
                        <li><a href="#home">HOME</a></li>
                        <li><a href="#services">SERVICES</a></li>
                        <li><a href="#rates">RATES</a></li>
                        <li><a href="#process">PROCESS</a></li>
                        <li><a href="#about">ABOUT US</a></li>
                        <li><a href="#footer">CONTACT</a></li>
                    </ul>
                    <a href="login/login.php" class="btn btn-outline">BOOK A PICKUP</a>
                </nav>
            </div>
        </header>

<!-- HERO PAGE -->
        <section class="hero" id="home">
            <div class="container hero-container">
                <div class="hero-content">
                    <h1>LOOK SHARP <br> EVERY SINGLE DAY!</h1>
                    <p class="description">
                        Professional steaming, pressing. and garment care delivered 
                        straight to your door. Enjoy fresh, crease‑free clothes without any of
                        the hassle.
                    </p>
                    <div class="hero-buttons">
                        <a href="login/login.php" class="btn btn-primary">BOOK A PICKUP</a>
                        <a href="#services" class="btn btn-secondary">VIEW SERVICES</a>
                    </div>
                </div>
                <div class="hero-image">
                    <figure>
                        <img src="images/clothes.png" alt="Garment care">
                    </figure>
                </div>
            </div>
        </section>

<!-- SERVICES PAGE -->
        <section class="services" id="services">
            <div class="container">
                <div class="services-title-wrapper">
                    <h2 class="services-title">SERVICES</h2>
                </div>
                <div class="services-grid">
                    <article class="service-card">
                        <div class="service-icon">
                            <img src="images/icons/shirt.svg" alt="Daily Wear">
                        </div>
                        <h3>DAILY WEAR</h3>
                        <p class="service-subtitle">Dress shirts, trousers, polo shirts, and casual tops</p>
                        <p class="service-description">
                            High-pressure steam finishing and crisp crease-setting designed for your everyday wardrobe. Eliminates deep wrinkles and restores a polished look without the heat damage of standard household irons.
                        </p>
                    </article>

                    <article class="service-card">
                        <div class="service-icon">
                            <img src="images/icons/suit.svg" alt="Formal Wear">
                        </div>
                        <h3>FORMAL WEAR</h3>
                        <p class="service-subtitle">Suits, tuxedos, blazers, Barongs, and gowns</p>
                        <p class="service-description">
                            Precision tension-steaming crafted to preserve the natural drape, shoulder structure, and canvas lining of tailored pieces. Leaves formal wear wrinkle-free and sharp for events and business meetings.
                        </p>
                    </article>

                    <article class="service-card">
                        <div class="service-icon">
                            <img src="images/icons/feather.svg" alt="Delicates">
                        </div>
                        <h3>DELICATES</h3>
                        <p class="service-subtitle">Silk, linen, cashmere, wool, and embellished items</p>
                        <p class="service-description">
                            Gentle, contact-free thermal steam treatment for heat-sensitive and fragile materials. Relaxes fabric fibers, lifts surface odors, and smooths wrinkles safely without leaving unwanted shine marks.
                        </p>
                    </article>

                    <article class="service-card">
                        <div class="service-icon">
                            <img src="images/icons/pillow.svg" alt="Household">
                        </div>
                        <h3>HOUSEHOLD</h3>
                        <p class="service-subtitle">Bedsheet sets, tablecloths, cloth napkins, and curtain panels</p>
                        <p class="service-description">
                            Large-format flatbed pressing that eliminates stubborn folds and creases from home textiles, delivering a smooth, hotel-grade finish for your dining and bed linens.
                        </p>
                    </article>
                </div>
            </div>
        </section>

<!-- RATES PAGE -->
        <section class="rates" id="rates">
            <div class="container">
                <h2 class="rates-title">RATES</h2>
                <div class="rates-grid">
                    <div class="rate-card rate-card-daily">
                        <h3>DAILY WEAR</h3>
                        <p class="rate-label">starts at</p>
                        <p class="rate-price">₱45</p>
                        <p class="rate-unit">/ pc</p>
                    </div>

                    <div class="rate-card rate-card-formal">
                        <h3>FORMAL WEAR</h3>
                        <p class="rate-label">starts at</p>
                        <p class="rate-price">₱180</p>
                        <p class="rate-unit">/ pc</p>
                    </div>

                    <div class="rate-card rate-card-delicates">
                        <h3>DELICATES</h3>
                        <p class="rate-label">starts at</p>
                        <p class="rate-price">₱85</p>
                        <p class="rate-unit">/ pc</p>
                    </div>

                    <div class="rate-card rate-card-household">
                        <h3>HOUSEHOLD</h3>
                        <p class="rate-label">starts at</p>
                        <p class="rate-price">₱25</p>
                        <p class="rate-unit">/ pc</p>
                    </div>
                </div>
            </div>
        </section>

<!-- PROCESS PAGE -->
        <section class="process" id="process">
            <div class="container">
                <h2 class="process-title">PROCESS</h2>
                <div class="process-card">
                    <div class="process-grid">
                        <article class="process-step">
                            <h3>BOOK FOR A PICKUP</h3>
                            <p class="step-description">
                                Schedule a time online and prepare your garments
                            </p>
                        </article>

                        <article class="process-step">
                            <h3>WE STEAM & PRESS</h3>
                            <p class="step-description">
                                We apply precision thermal care tailored to your fabric type.
                            </p>
                        </article>

                        <article class="process-step">
                            <h3>WE DELIVER FRESH</h3>
                            <p class="step-description">
                                We return your items on hangers, crisp and ready for immediate wear.
                            </p>
                        </article>
                    </div>

                    <div class="process-disclaimer">
                        <p>
                            <strong>Disclaimer:</strong> We provide dedicated pressing and finishing services only. Please ensure all garments are pre-cleaned before pickup. We are not liable for pre-existing stains, fabric wear, or damage from undisclosed delicate materials.
                        </p>
                    </div>
                </div>
            </div>
        </section>


<!-- ABOUT US PAGE -->
        <section class="about" id="about">
            <div class="container">
                <div class="about-card">
                    <h2 class="about-title">ABOUT US</h2>
                    <div class="about-grid">
                        <div class="about-text">
                            <p>
                                At CRISP Steam n' Press, we specialize in high-grade steam and press finishing designed to keep your wardrobe looking effortlessly sharp and polished. We provide dedicated thermal care that eliminates deep wrinkles, restores crisp creases, and preserves natural fabric structures without the harsh heat damage of traditional household irons. From daily workwear and structured formal suits, to delicate fine fabrics and household textiles, our precision service delivers a clean, hotel-grade finish so you always step out looking your best.
                            </p>
                            <p>
                                Driven by a commitment to garment longevity and modern convenience, we take the hassle out of fabric maintenance. Simply bring us your pre-cleaned items, and our team will handle the rest using tailored, temperature-controlled steam processes. Whether you need a single outfit refreshed for a last-minute event or your entire weekly rotation pressed and ready to wear, CRISP ensures premium quality and reliable turnaround every time.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

<!-- TESTIMONIALS -->
        <section class="testimonials" id="testimonials">
            <div class="container">
                <h2 class="testimonials-title">WHY CHOOSE CRiSP?</h2>
                <p class="testimonials-subtitle">Here is why our customers trust us with their daily wardrobe and special pieces</p>
                <div class="testimonials-grid">
                    <article class="testimonial-card">
                        <h2 class="testimonial-highlight">"Saved me hours on my work clothes"</h2>
                        <p class="testimonial-quote">
                            I used to dread pressing my suits every week. Booking a pickup with CRiSP was seamless, and my clothes came back on hangers, perfectly crisp, and ready to wear right out of the plastic.
                        </p>
                        <div class="testimonial-stars">
                            <img src="images/stars/5 stars.png" alt="5 stars">
                        </div>
                        <p class="testimonial-name"><strong>Tung S.</strong></p>
                    </article>

                    <article class="testimonial-card">
                        <h2 class="testimonial-highlight">"Hotel-quality finish right at home"</h2>
                        <p class="testimonial-quote">
                            The convenience of the pickup service is top-tier. My linen shirts usually get ruined with standard ironing, but their thermal steam care handled them perfectly without any heat damage.
                        </p>
                        <div class="testimonial-stars">
                            <img src="images/stars/5 stars.png" alt="5 stars">
                        </div>
                        <p class="testimonial-name"><strong>Skib D.</strong></p>
                    </article>

                    <article class="testimonial-card">
                        <h2 class="testimonial-highlight">"Reliable, sharp, and super easy"</h2>
                        <p class="testimonial-quote">
                            Extremely smooth booking process from start to finish. Dropped off my pre-cleaned items, picked a delivery window, and received pristine workwear right on schedule.
                        </p>
                        <div class="testimonial-stars">
                            <img src="images/stars/5 stars.png" alt="5 stars">
                        </div>
                        <p class="testimonial-name"><strong>Cris P.</strong></p>
                    </article>
                </div>
            </div>
        </section>
        
<!-- FOOTER -->
        <footer class="footer" id="footer">
            <div class="container">
                <div class="footer-grid">
                    <div class="footer-column footer-column-left">
                        <img src="images/logo white.svg" alt="CRiSP" class="footer-logo-icon">

                        <div class="footer-contact-cluster">
                            <p class="footer-contact-item">
                                <img src="images/icons/mail.svg" alt="Email" class="footer-icon">
                                crisp.snp@gmail.com
                            </p>
                            <p class="footer-contact-item">
                                <img src="images/icons/phone.svg" alt="Phone" class="footer-icon">
                                +63 993 270 8291
                            </p>
                            <p class="footer-contact-item">
                                <img src="images/icons/socials.svg" alt="Social" class="footer-social">
                                CRiSP Official
                            </p>
                        </div>
                    </div>

                    <div class="footer-quick-links">
                        <h3 class="footer-heading">QUICK LINKS</h3>
                        <ul class="footer-links">
                            <li><a href="#rates">RATES</a></li>
                            <li><a href="#about">ABOUT US</a></li>
                            <li><a href="#process">PROCESS</a></li>
                            <li><a href="#footer">CONTACT</a></li>
                        </ul>
                    </div>

                    <div class="footer-services">
                        <h3 class="footer-heading">SERVICES</h3>
                        <ul class="footer-links">
                            <li><a href="#services">DAILY WEAR</a></li>
                            <li><a href="#services">FORMAL WEAR</a></li>
                            <li><a href="#services">DELICATES</a></li>
                            <li><a href="#services">HOUSEHOLD</a></li>
                        </ul>
                    </div>
                </div>
            </div>
        </footer>
    </div>
</body>
</html>