<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1"/>
  <title>MyPOS | Smart POS Software</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet"/>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet"/>
  <style>
    body { font-family: 'Inter', sans-serif; }
    .navbar { background: rgba(255,255,255,0.95); box-shadow: 0 2px 8px  rgba(0,0,0,0.05);  font-weight: 700;  }
    .navbar-brand { font-size: 1.8rem; font-weight: 700; color: black !important; }
    .carousel-caption { background: rgba(0,0,0,0.5); padding: 20px; border-radius: 10px; }
    .feature-icon { font-size: 40px; color: #007bff; margin-bottom: 10px; }
    .footer { background: #f8f9fa; padding: 20px 0; font-size: 14px; text-align: center; }
  </style>
</head>
<body>
  <!-- Navbar -->
  <nav class="navbar navbar-expand-lg navbar-light sticky-top">
    <div class="container">
      <a class="navbar-brand" href="#">DREAM POS</a>
      <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
        <span class="navbar-toggler-icon"></span>
      </button>
      <div class="collapse navbar-collapse justify-content-end" id="navbarNav">
        <ul class="navbar-nav">
          <li class="nav-item"><a class="nav-link active" href="#">Home</a></li>
          <li class="nav-item"><a class="nav-link" href="#">Features</a></li>
          <li class="nav-item"><a class="nav-link" href="#">Pricing</a></li>
          <li class="nav-item"><a class="btn btn-dark" href="login.php">Login</a></li>
        </ul>
      </div>
    </div>
  </nav>

  <!-- Carousel -->
  <div id="heroCarousel" class="carousel slide" data-bs-ride="carousel" data-bs-interval="2000">
    <div class="carousel-indicators">
      <button type="button" data-bs-target="#heroCarousel" data-bs-slide-to="0" class="active" aria-current="true" aria-label="Slide 1"></button>
      <button type="button" data-bs-target="#heroCarousel" data-bs-slide-to="1" aria-label="Slide 2"></button>
      <button type="button" data-bs-target="#heroCarousel" data-bs-slide-to="2" aria-label="Slide 3"></button>
    </div>
    <div class="carousel-inner">
      <div class="carousel-item active">
        <img src="assets/images/logo_1639375433.jpg" class="d-block w-100" alt="POS Banner 1" style="height:500px; object-fit:cover;">
        <div class="carousel-caption d-none d-md-block">
          <h1 class="fw-bold">Smart POS for Modern Businesses</h1>
          <p>Inventory, sales & customers in one dashboard.</p>
        </div>
      </div>
      <div class="carousel-item">
        <img src="assets/images/POS-Banner.png" class="d-block w-100" alt="POS Banner 2" style="height:500px; object-fit:cover;">
        <div class="carousel-caption d-none d-md-block">
          <h1 class="fw-bold">Track Everything in Real-Time</h1>
          <p>Data-driven insights for smarter decisions.</p>
        </div>
      </div>
      <div class="carousel-item">
        <img src="assets/images/points-of-sale-banner.png" class="d-block w-100" alt="POS Banner 3" style="height:500px; object-fit:cover;">
        <div class="carousel-caption d-none d-md-block">
          <h1 class="fw-bold">User-Friendly Interface</h1>
          <p>Intuitive design for seamless operations.</p>
        </div>
      </div>
    </div>
    <button class="carousel-control-prev" type="button" data-bs-target="#heroCarousel" data-bs-slide="prev">
      <span class="carousel-control-prev-icon" aria-hidden="true"></span>
      <span class="visually-hidden">Previous</span>
    </button>
    <button class="carousel-control-next" type="button" data-bs-target="#heroCarousel" data-bs-slide="next">
      <span class="carousel-control-next-icon" aria-hidden="true"></span>
      <span class="visually-hidden">Next</span>
    </button>
  </div>

  <!-- Key Features -->
  <section class="py-5 bg-light">
    <div class="container text-center">
      <h2 class="fw-bold">Key Features</h2>
      <p class="text-muted">Why businesses choose DREAM POS</p>
      <div class="row mt-4">
        <div class="col-md-4">
          <div class="feature-icon">📊</div>
          <h5>Real-Time Analytics</h5>
          <p>Monitor sales, stock, and customer trends instantly.</p>
        </div>
        <div class="col-md-4">
          <div class="feature-icon">🛒</div>
          <h5>Smart Inventory</h5>
          <p>Automatic stock alerts and low inventory notifications.</p>
        </div>
        <div class="col-md-4">
          <div class="feature-icon">👥</div>
          <h5>Customer Management</h5>
          <p>Reward loyal customers and manage feedback effectively.</p>
        </div>
      </div>
    </div>
  </section>

  <!-- Benefits -->
  <section class="py-5">
    <div class="container text-center">
      <h2 class="fw-bold">Why Choose DREAM POS</h2>
      <div class="row mt-4">
        <div class="col-md-4">
          <h5>Automate Inventory</h5>
          <p>Smart stock updates & low-inventory alerts.</p>
        </div>
        <div class="col-md-4">
          <h5>Real-Time Insights</h5>
          <p>Sales & customer data, instant analytics.</p>
        </div>
        <div class="col-md-4">
          <h5>Loyalty Management</h5>
          <p>Track behavior and reward loyal repeat customers.</p>
        </div>
      </div>
    </div>
  </section>

  <!-- How It Works -->
  <section class="py-5 bg-light">
    <div class="container text-center">
      <h2 class="fw-bold">How It Works</h2>
      <div class="row mt-4">
        <div class="col-md-4">
          <h5>1. Setup</h5>
          <p>Upload inventory, personalize your dashboard</p>
        </div>
        <div class="col-md-4">
          <h5>2. Sell</h5>
          <p>Process sales and manage customers seamlessly</p>
        </div>
        <div class="col-md-4">
          <h5>3. Analyze</h5>
          <p>Gain actionable insights in real time</p>
        </div>
      </div>
    </div>
  </section>

  <!-- Testimonials -->
  <section class="py-5">
    <div class="container">
      <h2 class="fw-bold text-center">What Our Clients Say</h2>
      <div class="row mt-4">
        <div class="col-md-6">
          <blockquote class="blockquote">
            “MyPOS reduced our inventory discrepancies by 40%.”<br>
            — Jane Doe, Manager at RetailCo
          </blockquote>
        </div>
        <div class="col-md-6">
          <blockquote class="blockquote">
            “Real‑time analytics let us make faster decisions.”<br>
            — John Smith, Owner at CaféSync
          </blockquote>
        </div>
      </div>
    </div>
  </section>

  <!-- Resources & FAQ -->
  <section class="py-5 bg-light">
    <div class="container">
      <h2 class="fw-bold text-center">Resources & Help</h2>
      <div class="row mt-4">
        <div class="col-md-6">
          <h5>From the Blog</h5>
          <ul class="list-unstyled">
            <li><a href="#">Optimizing Inventory Levels</a></li>
            <li><a href="#">Boosting Sales with Analytics</a></li>
          </ul>
        </div>
        <div class="col-md-6">
          <h5>FAQ</h5>
          <ul class="list-unstyled">
            <li>How does stock syncing work?</li>
            <li>Which payment methods are supported?</li>
          </ul>
        </div>
      </div>
    </div>
  </section>

  <!-- Final CTA -->
  <section class="py-5 text-white bg-primary text-center">
    <div class="container">
      <h2 class="fw-bold">Ready to Elevate Your Sales?</h2>
      <p>Start your free trial or <a href="contact.php" class="text-white text-decoration-underline">get in touch</a></p>
      <a href="signup.php" class="btn btn-light btn-lg">Start Free Trial</a>
    </div>
  </section>

  <!-- Footer -->
  <footer class="footer">
    &copy; 2025 <b>DREAM POS Software. All rights reserved. Developed by Rafia Hawlader Bonna
  </footer>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
