    <!-- |KB Навигация: логотип, ссылки и мобильное меню -->
    <nav>
        <div class="nav-container">
            <a href="#top" class="logo">CARDFLY-CRYPTO</a>
            {{--<ul class="nav-links">
                <li><a href="#features">Features</a></li>
                <li><a href="#pricing">Pricing</a></li>
                <li><a href="#stats">Stats</a></li>
                <li><a href="#contact">Contact</a></li>
            </ul>--}}
            <div class="nav-bottom">
                <a href="{{ route('login') }}" class="cyber-button">Вход</a>
            </div>
            {{--<button class="mobile-menu-button" id="mobileMenuBtn">
                <div class="hamburger">
                    <span></span>
                    <span></span>
                    <span></span>
                </div>
            </button>--}}
        </div>
    </nav>
    <div class="mobile-menu-overlay" id="mobileMenuOverlay"></div>
    <div class="mobile-menu" id="mobileMenu">
        <div class="mobile-menu-header">
            <a href="#top" class="mobile-menu-logo">CARDFLY-CRYPTO</a>
            <button class="mobile-menu-close" id="mobileMenuClose">✕</button>
        </div>
        <div class="mobile-menu-cta">
            <a href="{{ route('login') }}" class="cyber-button">Вход</a>
        </div>
       {{-- <nav class="mobile-menu-nav">
            <ul>
                <li><a href="#features">Features</a></li>
                <li><a href="#pricing">Pricing</a></li>
                <li><a href="#stats">Stats</a></li>
                <li><a href="#contact">Contact</a></li>
            </ul>
        </nav>--}}
    </div>


