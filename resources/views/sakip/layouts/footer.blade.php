@php
    $user = auth()->user();
    $currentYear = date('Y');
    $appVersion = config('app.version', '1.0.0');
    $lastLogin = $user->last_login_at ?? now();
    $serverTime = now();
@endphp

<footer class="sakip-footer" role="contentinfo" aria-label="Footer SAKIP">
    <div class="footer-container">
        <!-- Footer Top Section -->
        <div class="footer-top">
            <!-- Government Branding -->
            <div class="footer-section">
                <div class="footer-brand">
                    <div class="brand-logo">
                        <i class="fas fa-landmark"></i>
                    </div>
                    <div class="brand-info">
                        <h6>Sistem Akuntabilitas Kinerja</h6>
                        <p>SAKIP - Platform pengelolaan kinerja instansi pemerintah</p>
                    </div>
                </div>
            </div>
            
            <!-- Quick Links -->
            <div class="footer-section">
                <h6 class="footer-title">Tautan Cepat</h6>
                <ul class="footer-links">
                    <li><a href="{{ route('sakip.dashboard') }}" class="footer-link">Dashboard</a></li>
                    <li><a href="{{ route('sakip.indicators.index') }}" class="footer-link">Indikator Kinerja</a></li>
                    <li><a href="{{ route('sakip.data-collection.index') }}" class="footer-link">Pengumpulan Data</a></li>
                    <li><a href="{{ route('sakip.assessments.index') }}" class="footer-link">Penilaian</a></li>
                    <li><a href="{{ route('sakip.reports.index') }}" class="footer-link">Laporan</a></li>
                </ul>
            </div>
            
            <!-- Support -->
            <div class="footer-section">
                <h6 class="footer-title">Bantuan & Dukungan</h6>
                <ul class="footer-links">
                    <li><a href="{{ route('help') }}" class="footer-link">Pusat Bantuan</a></li>
                    <li><a href="{{ route('documentation') }}" class="footer-link">Dokumentasi</a></li>
                    <li><a href="{{ route('tutorials') }}" class="footer-link">Tutorial</a></li>
                    <li><a href="{{ route('faq') }}" class="footer-link">FAQ</a></li>
                    <li><a href="{{ route('feedback') }}" class="footer-link">Kirim Masukan</a></li>
                </ul>
            </div>
            
            <!-- Contact Info -->
            <div class="footer-section">
                <h6 class="footer-title">Kontak</h6>
                <div class="footer-contact">
                    <div class="contact-item">
                        <i class="fas fa-envelope"></i>
                        <span>sakip-support@example.go.id</span>
                    </div>
                    <div class="contact-item">
                        <i class="fas fa-phone"></i>
                        <span>+62 21 1234-5678</span>
                    </div>
                    <div class="contact-item">
                        <i class="fas fa-map-marker-alt"></i>
                        <span>Jakarta, Indonesia</span>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Footer Middle Section -->
        <div class="footer-middle">
            <!-- System Information -->
            <div class="system-info">
                <div class="info-item">
                    <i class="fas fa-info-circle"></i>
                    <span>Versi: {{ $appVersion }}</span>
                </div>
                <div class="info-item">
                    <i class="fas fa-clock"></i>
                    <span>Server Time: {{ $serverTime->format('d/m/Y H:i:s') }}</span>
                </div>
                <div class="info-item">
                    <i class="fas fa-user-clock"></i>
                    <span>Last Login: {{ $lastLogin->format('d/m/Y H:i') }}</span>
                </div>
                <div class="info-item">
                    <i class="fas fa-calendar-alt"></i>
                    <span>© {{ $currentYear }} Kementerian PANRB</span>
                </div>
            </div>
            
            <!-- Technical Info -->
            <div class="technical-info">
                <div class="tech-item">
                    <span class="tech-label">PHP Version:</span>
                    <span class="tech-value">{{ PHP_VERSION }}</span>
                </div>
                <div class="tech-item">
                    <span class="tech-label">Laravel:</span>
                    <span class="tech-value">{{ app()->version() }}</span>
                </div>
                <div class="tech-item">
                    <span class="tech-label">Environment:</span>
                    <span class="tech-value">{{ app()->environment() }}</span>
                </div>
            </div>
        </div>
        
        <!-- Footer Bottom Section -->
        <div class="footer-bottom">
            <!-- Legal Links -->
            <div class="legal-links">
                <a href="{{ route('privacy-policy') }}" class="legal-link">Kebijakan Privasi</a>
                <a href="{{ route('terms-of-service') }}" class="legal-link">Syarat & Ketentuan</a>
                <a href="{{ route('disclaimer') }}" class="legal-link">Disclaimer</a>
                <a href="{{ route('accessibility') }}" class="legal-link">Aksesibilitas</a>
            </div>
            
            <!-- Social Links -->
            <div class="social-links">
                <a href="https://twitter.com/example" class="social-link" target="_blank" rel="noopener noreferrer" title="Twitter">
                    <i class="fab fa-twitter"></i>
                </a>
                <a href="https://facebook.com/example" class="social-link" target="_blank" rel="noopener noreferrer" title="Facebook">
                    <i class="fab fa-facebook"></i>
                </a>
                <a href="https://instagram.com/example" class="social-link" target="_blank" rel="noopener noreferrer" title="Instagram">
                    <i class="fab fa-instagram"></i>
                </a>
                <a href="https://youtube.com/example" class="social-link" target="_blank" rel="noopener noreferrer" title="YouTube">
                    <i class="fab fa-youtube"></i>
                </a>
            </div>
            
            <!-- Back to Top Button -->
            <div class="back-to-top">
                <button class="back-to-top-btn" id="backToTopBtn" title="Kembali ke atas" aria-label="Kembali ke atas">
                    <i class="fas fa-arrow-up"></i>
                    <span>Atas</span>
                </button>
            </div>
        </div>
        
        <!-- Footer Status Bar -->
        <div class="footer-status">
            <div class="status-indicators">
                <span class="status-indicator {{ app()->environment('production') ? 'production' : 'development' }}">
                    <i class="fas fa-circle"></i>
                    <span>{{ ucfirst(app()->environment()) }}</span>
                </span>
                <span class="status-indicator online">
                    <i class="fas fa-wifi"></i>
                    <span>Online</span>
                </span>
                <span class="status-indicator">
                    <i class="fas fa-shield-alt"></i>
                    <span>SSL Secured</span>
                </span>
            </div>
            
            <div class="performance-info">
                <span class="load-time">Load Time: <span id="loadTime">-</span>ms</span>
                <span class="memory-usage">Memory: <span id="memoryUsage">-</span>MB</span>
            </div>
        </div>
    </div>
</footer>

<style>
.sakip-footer {
    background: var(--sakip-dark);
    color: white;
    margin-top: auto;
    border-top: 3px solid var(--sakip-secondary);
}

.footer-container {
    max-width: 1400px;
    margin: 0 auto;
    padding: 2rem 1.5rem 1rem;
}

/* Footer Top Section */
.footer-top {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 2rem;
    margin-bottom: 2rem;
    padding-bottom: 2rem;
    border-bottom: 1px solid rgba(255,255,255,0.1);
}

.footer-section {
    min-width: 0;
}

.footer-brand {
    display: flex;
    align-items: flex-start;
    gap: 1rem;
}

.brand-logo {
    font-size: 2rem;
    color: var(--sakip-accent);
    flex-shrink: 0;
}

.brand-info h6 {
    margin: 0 0 0.5rem 0;
    font-size: 1.1rem;
    font-weight: 600;
}

.brand-info p {
    margin: 0;
    font-size: 0.875rem;
    opacity: 0.8;
    line-height: 1.5;
}

.footer-title {
    margin: 0 0 1rem 0;
    font-size: 1rem;
    font-weight: 600;
    color: var(--sakip-accent);
}

.footer-links {
    list-style: none;
    margin: 0;
    padding: 0;
}

.footer-links li {
    margin-bottom: 0.5rem;
}

.footer-link {
    color: rgba(255,255,255,0.8);
    text-decoration: none;
    font-size: 0.875rem;
    transition: all 0.3s ease;
    display: inline-block;
    padding: 0.25rem 0;
}

.footer-link:hover {
    color: var(--sakip-accent);
    transform: translateX(4px);
}

.footer-contact {
    display: flex;
    flex-direction: column;
    gap: 0.75rem;
}

.contact-item {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    font-size: 0.875rem;
    color: rgba(255,255,255,0.8);
}

.contact-item i {
    width: 16px;
    text-align: center;
    color: var(--sakip-accent);
}

/* Footer Middle Section */
.footer-middle {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 2rem;
    padding-bottom: 2rem;
    border-bottom: 1px solid rgba(255,255,255,0.1);
    flex-wrap: wrap;
    gap: 2rem;
}

.system-info {
    display: flex;
    align-items: center;
    gap: 1.5rem;
    flex-wrap: wrap;
}

.info-item {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    font-size: 0.75rem;
    color: rgba(255,255,255,0.7);
}

.info-item i {
    color: var(--sakip-accent);
}

.technical-info {
    display: flex;
    align-items: center;
    gap: 1rem;
    flex-wrap: wrap;
}

.tech-item {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    font-size: 0.75rem;
}

.tech-label {
    color: rgba(255,255,255,0.6);
}

.tech-value {
    color: var(--sakip-accent);
    font-weight: 500;
}

/* Footer Bottom Section */
.footer-bottom {
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 1rem;
}

.legal-links {
    display: flex;
    align-items: center;
    gap: 1.5rem;
    flex-wrap: wrap;
}

.legal-link {
    color: rgba(255,255,255,0.7);
    text-decoration: none;
    font-size: 0.75rem;
    transition: color 0.3s ease;
}

.legal-link:hover {
    color: var(--sakip-accent);
}

.social-links {
    display: flex;
    align-items: center;
    gap: 0.75rem;
}

.social-link {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 32px;
    height: 32px;
    background: rgba(255,255,255,0.1);
    color: white;
    border-radius: 50%;
    text-decoration: none;
    transition: all 0.3s ease;
    font-size: 0.875rem;
}

.social-link:hover {
    background: var(--sakip-accent);
    transform: translateY(-2px);
}

.back-to-top-btn {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    background: rgba(255,255,255,0.1);
    color: white;
    border: none;
    padding: 0.5rem 1rem;
    border-radius: 6px;
    font-size: 0.75rem;
    cursor: pointer;
    transition: all 0.3s ease;
}

.back-to-top-btn:hover {
    background: var(--sakip-accent);
    transform: translateY(-2px);
}

/* Footer Status Bar */
.footer-status {
    margin-top: 2rem;
    padding-top: 1rem;
    border-top: 1px solid rgba(255,255,255,0.1);
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 1rem;
}

.status-indicators {
    display: flex;
    align-items: center;
    gap: 1rem;
}

.status-indicator {
    display: flex;
    align-items: center;
    gap: 0.25rem;
    font-size: 0.75rem;
    color: rgba(255,255,255,0.7);
}

.status-indicator.production i {
    color: #10b981;
}

.status-indicator.development i {
    color: #f59e0b;
}

.status-indicator.online i {
    color: #10b981;
}

.performance-info {
    display: flex;
    align-items: center;
    gap: 1rem;
    font-size: 0.75rem;
    color: rgba(255,255,255,0.6);
}

/* Back to Top Button */
.back-to-top-btn {
    opacity: 0;
    visibility: hidden;
    transform: translateY(20px);
    transition: all 0.3s ease;
}

.back-to-top-btn.visible {
    opacity: 1;
    visibility: visible;
    transform: translateY(0);
}

/* Mobile Responsive */
@media (max-width: 768px) {
    .footer-top {
        grid-template-columns: 1fr;
        gap: 1.5rem;
    }
    
    .footer-middle {
        flex-direction: column;
        align-items: flex-start;
        gap: 1.5rem;
    }
    
    .system-info,
    .technical-info {
        flex-direction: column;
        align-items: flex-start;
        gap: 0.75rem;
    }
    
    .footer-bottom {
        flex-direction: column;
        align-items: center;
        text-align: center;
        gap: 1.5rem;
    }
    
    .legal-links {
        justify-content: center;
    }
    
    .footer-status {
        flex-direction: column;
        align-items: center;
        text-align: center;
    }
    
    .status-indicators,
    .performance-info {
        justify-content: center;
    }
}

@media (max-width: 480px) {
    .footer-container {
        padding: 1.5rem 1rem 1rem;
    }
    
    .footer-brand {
        flex-direction: column;
        text-align: center;
    }
    
    .footer-section {
        text-align: center;
    }
    
    .contact-item {
        justify-content: center;
    }
}

/* Print Styles */
@media print {
    .sakip-footer {
        display: none;
    }
}

/* Dark mode support */
@media (prefers-color-scheme: dark) {
    .sakip-footer {
        background: #1f2937;
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Calculate and display performance metrics
    function updatePerformanceMetrics() {
        // Calculate load time
        if (window.performance && window.performance.timing) {
            const timing = window.performance.timing;
            const loadTime = timing.loadEventEnd - timing.navigationStart;
            const loadTimeElement = document.getElementById('loadTime');
            if (loadTimeElement && loadTime > 0) {
                loadTimeElement.textContent = loadTime;
            }
        }
        
        // Calculate memory usage (if available)
        if (window.performance && window.performance.memory) {
            const memoryUsage = window.performance.memory.usedJSHeapSize / 1048576; // Convert to MB
            const memoryElement = document.getElementById('memoryUsage');
            if (memoryElement) {
                memoryElement.textContent = memoryUsage.toFixed(1);
            }
        }
    }
    
    // Update performance metrics after page load
    if (document.readyState === 'complete') {
        updatePerformanceMetrics();
    } else {
        window.addEventListener('load', updatePerformanceMetrics);
    }
    
    // Back to top functionality
    const backToTopBtn = document.getElementById('backToTopBtn');
    
    function toggleBackToTop() {
        if (window.pageYOffset > 300) {
            backToTopBtn.classList.add('visible');
        } else {
            backToTopBtn.classList.remove('visible');
        }
    }
    
    if (backToTopBtn) {
        // Show/hide button based on scroll position
        window.addEventListener('scroll', toggleBackToTop);
        
        // Smooth scroll to top
        backToTopBtn.addEventListener('click', function(e) {
            e.preventDefault();
            window.scrollTo({
                top: 0,
                behavior: 'smooth'
            });
        });
        
        // Initial check
        toggleBackToTop();
    }
    
    // Update online/offline status
    function updateConnectionStatus() {
        const statusIndicator = document.querySelector('.status-indicator.online');
        if (statusIndicator) {
            if (navigator.onLine) {
                statusIndicator.innerHTML = '<i class="fas fa-wifi"></i><span>Online</span>';
                statusIndicator.classList.add('online');
            } else {
                statusIndicator.innerHTML = '<i class="fas fa-wifi-slash"></i><span>Offline</span>';
                statusIndicator.classList.remove('online');
            }
        }
    }
    
    // Listen for connection changes
    window.addEventListener('online', updateConnectionStatus);
    window.addEventListener('offline', updateConnectionStatus);
    
    // Initial status update
    updateConnectionStatus();
    
    // Add click handlers for footer links (analytics placeholder)
    const footerLinks = document.querySelectorAll('.footer-link, .legal-link, .social-link');
    footerLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            // Placeholder for analytics tracking
            console.log('Footer link clicked:', this.href);
            // You can add Google Analytics or other tracking here
        });
    });
    
    // Keyboard navigation support
    document.addEventListener('keydown', function(e) {
        // Home key to go to top
        if (e.key === 'Home' && e.ctrlKey) {
            e.preventDefault();
            if (backToTopBtn) {
                backToTopBtn.click();
            }
        }
    });
    
    // Add smooth scrolling for anchor links
    const anchorLinks = document.querySelectorAll('a[href^="#"]');
    anchorLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            const targetId = this.getAttribute('href').substring(1);
            const targetElement = document.getElementById(targetId);
            if (targetElement) {
                targetElement.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            }
        });
    });
});
</script>