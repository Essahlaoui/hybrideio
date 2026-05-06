<?php
$current_page = basename($_SERVER['PHP_SELF']);
?>
<nav>
    <div style="display: flex; align-items: center; gap: 12px; font-weight: 900; font-size: 1.4rem; letter-spacing: -1px;">
        <img src="../assets/img/favicon.png" alt="Logo" style="height: 32px; width: 32px; border-radius: 8px;">
        <span style="color: var(--accent);">HYBRIDE</span>.IO
    </div>

    <div class="nav-links">
        <a href="dashboard.php" class="<?php echo ($current_page == 'dashboard.php') ? 'active' : ''; ?>">Dashboard</a>
        <a href="charts.php" class="<?php echo ($current_page == 'charts.php') ? 'active' : ''; ?>">Analytique</a>
        <a href="nodes.php" class="<?php echo ($current_page == 'nodes.php') ? 'active' : ''; ?>">Nodes</a>
        <a href="generator.php" class="<?php echo ($current_page == 'generator.php') ? 'active' : ''; ?>">Générateur</a>

        <a href="settings.php" class="<?php echo ($current_page == 'settings.php') ? 'active' : ''; ?>">Configuration</a>
        <a href="../logout.php" class="logout-btn">Déconnexion</a>
    </div>

    <div style="font-size: 0.8rem; color: #64748b; font-weight: 600;">v2.1 Stable</div>
</nav>
