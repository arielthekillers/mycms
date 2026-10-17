<header class="site-header">
    <div class="site-branding">
        <a href="/">Pesantren CMS</a>
    </div>
    <nav class="site-nav">
        <!-- Wire this to MenuManager::getResolvedTree('main-nav') once a menu is created -->
    </nav>
    <?php if (!empty($global['header-top'])): ?>
        <div class="widget-area widget-area-header"><?= $global['header-top'] ?></div>
    <?php endif; ?>
</header>
