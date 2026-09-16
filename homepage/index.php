<?php
session_start();
require_once '../database/config.php';

if (isset($_SESSION['user_id']) && ($_SESSION['role'] ?? '') === 'admin') {
    header("Location: ../admin/dashboard.php");
    exit();
}

$is_logged_in = isset($_SESSION['user_id']);
$user_name = $_SESSION['user_name'] ?? 'Guest';
$user_email = $_SESSION['email'] ?? '';
$user_initial = !empty($user_name) ? strtoupper(substr($user_name, 0, 1)) : 'G';

$pdo = getConnection();

$stmt = $pdo->query("SELECT service_id, service_name, description FROM services WHERE is_active = 1 ORDER BY service_id");
$services = $stmt->fetchAll();

$stmt = $pdo->query("SELECT rate_id, rate_name, rate_type, price_per_unit FROM rates WHERE is_active = 1 ORDER BY rate_id");
$rates = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CRiSP - Steam n' Press</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@100..800&family=Poppins:wght@400;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="../css/customer.css">
</head>
<body>
    <div id="app">
        <header class="header">
            <div class="container">
                <div class="logo"><a href="index.php"><img src="../assets/images/logo.svg" alt="CRiSP"></a></div>
                <nav class="nav">
                    <ul>
                        <li><a href="">HOME</a></li>
                        <li><a href="#services">SERVICES</a></li>
                        <li><a href="#rates">RATES</a></li>
                        <li><a href="#process">PROCESS</a></li>
                        <li><a href="#about">ABOUT US</a></li>
                        <li><a href="contact.php">CONTACT</a></li>
                    </ul>
                    <?php if ($is_logged_in): ?>
                        <div class="hamburger" id="hamburger" style="display:flex !important;"><span></span><span></span><span></span></div>
                    <?php else: ?>
                        <a href="../login/login.php" class="btn btn-outline">BOOK A PICKUP</a>
                        <div class="hamburger" id="hamburger" style="display:flex !important;"><span></span><span></span><span></span></div>
                    <?php endif; ?>
                </nav>
            </div>
        </header>

        <div class="nav-overlay" id="navOverlay"></div>
        <div class="mobile-menu" id="mobileMenu">
            <div class="user-info">
                <div class="avatar"><?php echo $user_initial; ?></div>
                <div class="user-name"><?php echo htmlspecialchars($user_name); ?></div>
                <div class="user-email"><?php echo htmlspecialchars($user_email); ?></div>
            </div>
            <ul class="menu-items">
                <?php if ($is_logged_in): ?>
                    <li><a href="../customer-dashboard/dashboard.php">Dashboard</a></li>
                    <li><a href="../customer-dashboard/book.php">Book a Service</a></li>
                    <li><a href="../customer-dashboard/orders.php">My Orders</a></li>
                    <li><a href="../customer-dashboard/reviews.php">Write a Review</a></li>
                    <li><a href="../logout.php" class="logout-link">Logout</a></li>
                <?php else: ?>
                    <li><a href="../login/login.php">Login</a></li>
                    <li><a href="../signup/signup.php">Sign Up</a></li>
                <?php endif; ?>
            </ul>
        </div>

        <section class="hero" id="home">
            <div class="container hero-container">
                <div class="hero-content">
                    <h1>LOOK SHARP <br> EVERY SINGLE DAY!</h1>
                    <p class="description">Professional steaming, pressing. and garment care delivered straight to your door. Enjoy fresh, crease‑free clothes without any of the hassle.</p>
                    <div class="hero-buttons">
                        <a href="<?php echo $is_logged_in ? 'customer-dashboard/book.php' : 'login/login.php'; ?>" class="btn btn-primary">BOOK A PICKUP</a>
                        <a href="#services" class="btn btn-secondary">VIEW SERVICES</a>
                    </div>
                </div>
                <div class="hero-image"><figure><img src="../assets/images/clothes.png" alt="Garment care"></figure></div>
            </div>
        </section>

        <section class="services" id="services">
            <div class="container">
                <h2 class="services-title">SERVICES</h2>
                <div class="services-grid">
                    <?php if (count($services) > 0): foreach ($services as $service): ?>
                        <article class="service-card">
                            <div class="service-icon">
                                <?php
                                $icon_map = ['Daily Wear'=>'shirt.svg','Formal Wear'=>'suit.svg','Delicates'=>'feather.svg','Household'=>'pillow.svg'];
                                $icon = $icon_map[$service['service_name']] ?? 'shirt.svg';
                                ?>
                                <img src="../assets/icons/<?php echo $icon; ?>" alt="<?php echo htmlspecialchars($service['service_name']); ?>">
                            </div>
                            <h3><?php echo strtoupper(htmlspecialchars($service['service_name'])); ?></h3>
                            <p class="service-subtitle"><?php echo htmlspecialchars($service['description']); ?></p>
                        </article>
                    <?php endforeach; else: ?>
                        <p style="color:var(--primary-cream); font-size:20px; text-align:center; width:100%;">No services available at the moment.</p>
                    <?php endif; ?>
                </div>
            </div>
        </section>

        <section class="rates" id="rates">
            <div class="container">
                <h2 class="rates-title">RATES</h2>
                <div class="rates-grid">
                    <?php if (count($rates) > 0): foreach ($rates as $rate): ?>
                        <div class="rate-card <?php
                            $card_class = '';
                            if (strpos($rate['rate_name'], 'Daily') !== false) $card_class = 'rate-card-daily';
                            elseif (strpos($rate['rate_name'], 'Formal') !== false) $card_class = 'rate-card-formal';
                            elseif (strpos($rate['rate_name'], 'Delicates') !== false) $card_class = 'rate-card-delicates';
                            elseif (strpos($rate['rate_name'], 'Household') !== false) $card_class = 'rate-card-household';
                            echo $card_class;
                        ?>">
                            <h3><?php echo strtoupper(str_replace(' Rate', '', htmlspecialchars($rate['rate_name']))); ?></h3>
                            <p class="rate-label">starts at</p>
                            <p class="rate-price">₱<?php echo number_format($rate['price_per_unit'], 0); ?></p>
                            <p class="rate-unit">/ <?php echo htmlspecialchars($rate['rate_type']); ?></p>
                        </div>
                    <?php endforeach; else: ?>
                        <p style="color:var(--primary-cream); font-size:20px; text-align:center; width:100%;">No rates available at the moment.</p>
                    <?php endif; ?>
                </div>
            </div>
        </section>

        <section class="process" id="process">
            <div class="container">
                <h2 class="process-title">PROCESS</h2>
                <div class="process-card">
                    <div class="process-grid">
                        <article class="process-step"><h3>BOOK FOR A PICKUP</h3><p class="step-description">Schedule a time online and prepare your garments</p></article>
                        <article class="process-step"><h3>WE STEAM & PRESS</h3><p class="step-description">We apply precision thermal care tailored to your fabric type.</p></article>
                        <article class="process-step"><h3>WE DELIVER FRESH</h3><p class="step-description">We return your items on hangers, crisp and ready for immediate wear.</p></article>
                    </div>
                    <div class="process-disclaimer">
                        <p><strong>Disclaimer:</strong> We provide dedicated pressing and finishing services only. Please ensure all garments are pre-cleaned before pickup. We are not liable for pre-existing stains, fabric wear, or damage from undisclosed delicate materials.</p>
                    </div>
                </div>
            </div>
        </section>

        <section class="about" id="about">
            <div class="container">
                <div class="about-card">
                    <h2 class="about-title">ABOUT US</h2>
                    <div class="about-grid">
                        <div class="about-text">
                            <p>At CRISP Steam n' Press, we specialize in high-grade steam and press finishing designed to keep your wardrobe looking effortlessly sharp and polished. We provide dedicated thermal care that eliminates deep wrinkles, restores crisp creases, and preserves natural fabric structures without the harsh heat damage of traditional household irons. From daily workwear and structured formal suits, to delicate fine fabrics and household textiles, our precision service delivers a clean, hotel-grade finish so you always step out looking your best.</p>
                            <p>Driven by a commitment to garment longevity and modern convenience, we take the hassle out of fabric maintenance. Simply bring us your pre-cleaned items, and our team will handle the rest using tailored, temperature-controlled steam processes. Whether you need a single outfit refreshed for a last-minute event or your entire weekly rotation pressed and ready to wear, CRISP ensures premium quality and reliable turnaround every time.</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

       <section class="testimonials" id="testimonials">
    <div class="container">
        <h2 class="testimonials-title">WHY CHOOSE CRiSP?</h2>
        <p class="testimonials-subtitle">Here is why our customers trust us with their daily wardrobe and special pieces</p>
        <?php
        $stmt = $pdo->query("SELECT r.rating, r.comment, r.created_at, u.name FROM reviews r JOIN users u ON r.user_id = u.user_id WHERE r.status = 'approved' ORDER BY r.created_at DESC LIMIT 10");
        $approved_reviews = $stmt->fetchAll();

        function renderStars($rating) {
            $rating = max(1, min(5, (int)$rating));
            $filename = ($rating == 1) ? 'star.png' : $rating . ' stars.png';
            return '<img src="../assets/stars/' . $filename . '" alt="' . $rating . ' stars" class="star-img">';
        }
        ?>

        <div class="testimonials-carousel-wrapper">
            <div class="testimonials-carousel" id="testimonialsCarousel">
                <div class="testimonials-track" id="testimonialsTrack">

                    <div class="testimonial-card" data-index="0">
                        <h2 class="testimonial-highlight">"Saved me hours on my work clothes"</h2>
                        <p class="testimonial-quote">I used to dread pressing my suits every week. Booking a pickup with CRiSP was seamless, and my clothes came back on hangers, perfectly crisp, and ready to wear right out of the plastic.</p>
                        <div class="testimonial-stars"><?php echo renderStars(5); ?></div>
                        <p class="testimonial-name"><strong>Tung S.</strong></p>
                    </div>

                    <div class="testimonial-card" data-index="1">
                        <h2 class="testimonial-highlight">"Hotel-quality finish right at home"</h2>
                        <p class="testimonial-quote">The convenience of the pickup service is top-tier. My linen shirts usually get ruined with standard ironing, but their thermal steam care handled them perfectly without any heat damage.</p>
                        <div class="testimonial-stars"><?php echo renderStars(5); ?></div>
                        <p class="testimonial-name"><strong>Skib D.</strong></p>
                    </div>

                    <div class="testimonial-card" data-index="2">
                        <h2 class="testimonial-highlight">"Reliable, sharp, and super easy"</h2>
                        <p class="testimonial-quote">Extremely smooth booking process from start to finish. Dropped off my pre-cleaned items, picked a delivery window, and received pristine workwear right on schedule.</p>
                        <div class="testimonial-stars"><?php echo renderStars(5); ?></div>
                        <p class="testimonial-name"><strong>Cris P.</strong></p>
                    </div>

                    <?php $loopIndex = 3; ?>
                    <?php foreach ($approved_reviews as $review): ?>
                        <div class="testimonial-card" data-index="<?php echo $loopIndex; ?>">
                            <h2 class="testimonial-highlight">"<?php echo htmlspecialchars(substr($review['comment'], 0, 60)) . (strlen($review['comment']) > 60 ? '...' : ''); ?>"</h2>
                            <p class="testimonial-quote"><?php echo htmlspecialchars($review['comment']); ?></p>
                            <div class="testimonial-stars"><?php echo renderStars($review['rating']); ?></div>
                            <p class="testimonial-name"><strong><?php echo htmlspecialchars($review['name']); ?></strong></p>
                        </div>
                        <?php $loopIndex++; ?>
                    <?php endforeach; ?>

                </div>
            </div>

            <button class="carousel-arrow carousel-arrow-prev" id="prevTestimonial" aria-label="Previous review">&#10094;</button>
            <button class="carousel-arrow carousel-arrow-next" id="nextTestimonial" aria-label="Next review">&#10095;</button>
            <div class="carousel-dots"></div>
        </div>
    </div>
</section>

        <footer class="footer" id="footer">
            <div class="container">
                <div class="footer-grid">
                    <div class="footer-column footer-column-left">
                        <img src="../assets/images/logo white.svg" alt="CRiSP" class="footer-logo-icon">
                        <div class="footer-contact-cluster">
                            <p class="footer-contact-item"><img src="../assets/icons/mail.svg" alt="Email" class="footer-icon"> crisp.snp@gmail.com</p>
                            <p class="footer-contact-item"><img src="../assets/icons/phone.svg" alt="Phone" class="footer-icon"> +63 993 270 8291</p>
                            <p class="footer-contact-item"><img src="../assets/icons/socials.svg" alt="Social" class="footer-social"> CRiSP Official</p>
                        </div>
                    </div>
                    <div class="footer-quick-links">
                        <h3 class="footer-heading">QUICK LINKS</h3>
                        <ul class="footer-links">
                            <li><a href="#rates">RATES</a></li>
                            <li><a href="#about">ABOUT US</a></li>
                            <li><a href="#process">PROCESS</a></li>
                            <li><a href="contact.php">CONTACT</a></li>
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
    <script src="../js/dashboard.js"></script>
    <script src="../js/script.js"></script>
    <script src="../js/logout.js"></script>
</body>
</html>