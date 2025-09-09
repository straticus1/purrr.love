    </main>
    
    <!-- Footer -->
    <footer class="relative z-10 bg-gray-900 text-white py-16">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
                <div>
                    <h3 class="text-2xl font-bold gradient-text mb-4">🐱 Purrr.love</h3>
                    <p class="text-gray-400 mb-4">
                        The ultimate cat gaming ecosystem with AI, blockchain, and VR technology.
                    </p>
                    <div class="flex space-x-4">
                        <a href="#" class="text-gray-400 hover:text-white transition-colors"><i class="fab fa-twitter text-xl"></i></a>
                        <a href="#" class="text-gray-400 hover:text-white transition-colors"><i class="fab fa-discord text-xl"></i></a>
                        <a href="#" class="text-gray-400 hover:text-white transition-colors"><i class="fab fa-telegram text-xl"></i></a>
                        <a href="#" class="text-gray-400 hover:text-white transition-colors"><i class="fab fa-github text-xl"></i></a>
                    </div>
                </div>
                <div>
                    <h4 class="text-lg font-semibold mb-4">Platform</h4>
                    <ul class="space-y-2 text-gray-400">
                        <li><a href="/web/games.php" class="hover:text-white transition-colors">Games</a></li>
                        <li><a href="/web/ml-personality.php" class="hover:text-white transition-colors">AI Personality</a></li>
                        <li><a href="/web/blockchain-nft.php" class="hover:text-white transition-colors">Blockchain</a></li>
                        <li><a href="/web/metaverse-vr.php" class="hover:text-white transition-colors">VR Metaverse</a></li>
                    </ul>
                </div>
                <div>
                    <h4 class="text-lg font-semibold mb-4">Resources</h4>
                    <ul class="space-y-2 text-gray-400">
                        <li><a href="/web/documentation.php" class="hover:text-white transition-colors">Documentation</a></li>
                        <li><a href="/api/" class="hover:text-white transition-colors">API Reference</a></li>
                        <li><a href="/sdk/" class="hover:text-white transition-colors">SDK Downloads</a></li>
                        <li><a href="/web/community.php" class="hover:text-white transition-colors">Community</a></li>
                    </ul>
                </div>
                <div>
                    <h4 class="text-lg font-semibold mb-4">Support</h4>
                    <ul class="space-y-2 text-gray-400">
                        <li><a href="/web/help.php" class="hover:text-white transition-colors">Help Center</a></li>
                        <li><a href="/web/support.php" class="hover:text-white transition-colors">Contact Us</a></li>
                        <li><a href="/web/support.php" class="hover:text-white transition-colors">Bug Reports</a></li>
                        <li><a href="/web/support.php" class="hover:text-white transition-colors">Feature Requests</a></li>
                    </ul>
                </div>
            </div>
            <div class="border-t border-gray-800 mt-12 pt-8 text-center text-gray-400">
                <p>&copy; <?php echo date('Y'); ?> Purrr.love. All rights reserved. Made with ❤️ for cat lovers everywhere.</p>
            </div>
        </div>
    </footer>

    <!-- Additional scripts can be added here -->
    <?php if (isset($additional_scripts)): ?>
        <?php echo $additional_scripts; ?>
    <?php endif; ?>
</body>
</html>
