<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SallesPro — Réservation de Salles</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Inter', sans-serif; }

        /* Navbar */
        .navbar { padding: 18px 0; background: transparent; position: absolute; top: 0; width: 100%; z-index: 100; }
        .navbar-brand { font-weight: 700; font-size: 22px; color: #fff !important; }
        .navbar-brand i { margin-right: 8px; }
        .nav-link { color: rgba(255,255,255,0.85) !important; font-weight: 500; margin: 0 12px; transition: color 0.3s; }
        .nav-link:hover { color: #fff !important; }
        .btn-nav { background: #fff; color: #0d9488 !important; padding: 8px 24px; border-radius: 50px; font-weight: 600; font-size: 14px; transition: all 0.3s; border: none; }
        .btn-nav:hover { transform: translateY(-2px); box-shadow: 0 4px 15px rgba(0,0,0,0.2); }

        /* Hero */
        .hero {
            min-height: 100vh;
            background: linear-gradient(135deg, #042f2e 0%, #0f766e 40%, #0d9488 70%, #06b6d4 100%);
            display: flex; align-items: center; position: relative; overflow: hidden;
        }
        .hero::before {
            content: '';
            position: absolute; top: 0; left: 0; right: 0; bottom: 0;
            background: url('https://images.unsplash.com/photo-1497366216548-37526070297c?w=1600&q=80') center/cover;
            opacity: 0.12;
        }
        .hero-content { position: relative; z-index: 2; }
        .hero h1 { font-size: 52px; font-weight: 800; color: #fff; line-height: 1.15; margin-bottom: 20px; }
        .hero h1 span { color: #99f6e4; }
        .hero p { font-size: 18px; color: rgba(255,255,255,0.8); max-width: 520px; line-height: 1.7; margin-bottom: 35px; }
        .btn-hero { padding: 14px 36px; border-radius: 50px; font-weight: 600; font-size: 16px; border: none; transition: all 0.3s; text-decoration: none; display: inline-block; }
        .btn-hero-primary { background: #fff; color: #0d9488; }
        .btn-hero-primary:hover { transform: translateY(-3px); box-shadow: 0 8px 25px rgba(0,0,0,0.25); color: #0f766e; }
        .btn-hero-secondary { background: rgba(255,255,255,0.15); color: #fff; border: 2px solid rgba(255,255,255,0.4); margin-left: 12px; }
        .btn-hero-secondary:hover { background: rgba(255,255,255,0.25); transform: translateY(-3px); }
        .hero-stats { margin-top: 50px; display: flex; gap: 40px; }
        .hero-stat h3 { font-size: 32px; font-weight: 700; color: #fff; }
        .hero-stat p { font-size: 14px; color: rgba(255,255,255,0.6); margin: 0; }

        /* Floating shapes */
        .hero-shape { position: absolute; border-radius: 50%; opacity: 0.08; background: #99f6e4; }
        .shape-1 { width: 400px; height: 400px; top: -100px; right: -100px; }
        .shape-2 { width: 250px; height: 250px; bottom: -50px; left: 10%; }
        .shape-3 { width: 150px; height: 150px; top: 40%; right: 15%; }

        /* Features */
        .features { padding: 100px 0; background: #f0fdfa; }
        .section-title { text-align: center; margin-bottom: 60px; }
        .section-title h2 { font-size: 36px; font-weight: 700; color: #042f2e; margin-bottom: 12px; }
        .section-title p { font-size: 16px; color: #666; max-width: 500px; margin: 0 auto; }
        .feature-card {
            background: #fff; border-radius: 16px; padding: 35px 30px; text-align: center;
            box-shadow: 0 4px 20px rgba(0,0,0,0.04); transition: all 0.3s; border: 1px solid #e2e8f0;
        }
        .feature-card:hover { transform: translateY(-8px); box-shadow: 0 12px 35px rgba(13,148,136,0.1); }
        .feature-icon {
            width: 70px; height: 70px; border-radius: 16px; display: flex; align-items: center; justify-content: center;
            margin: 0 auto 20px; font-size: 28px; color: #fff;
        }
        .icon-teal { background: linear-gradient(135deg, #0d9488, #06b6d4); }
        .icon-blue { background: linear-gradient(135deg, #2563eb, #3b82f6); }
        .icon-green { background: linear-gradient(135deg, #059669, #10b981); }
        .icon-orange { background: linear-gradient(135deg, #d97706, #f59e0b); }
        .icon-cyan { background: linear-gradient(135deg, #0891b2, #22d3ee); }
        .icon-emerald { background: linear-gradient(135deg, #047857, #34d399); }
        .feature-card h4 { font-size: 18px; font-weight: 600; color: #042f2e; margin-bottom: 10px; }
        .feature-card p { font-size: 14px; color: #777; line-height: 1.6; margin: 0; }

        /* How it works */
        .how-it-works { padding: 100px 0; background: #fff; }
        .step-card { text-align: center; padding: 30px 20px; position: relative; }
        .step-number {
            width: 50px; height: 50px; border-radius: 50%; background: linear-gradient(135deg, #0d9488, #06b6d4);
            color: #fff; font-size: 20px; font-weight: 700; display: flex; align-items: center; justify-content: center;
            margin: 0 auto 20px;
        }
        .step-card h5 { font-weight: 600; color: #042f2e; margin-bottom: 8px; }
        .step-card p { font-size: 14px; color: #777; }

        /* CTA */
        .cta {
            padding: 100px 0;
            background: linear-gradient(135deg, #042f2e, #0d9488);
            text-align: center; position: relative; overflow: hidden;
        }
        .cta::before {
            content: '';
            position: absolute; top: 0; left: 0; right: 0; bottom: 0;
            background: url('https://images.unsplash.com/photo-1497215842964-222b430dc094?w=1600&q=80') center/cover;
            opacity: 0.08;
        }
        .cta-content { position: relative; z-index: 2; }
        .cta h2 { font-size: 38px; font-weight: 700; color: #fff; margin-bottom: 16px; }
        .cta p { font-size: 17px; color: rgba(255,255,255,0.75); margin-bottom: 35px; max-width: 500px; margin-left: auto; margin-right: auto; }

        /* Footer */
        .footer { padding: 40px 0; background: #042f2e; text-align: center; }
        .footer p { color: rgba(255,255,255,0.5); font-size: 14px; margin: 0; }
        .footer a { color: #99f6e4; text-decoration: none; }

        /* Responsive */
        @media (max-width: 768px) {
            .hero h1 { font-size: 32px; }
            .hero-stats { gap: 20px; flex-wrap: wrap; }
            .btn-hero-secondary { margin-left: 0; margin-top: 10px; }
        }
    </style>
</head>
<body>

<!-- Navbar -->
<nav class="navbar navbar-expand-lg">
    <div class="container">
        <a class="navbar-brand" href="index.php"><i class="fas fa-building"></i> SallesPro</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto align-items-center">
                <li class="nav-item"><a class="nav-link" href="#features">Fonctionnalités</a></li>
                <li class="nav-item"><a class="nav-link" href="#how">Comment ça marche</a></li>
                <li class="nav-item"><a class="btn-nav" href="index.php?action=login">Se connecter</a></li>
            </ul>
        </div>
    </div>
</nav>

<!-- Hero -->
<section class="hero">
    <div class="hero-shape shape-1"></div>
    <div class="hero-shape shape-2"></div>
    <div class="hero-shape shape-3"></div>
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-7 hero-content">
                <h1>Réservez vos <span>salles de réunion</span> en quelques clics</h1>
                <p>Plateforme moderne et intuitive pour gérer la réservation de salles au sein de votre entreprise ou université. Consultez les disponibilités, réservez et gérez tout en un seul endroit.</p>
                <div>
                    <a href="index.php?action=login" class="btn-hero btn-hero-primary">
                        <i class="fas fa-sign-in-alt me-2"></i> Se connecter
                    </a>
                    <a href="index.php?action=register" class="btn-hero btn-hero-secondary">
                        <i class="fas fa-user-plus me-2"></i> Créer un compte
                    </a>
                </div>
                <div class="hero-stats">
                    <div class="hero-stat">
                        <h3>50+</h3>
                        <p>Salles disponibles</p>
                    </div>
                    <div class="hero-stat">
                        <h3>24/7</h3>
                        <p>Réservation en ligne</p>
                    </div>
                    <div class="hero-stat">
                        <h3>100%</h3>
                        <p>Gratuit et rapide</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Features -->
<section class="features" id="features">
    <div class="container">
        <div class="section-title">
            <h2>Fonctionnalités principales</h2>
            <p>Tout ce dont vous avez besoin pour gérer vos réservations efficacement</p>
        </div>
        <div class="row g-4">
            <div class="col-lg-4 col-md-6">
                <div class="feature-card">
                    <div class="feature-icon icon-teal"><i class="fas fa-calendar-alt"></i></div>
                    <h4>Calendrier interactif</h4>
                    <p>Visualisez les disponibilités en temps réel avec un calendrier coloré et intuitif.</p>
                </div>
            </div>
            <div class="col-lg-4 col-md-6">
                <div class="feature-card">
                    <div class="feature-icon icon-blue"><i class="fas fa-shield-alt"></i></div>
                    <h4>Gestion des conflits</h4>
                    <p>Le système détecte automatiquement les chevauchements et empêche les doubles réservations.</p>
                </div>
            </div>
            <div class="col-lg-4 col-md-6">
                <div class="feature-card">
                    <div class="feature-icon icon-green"><i class="fas fa-envelope-open-text"></i></div>
                    <h4>Notifications par email</h4>
                    <p>Recevez des confirmations et des alertes automatiques pour chaque réservation.</p>
                </div>
            </div>
            <div class="col-lg-4 col-md-6">
                <div class="feature-card">
                    <div class="feature-icon icon-orange"><i class="fas fa-search"></i></div>
                    <h4>Recherche multicritères</h4>
                    <p>Trouvez la salle parfaite en filtrant par bâtiment, capacité, équipements et date.</p>
                </div>
            </div>
            <div class="col-lg-4 col-md-6">
                <div class="feature-card">
                    <div class="feature-icon icon-cyan"><i class="fas fa-chart-bar"></i></div>
                    <h4>Statistiques & Rapports</h4>
                    <p>Suivez l'utilisation des salles et générez des rapports détaillés par période.</p>
                </div>
            </div>
            <div class="col-lg-4 col-md-6">
                <div class="feature-card">
                    <div class="feature-icon icon-emerald"><i class="fas fa-mobile-alt"></i></div>
                    <h4>Interface responsive</h4>
                    <p>Accédez depuis n'importe quel appareil — ordinateur, tablette ou smartphone.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- How it works -->
<section class="how-it-works" id="how">
    <div class="container">
        <div class="section-title">
            <h2>Comment ça marche ?</h2>
            <p>Un processus simple en 3 étapes</p>
        </div>
        <div class="row">
            <div class="col-lg-4 col-md-6">
                <div class="step-card">
                    <div class="step-number">1</div>
                    <h5>Créez votre compte</h5>
                    <p>Inscrivez-vous en quelques secondes avec votre email et un mot de passe.</p>
                </div>
            </div>
            <div class="col-lg-4 col-md-6">
                <div class="step-card">
                    <div class="step-number">2</div>
                    <h5>Choisissez votre salle</h5>
                    <p>Parcourez les salles disponibles, consultez le calendrier et filtrez selon vos besoins.</p>
                </div>
            </div>
            <div class="col-lg-4 col-md-6">
                <div class="step-card">
                    <div class="step-number">3</div>
                    <h5>Réservez et c'est fait !</h5>
                    <p>Soumettez votre demande et recevez une confirmation par email instantanément.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- CTA -->
<section class="cta">
    <div class="cta-content">
        <h2>Prêt à réserver ?</h2>
        <p>Rejoignez notre plateforme et simplifiez la gestion de vos salles de réunion dès maintenant.</p>
        <a href="index.php?action=register" class="btn-hero btn-hero-primary">
            <i class="fas fa-rocket me-2"></i> Commencer maintenant
        </a>
    </div>
</section>

<!-- Footer -->
<footer class="footer">
    <div class="container">
        <p>&copy; 2026 <a href="index.php">SallesPro</a> — Réservation de Salles de Réunion</p>
    </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>