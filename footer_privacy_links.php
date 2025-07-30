<?php
// footer_privacy_links.php - Link privacy per footer (da includere)
?>
                <div class="mt-2">
                    <a href="privacy_policy_simple.php" class="text-decoration-none me-3 text-white">
                        <i class="fas fa-shield-alt me-1"></i>Privacy Policy
                    </a>
                    <?php if (function_exists('isLoggedIn') && isLoggedIn()): ?>
                        <a href="gdpr_simple.php" class="text-decoration-none me-3 text-white">
                            <i class="fas fa-user-shield me-1"></i>I Tuoi Diritti
                        </a>
                    <?php endif; ?>
                    <a href="javascript:void(0)" onclick="cookieManager.showPreferences()" class="text-decoration-none text-white">
                        <i class="fas fa-cookie-bite me-1"></i>Cookie
                    </a>
                </div>
